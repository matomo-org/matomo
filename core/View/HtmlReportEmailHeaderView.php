<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\View;

use Piwik\Date;
use Piwik\Plugins\API\API;
use Piwik\Plugins\CoreAdminHome\CustomLogo;
use Piwik\ReportRenderer;
use Piwik\Scheduler\Schedule\Schedule;
use Piwik\SettingsPiwik;
use Piwik\Site;
use Piwik\View;

class HtmlReportEmailHeaderView extends View
{
    const TEMPLATE_FILE = '@CoreHome/ReportRenderer/_htmlReportHeader';

    const REPORT_TITLE_TEXT_SIZE = 24;
    const REPORT_TABLE_HEADER_TEXT_SIZE = 11;
    const REPORT_TABLE_ROW_TEXT_SIZE = '13px';
    const REPORT_BACK_TO_TOP_TEXT_SIZE = 9;

    private static $reportFrequencyTranslationByPeriod = [
        Schedule::PERIOD_DAY   => 'General_DailyReport',
        Schedule::PERIOD_WEEK  => 'General_WeeklyReport',
        Schedule::PERIOD_MONTH => 'General_MonthlyReport',
        Schedule::PERIOD_YEAR  => 'General_YearlyReport',
        Schedule::PERIOD_RANGE => 'General_RangeReports',
    ];

    public function __construct($reportTitle, $prettyDate, $description, $reportMetadata, $segment, $idSite, $period)
    {
        parent::__construct(self::TEMPLATE_FILE);

        self::assignCommonParameters($this);

        $periods = self::getPeriodToFrequencyAsAdjective();
        $this->assign("frequency", $periods[$period]);
        $this->assign("reportTitle", $reportTitle);
        $this->assign("prettyDate", $prettyDate);
        $this->assign("description", $description);
        $this->assign("reportMetadata", $reportMetadata);
        $this->assign("websiteName", Site::getNameFor($idSite));
        $this->assign("idSite", $idSite);
        $this->assign("period", $period);

        $customLogo = new CustomLogo();
        $this->assign("isCustomLogo", $customLogo->isEnabled() && CustomLogo::hasUserLogo());
        $this->assign("logoHeader", $customLogo->getHeaderLogoUrl($pathOnly = false));

        $date = Date::now()->setTimezone(Site::getTimezoneFor($idSite))->toString();
        $this->assign("date", $date);

        // segment
        $displaySegment = ($segment != null);
        $this->assign("displaySegment", $displaySegment);
        if ($displaySegment) {
            $this->assign("segmentName", $segment['name']);
        }
    }

    public static function assignCommonParameters(View $view)
    {
        $view->assign("reportFontFamily", ReportRenderer::DEFAULT_REPORT_FONT_FAMILY);
        $view->assign("reportTitleTextColor", ReportRenderer::REPORT_TITLE_TEXT_COLOR);
        $view->assign("reportTitleTextSize", self::REPORT_TITLE_TEXT_SIZE);
        $view->assign("reportTextColor", ReportRenderer::REPORT_TEXT_COLOR);
        $view->assign("tableHeaderBgColor", ReportRenderer::TABLE_HEADER_BG_COLOR);
        $view->assign("tableHeaderTextColor", ReportRenderer::TABLE_HEADER_TEXT_COLOR);
        $view->assign("tableCellBorderColor", ReportRenderer::TABLE_CELL_BORDER_COLOR);
        $view->assign("tableBgColor", ReportRenderer::TABLE_BG_COLOR);
        $view->assign("reportTableHeaderTextWeight", ReportRenderer::TABLE_HEADER_TEXT_WEIGHT);
        $view->assign("reportTableHeaderTextSize", self::REPORT_TABLE_HEADER_TEXT_SIZE);
        $view->assign("reportTableHeaderTextTransform", ReportRenderer::TABLE_HEADER_TEXT_TRANSFORM);
        $view->assign("reportTableRowTextSize", self::REPORT_TABLE_ROW_TEXT_SIZE);
        $view->assign("reportBackToTopTextSize", self::REPORT_BACK_TO_TOP_TEXT_SIZE);
        $view->assign("currentPath", SettingsPiwik::getPiwikUrl());
        $view->assign("logoHeader", API::getInstance()->getHeaderLogoUrl());
    }

    private static function getPeriodToFrequencyAsAdjective()
    {
        return array_map(['\Piwik\Piwik', 'translate'], self::$reportFrequencyTranslationByPeriod);
    }
}
