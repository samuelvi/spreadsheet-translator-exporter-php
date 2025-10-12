<?php

/*
 * This file is part of the Atico/SpreadsheetTranslator package.
 *
 * (c) Samuel Vicent <samuelvicent@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Atico\SpreadsheetTranslator\Exporter\Php;

use Atico\SpreadsheetTranslator\Core\Exporter\ExportContentInterface;
use Atico\SpreadsheetTranslator\Core\Exporter\ExporterInterface;
use Atico\SpreadsheetTranslator\Core\Exporter\AbstractExporter;

class Php extends AbstractExporter implements ExporterInterface
{
    function __construct($configuration)
    {
        $this->configuration = new PhpExporterConfigurationManager($configuration);
    }

    public function getFormat(): string
    {
        return 'php';
    }

    protected function buildContent(ExportContentInterface $exportContent): string
    {
        return sprintf("<?php\nreturn %s;", var_export($exportContent->getTranslations(), true));
    }

}