<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\View;

use Piwik\Common;
use Piwik\Date;
use Piwik\Mail\EmailStyles;
use Piwik\Plugin\ThemeStyles;
use Piwik\Plugins\CoreAdminHome\CustomLogo;
use Piwik\Scheduler\Schedule\Schedule;
use Piwik\SettingsPiwik;
use Piwik\Site;
use Piwik\View;
use Piwik\Plugin\Manager;

class HtmlReportEmailHeaderView extends View
{
    const TEMPLATE_FILE = '@CoreHome/ReportRenderer/_htmlReportHeader';

    private static $reportFrequencyTranslationByPeriod = [
        Schedule::PERIOD_NEVER => '',
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
        $themeStyles = ThemeStyles::get();
        $emailStyles = EmailStyles::get();

        $view->currentPath = SettingsPiwik::getPiwikUrl();

        $view->themeStyles = $themeStyles;
        $view->emailStyles = $emailStyles;

        $view->fontStyle = 'color:' . $themeStyles->colorText . ';font-family:' . $themeStyles->fontFamilyBase.';';
        $view->styleParagraphText = 'font-size:15px;line-height:24px;';
        $view->styleParagraph = $view->styleParagraphText . 'margin:0 0 16px;';

        $customLogo = new CustomLogo();
        $view->isCustomLogo = $customLogo->isEnabled() && CustomLogo::hasUserLogo();
        $view->logoHeader = $customLogo->getHeaderLogoUrl($pathOnly = false);

        $pluginManager = Manager::getInstance();

        $view->hasWhiteLabel = $pluginManager->isPluginLoaded('WhiteLabel')
            && $pluginManager->isPluginActivated('WhiteLabel')
            && $pluginManager->isPluginInFilesystem('WhiteLabel');

        $view->idSite = Common::getRequestVar('idSite', false);
    }

    private static function getPeriodToFrequencyAsAdjective()
    {
        return array_map(['\Piwik\Piwik', 'translate'], self::$reportFrequencyTranslationByPeriod);
    }
}
