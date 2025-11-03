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

use Atico\SpreadsheetTranslator\Core\Configuration\Configuration;
use Atico\SpreadsheetTranslator\Core\Exporter\ExporterConfigurationInterface;
use Atico\SpreadsheetTranslator\Exporter\Php\PhpExporterConfigurationManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PhpExporterConfigurationManagerTest extends TestCase
{
    private function createConfiguration(array $options): Configuration
    {
        $wrappedConfig = ['exporter' => ['php' => $options]];
        return new Configuration($wrappedConfig, 'php');
    }

    #[Test]
    public function constructor_creates_instance(): void
    {
        $config = $this->createConfiguration([
            'destination_folder' => '/tmp',
            'domain' => 'messages',
        ]);

        $manager = new PhpExporterConfigurationManager($config);

        $this->assertInstanceOf(PhpExporterConfigurationManager::class, $manager);
    }

    #[Test]
    public function implements_exporter_configuration_interface(): void
    {
        $config = $this->createConfiguration([
            'destination_folder' => '/tmp',
            'domain' => 'messages',
        ]);

        $manager = new PhpExporterConfigurationManager($config);

        $this->assertInstanceOf(ExporterConfigurationInterface::class, $manager);
    }

    #[Test]
    public function getDestinationFolder_returns_configured_value(): void
    {
        $config = $this->createConfiguration([
            'destination_folder' => '/var/www/translations',
            'domain' => 'messages',
        ]);

        $manager = new PhpExporterConfigurationManager($config);

        $this->assertEquals('/var/www/translations', $manager->getDestinationFolder());
    }

    #[Test]
    public function getDestinationFolder_throws_exception_when_missing(): void
    {
        $config = $this->createConfiguration([
            'domain' => 'messages',
        ]);

        $manager = new PhpExporterConfigurationManager($config);

        $this->expectException(\Exception::class);
        $manager->getDestinationFolder();
    }

    #[Test]
    public function getDomain_returns_configured_value(): void
    {
        $config = $this->createConfiguration([
            'destination_folder' => '/tmp',
            'domain' => 'validators',
        ]);

        $manager = new PhpExporterConfigurationManager($config);

        $this->assertEquals('validators', $manager->getDomain());
    }

    #[Test]
    public function getDomain_throws_exception_when_missing(): void
    {
        $config = $this->createConfiguration([
            'destination_folder' => '/tmp',
        ]);

        $manager = new PhpExporterConfigurationManager($config);

        $this->expectException(\Exception::class);
        $manager->getDomain();
    }

    #[Test]
    public function getPrefix_returns_empty_string_by_default(): void
    {
        $config = $this->createConfiguration([
            'destination_folder' => '/tmp',
            'domain' => 'messages',
        ]);

        $manager = new PhpExporterConfigurationManager($config);

        $this->assertEquals('', $manager->getPrefix());
    }

    #[Test]
    public function getPrefix_returns_configured_value(): void
    {
        $config = $this->createConfiguration([
            'destination_folder' => '/tmp',
            'domain' => 'messages',
            'prefix' => 'app_',
        ]);

        $manager = new PhpExporterConfigurationManager($config);

        $this->assertEquals('app_', $manager->getPrefix());
    }

    #[Test]
    public function handles_configuration_with_all_options(): void
    {
        $config = $this->createConfiguration([
            'destination_folder' => '/var/www/translations',
            'domain' => 'validators',
            'prefix' => 'custom_',
        ]);

        $manager = new PhpExporterConfigurationManager($config);

        $this->assertEquals('/var/www/translations', $manager->getDestinationFolder());
        $this->assertEquals('validators', $manager->getDomain());
        $this->assertEquals('custom_', $manager->getPrefix());
    }

    #[Test]
    public function handles_empty_prefix(): void
    {
        $config = $this->createConfiguration([
            'destination_folder' => '/tmp',
            'domain' => 'messages',
            'prefix' => '',
        ]);

        $manager = new PhpExporterConfigurationManager($config);

        $this->assertEquals('', $manager->getPrefix());
    }

    #[Test]
    public function handles_null_prefix(): void
    {
        $config = $this->createConfiguration([
            'destination_folder' => '/tmp',
            'domain' => 'messages',
            'prefix' => null,
        ]);

        $manager = new PhpExporterConfigurationManager($config);

        $this->assertEquals('', $manager->getPrefix());
    }

    #[Test]
    public function getDestinationFolder_handles_various_path_formats(): void
    {
        $paths = [
            '/absolute/path',
            './relative/path',
            '../parent/path',
            'simple/path',
            '/path/with spaces/folder',
        ];

        foreach ($paths as $path) {
            $config = $this->createConfiguration([
                'destination_folder' => $path,
                'domain' => 'messages',
            ]);

            $manager = new PhpExporterConfigurationManager($config);

            $this->assertEquals($path, $manager->getDestinationFolder(), "Failed for path: {$path}");
        }
    }

    #[Test]
    public function getDomain_handles_various_domain_names(): void
    {
        $domains = [
            'messages',
            'validators',
            'errors',
            'custom-domain',
            'domain_with_underscore',
            'domain.with.dots',
        ];

        foreach ($domains as $domain) {
            $config = $this->createConfiguration([
                'destination_folder' => '/tmp',
                'domain' => $domain,
            ]);

            $manager = new PhpExporterConfigurationManager($config);

            $this->assertEquals($domain, $manager->getDomain(), "Failed for domain: {$domain}");
        }
    }

    #[Test]
    public function constructor_with_empty_configuration_array(): void
    {
        $config = $this->createConfiguration([]);
        $manager = new PhpExporterConfigurationManager($config);

        $this->assertInstanceOf(PhpExporterConfigurationManager::class, $manager);
        $this->expectException(\Exception::class);
        $manager->getDestinationFolder();
    }
}
