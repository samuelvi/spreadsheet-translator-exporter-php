<?php

declare(strict_types=1);

/*
 * This file is part of the Atico/SpreadsheetTranslator package.
 *
 * (c) Samuel Vicent <samuelvicent@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Atico\SpreadsheetTranslator\Exporter\Php\Tests;

use Atico\SpreadsheetTranslator\Core\Exporter\ExportContentInterface;
use Atico\SpreadsheetTranslator\Exporter\Php\Php;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class PhpTest extends TestCase
{
    private array $validConfiguration = [
        'destination_folder' => '/tmp',
        'domain' => 'messages',
        'prefix' => '',
    ];

    #[Test]
    public function constructor_creates_instance_with_valid_configuration(): void
    {
        $exporter = new Php($this->validConfiguration);

        $this->assertInstanceOf(Php::class, $exporter);
    }

    #[Test]
    public function getFormat_returns_php(): void
    {
        $exporter = new Php($this->validConfiguration);

        $this->assertEquals('php', $exporter->getFormat());
    }

    #[Test]
    public function buildContent_with_empty_array(): void
    {
        $exporter = new Php($this->validConfiguration);
        $exportContent = $this->createMockExportContent([]);

        $content = $this->invokeBuildContent($exporter, $exportContent);

        $this->assertEquals("<?php\nreturn array (\n);", $content);
    }

    #[Test]
    public function buildContent_with_simple_array(): void
    {
        $exporter = new Php($this->validConfiguration);
        $translations = ['hello' => 'Hello World', 'goodbye' => 'Goodbye'];
        $exportContent = $this->createMockExportContent($translations);

        $content = $this->invokeBuildContent($exporter, $exportContent);

        $this->assertStringContainsString("<?php\nreturn array (", $content);
        $this->assertStringContainsString("'hello' => 'Hello World'", $content);
        $this->assertStringContainsString("'goodbye' => 'Goodbye'", $content);
    }

    #[Test]
    public function buildContent_with_nested_array(): void
    {
        $exporter = new Php($this->validConfiguration);
        $translations = [
            'messages' => [
                'welcome' => 'Welcome',
                'errors' => [
                    'not_found' => 'Not Found',
                    'unauthorized' => 'Unauthorized',
                ],
            ],
        ];
        $exportContent = $this->createMockExportContent($translations);

        $content = $this->invokeBuildContent($exporter, $exportContent);

        $this->assertStringContainsString("'messages'", $content);
        $this->assertStringContainsString("'welcome' => 'Welcome'", $content);
        $this->assertStringContainsString("'errors'", $content);
        $this->assertStringContainsString("'not_found' => 'Not Found'", $content);
        $this->assertStringContainsString("'unauthorized' => 'Unauthorized'", $content);
    }

    #[Test]
    public function buildContent_with_special_characters(): void
    {
        $exporter = new Php($this->validConfiguration);
        $translations = [
            'quote' => "It's a test",
            'double_quote' => 'He said "Hello"',
            'backslash' => 'Path: C:\\Users\\test',
            'newline' => "Line 1\nLine 2",
            'tab' => "Col1\tCol2",
        ];
        $exportContent = $this->createMockExportContent($translations);

        $content = $this->invokeBuildContent($exporter, $exportContent);

        $this->assertStringStartsWith("<?php\nreturn array (", $content);
        // Verify the content can be evaluated
        $evaluated = eval('return ' . substr($content, strlen("<?php\nreturn ")) . ';');
        $this->assertEquals($translations, $evaluated);
    }

    #[Test]
    public function buildContent_with_unicode_characters(): void
    {
        $exporter = new Php($this->validConfiguration);
        $translations = [
            'chinese' => '你好',
            'arabic' => 'مرحبا',
            'emoji' => '👋 Hello',
            'russian' => 'Привет',
            'japanese' => 'こんにちは',
        ];
        $exportContent = $this->createMockExportContent($translations);

        $content = $this->invokeBuildContent($exporter, $exportContent);

        $this->assertStringStartsWith("<?php\nreturn array (", $content);
        $evaluated = eval('return ' . substr($content, strlen("<?php\nreturn ")) . ';');
        $this->assertEquals($translations, $evaluated);
    }

    #[Test]
    public function buildContent_with_null_values(): void
    {
        $exporter = new Php($this->validConfiguration);
        $translations = [
            'null_value' => null,
            'empty_string' => '',
        ];
        $exportContent = $this->createMockExportContent($translations);

        $content = $this->invokeBuildContent($exporter, $exportContent);

        $this->assertStringContainsString('NULL', $content);
        $evaluated = eval('return ' . substr($content, strlen("<?php\nreturn ")) . ';');
        $this->assertEquals($translations, $evaluated);
    }

    #[Test]
    public function buildContent_with_boolean_values(): void
    {
        $exporter = new Php($this->validConfiguration);
        $translations = [
            'true_value' => true,
            'false_value' => false,
        ];
        $exportContent = $this->createMockExportContent($translations);

        $content = $this->invokeBuildContent($exporter, $exportContent);

        $this->assertStringContainsString('true', $content);
        $this->assertStringContainsString('false', $content);
        $evaluated = eval('return ' . substr($content, strlen("<?php\nreturn ")) . ';');
        $this->assertEquals($translations, $evaluated);
    }

    #[Test]
    public function buildContent_with_numeric_keys(): void
    {
        $exporter = new Php($this->validConfiguration);
        $translations = [
            0 => 'zero',
            1 => 'one',
            2 => 'two',
        ];
        $exportContent = $this->createMockExportContent($translations);

        $content = $this->invokeBuildContent($exporter, $exportContent);

        $evaluated = eval('return ' . substr($content, strlen("<?php\nreturn ")) . ';');
        $this->assertEquals($translations, $evaluated);
    }

    #[Test]
    public function buildContent_with_mixed_array(): void
    {
        $exporter = new Php($this->validConfiguration);
        $translations = [
            'string' => 'text',
            'number' => 42,
            'float' => 3.14,
            'bool' => true,
            'null' => null,
            'array' => ['nested' => 'value'],
        ];
        $exportContent = $this->createMockExportContent($translations);

        $content = $this->invokeBuildContent($exporter, $exportContent);

        $evaluated = eval('return ' . substr($content, strlen("<?php\nreturn ")) . ';');
        $this->assertEquals($translations, $evaluated);
    }

    #[Test]
    public function buildContent_with_deeply_nested_array(): void
    {
        $exporter = new Php($this->validConfiguration);
        $translations = [
            'level1' => [
                'level2' => [
                    'level3' => [
                        'level4' => [
                            'level5' => 'deep value',
                        ],
                    ],
                ],
            ],
        ];
        $exportContent = $this->createMockExportContent($translations);

        $content = $this->invokeBuildContent($exporter, $exportContent);

        $evaluated = eval('return ' . substr($content, strlen("<?php\nreturn ")) . ';');
        $this->assertEquals($translations, $evaluated);
    }

    #[Test]
    #[DataProvider('edgeCaseTranslationsProvider')]
    public function buildContent_handles_edge_cases(array $translations, string $description): void
    {
        $exporter = new Php($this->validConfiguration);
        $exportContent = $this->createMockExportContent($translations);

        $content = $this->invokeBuildContent($exporter, $exportContent);

        $this->assertStringStartsWith("<?php\nreturn array (", $content);
        $evaluated = eval('return ' . substr($content, strlen("<?php\nreturn ")) . ';');
        $this->assertEquals($translations, $evaluated, "Failed for: {$description}");
    }

    public static function edgeCaseTranslationsProvider(): array
    {
        return [
            'single quote in key' => [
                ["key'with'quote" => 'value'],
                'Keys with single quotes',
            ],
            'double quote in key' => [
                ['key"with"quote' => 'value'],
                'Keys with double quotes',
            ],
            'very long string' => [
                ['long' => str_repeat('A', 1000)],
                'Very long string value',
            ],
            'empty key' => [
                ['' => 'empty key value'],
                'Empty string as key',
            ],
            'numeric string keys' => [
                ['123' => 'numeric string key'],
                'Numeric string as key',
            ],
            'array with spaces in keys' => [
                ['key with spaces' => 'value with spaces'],
                'Keys and values with spaces',
            ],
            'html entities' => [
                ['html' => '<script>alert("XSS")</script>'],
                'HTML/JavaScript content',
            ],
            'sql injection attempt' => [
                ['sql' => "'; DROP TABLE users; --"],
                'SQL injection string',
            ],
            'zero values' => [
                ['zero_int' => 0, 'zero_float' => 0.0, 'zero_string' => '0'],
                'Different types of zero',
            ],
        ];
    }

    #[Test]
    public function buildContent_preserves_array_structure(): void
    {
        $exporter = new Php($this->validConfiguration);
        $translations = [
            'indexed' => [0, 1, 2],
            'associative' => ['a' => 1, 'b' => 2],
            'mixed' => [0 => 'zero', 'key' => 'value', 1 => 'one'],
        ];
        $exportContent = $this->createMockExportContent($translations);

        $content = $this->invokeBuildContent($exporter, $exportContent);

        $evaluated = eval('return ' . substr($content, strlen("<?php\nreturn ")) . ';');
        $this->assertEquals($translations, $evaluated);
        $this->assertEquals([0, 1, 2], $evaluated['indexed']);
        $this->assertEquals(['a' => 1, 'b' => 2], $evaluated['associative']);
    }

    #[Test]
    public function buildContent_output_starts_with_php_tag(): void
    {
        $exporter = new Php($this->validConfiguration);
        $exportContent = $this->createMockExportContent(['test' => 'value']);

        $content = $this->invokeBuildContent($exporter, $exportContent);

        $this->assertStringStartsWith("<?php\n", $content);
    }

    #[Test]
    public function buildContent_output_contains_return_statement(): void
    {
        $exporter = new Php($this->validConfiguration);
        $exportContent = $this->createMockExportContent(['test' => 'value']);

        $content = $this->invokeBuildContent($exporter, $exportContent);

        $this->assertStringContainsString('return ', $content);
    }

    #[Test]
    public function buildContent_output_ends_with_semicolon(): void
    {
        $exporter = new Php($this->validConfiguration);
        $exportContent = $this->createMockExportContent(['test' => 'value']);

        $content = $this->invokeBuildContent($exporter, $exportContent);

        $this->assertStringEndsWith(';', $content);
    }

    private function createMockExportContent(array $translations): ExportContentInterface
    {
        $mock = $this->createMock(ExportContentInterface::class);
        $mock->method('getTranslations')->willReturn($translations);
        $mock->method('getDestinationFile')->willReturn('/tmp/test.php');
        $mock->method('getLocale')->willReturn('en');

        return $mock;
    }

    private function invokeBuildContent(Php $exporter, ExportContentInterface $exportContent): string
    {
        $reflection = new ReflectionClass($exporter);
        $method = $reflection->getMethod('buildContent');
        $method->setAccessible(true);

        return $method->invoke($exporter, $exportContent);
    }
}
