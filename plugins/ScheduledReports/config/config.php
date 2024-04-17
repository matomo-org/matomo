<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

use Piwik\Plugins\ScheduledReports\ReportEmailGenerator;
use Piwik\Plugins\ScheduledReports\ReportEmailGenerator\AttachedFileReportEmailGenerator;
use Piwik\Plugins\ScheduledReports\ReportEmailGenerator\HtmlReportEmailGenerator;

return [
    ReportEmailGenerator::class . '.pdf' => Piwik\DI::autowire(AttachedFileReportEmailGenerator::class)
        ->constructorParameter('attachedFileExtension', '.pdf')
        ->constructorParameter('attachedFileMimeType', 'application/pdf'),

    ReportEmailGenerator::class . '.csv' => Piwik\DI::autowire(AttachedFileReportEmailGenerator::class)
        ->constructorParameter('attachedFileExtension', '.csv')
        ->constructorParameter('attachedFileMimeType', 'application/csv'),

    ReportEmailGenerator::class . '.tsv' => Piwik\DI::autowire(AttachedFileReportEmailGenerator::class)
        ->constructorParameter('attachedFileExtension', '.tsv')
        ->constructorParameter('attachedFileMimeType', 'application/tsv'),

    ReportEmailGenerator::class . '.html' => Piwik\DI::create(HtmlReportEmailGenerator::class),
];
