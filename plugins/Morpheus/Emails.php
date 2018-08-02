<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Morpheus;


use Piwik\Piwik;
use Piwik\ReportRenderer;

class Emails
{
    const REPORT_TITLE_TEXT_SIZE = 24;
    const REPORT_TABLE_HEADER_TEXT_SIZE = 11;
    const REPORT_TABLE_ROW_TEXT_SIZE = '13px';
    const REPORT_BACK_TO_TOP_TEXT_SIZE = 9;

    public static function getDefaultEmailStyles()
    {
        return [
            'reportFontFamily' => ReportRenderer::DEFAULT_REPORT_FONT_FAMILY,
            'reportTitleTextColor' => self::rgbToHex(ReportRenderer::REPORT_TITLE_TEXT_COLOR),
            'reportTitleTextSize' => self::REPORT_TITLE_TEXT_SIZE,
            'reportTextColor' => self::rgbToHex(ReportRenderer::REPORT_TEXT_COLOR),
            'tableHeaderBgColor' => self::rgbToHex(ReportRenderer::TABLE_HEADER_BG_COLOR),
            'tableHeaderTextColor' => self::rgbToHex(ReportRenderer::TABLE_HEADER_TEXT_COLOR),
            'tableCellBorderColor' => self::rgbToHex(ReportRenderer::TABLE_CELL_BORDER_COLOR),
            'tableBgColor' => self::rgbToHex(ReportRenderer::TABLE_BG_COLOR),
            'reportTableHeaderTextWeight' => ReportRenderer::TABLE_HEADER_TEXT_WEIGHT,
            'reportTableHeaderTextSize' => self::REPORT_TABLE_HEADER_TEXT_SIZE,
            'reportTableHeaderTextTransform' => ReportRenderer::TABLE_HEADER_TEXT_TRANSFORM,
            'reportTableRowTextSize' => self::REPORT_TABLE_ROW_TEXT_SIZE,
            'reportBackToTopTextSize' => self::REPORT_BACK_TO_TOP_TEXT_SIZE,

            'brandNameLong' => 'Matomo, ' . Piwik::translate('General_OpenSourceWebAnalytics'),
            'themeFontFamilyBase' => '-apple-system, BlinkMacSystemFont, \'Segoe UI\', Roboto, Oxygen-Sans, Cantarell, \'Helvetica Neue\', sans-serif',
            'themeColorBrand' => '#d4291f',
            'themeColorBrandContrast' => '#fff',
            'themeColorText' => '#0d0d0d',
            'themeColorTextLight' => '#444',
            'themeColorTextLighter' => '#666666',
            'themeColorTextContrast' => '#777',
            'themeColorLink' => '#4183C4',
            'themeColorBaseSeries' => '#ee3024',
            'themeColorHeadlineAlternative' => '#4E4E4E',
            'themeColorHeaderBackground' => '#37474f',
            'themeColorHeaderText' =>  '#fff',
        ];
    }

    private static function rgbToHex($rgbValues)
    {
        list($r, $g, $b) = explode(',', $rgbValues);
        return '#' . dechex($r) . dechex($g) . dechex($b);
    }
}
