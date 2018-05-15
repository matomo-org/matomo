<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

use Piwik\Plugins\ScheduledReports\ReportEmailGenerator;
use Piwik\Plugins\ScheduledReports\ReportEmailGenerator\AttachedFileReportEmailGenerator;
use Piwik\Plugins\ScheduledReports\ReportEmailGenerator\HtmlReportEmailGenerator;

return [
    ReportEmailGenerator::class . '.pdf' => DI\object(AttachedFileReportEmailGenerator::class)
        ->constructorParameter('attachedFileExtension', '.pdf')
        ->constructorParameter('attachedFileMimeType', 'application/pdf'),

    ReportEmailGenerator::class . '.csv' => DI\object(AttachedFileReportEmailGenerator::class)
        ->constructorParameter('attachedFileExtension', '.csv')
        ->constructorParameter('attachedFileMimeType', 'application/csv'),

    ReportEmailGenerator::class . '.html' => DI\object(HtmlReportEmailGenerator::class),
];
