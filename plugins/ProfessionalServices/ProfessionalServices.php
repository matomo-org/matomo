<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices;

use Piwik\Common;
use Piwik\DataTable;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\View;
use Piwik\Plugin;

class ProfessionalServices extends \Piwik\Plugin
{
    private const DISMISSED_WIDGET_OPTION_NAME = 'ProfessionalServices.DismissedWidget.%s.%s';

    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'Template.afterGoalConversionOverviewReport' => array('function' => 'getGoalOverviewPromo', 'after' => true),
            'Template.afterGoalCannotAddNewGoal' => array('function' => 'getGoalOverviewPromo', 'after' => true),
            'Template.endGoalEditTable' => array('function' => 'getGoalFunnelOverviewPromo', 'after' => true),
            'Template.afterEventsReport' => 'getEventsPromo',
            'Template.afterCampaignsReport' => 'getCampaignsPromo',
            'Template.afterReferrerTypeReport' => 'getReferrerTypePromo',
            'Template.afterReferrersKeywordsReport' => 'getSearchKeywordsPerformancePromo',
            'Template.afterCustomVariablesReport' => 'getCustomVariablesPromo',
            'Template.afterOverlaySidebar' => 'getHeatmapPromo',
            'Template.afterVisitorProfileOverview' => 'getSessionRecordingPromo',
            'Template.afterPagePerformanceReport' => 'getSeoWebVitalsPromo',
            'Template.afterSearchEngines' => 'getSeoWebVitalsPromo',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
        );
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = 'plugins/ProfessionalServices/stylesheets/promos.less';
        $stylesheets[] = 'plugins/ProfessionalServices/stylesheets/widget.less';
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = 'ProfessionalServices_DismissedNotification';
        $translationKeys[] = 'ProfessionalServices_PromoFunnels';
        $translationKeys[] = 'ProfessionalServices_PromoFormAnalytics';
        $translationKeys[] = 'ProfessionalServices_PromoMediaAnalytics';
        $translationKeys[] = 'ProfessionalServices_PromoAbTesting';
        $translationKeys[] = 'ProfessionalServices_PromoHeatmaps';
        $translationKeys[] = 'ProfessionalServices_PromoSessionRecording';
        $translationKeys[] = 'ProfessionalServices_PromoCustomReports';
        $translationKeys[] = 'ProfessionalServices_PromoCrashAnalytics';
    }

    public function isRequestForDashboardWidget()
    {
        $isWidget = Common::getRequestVar('widget', 0, 'int');
        return $isWidget;
    }

    public function getHeatmapPromo(&$out)
    {
        if (!$this->shouldShowPromoForPlugin('HeatmapSessionRecording')) {
            return;
        }

        $view = new View('@ProfessionalServices/promoHeatmaps');
        $out .= $view->render();
    }

    public function getSessionRecordingPromo(&$out)
    {
        if (!$this->shouldShowPromoForPlugin('HeatmapSessionRecording')) {
            return;
        }

        $view = new View('@ProfessionalServices/promoSessionRecordings');
        $out .= $view->render();
    }

    public function getSearchKeywordsPerformancePromo(&$out)
    {
        if (!$this->shouldShowPromoForPlugin('SearchEngineKeywordsPerformance')) {
            return;
        }

        $view = new View('@ProfessionalServices/promoSearchKeywords');
        $out .= $view->render();
    }

    public function getGoalFunnelOverviewPromo(&$out)
    {
        if ($this->shouldShowPromoForPlugin('Funnels')) {
            $view = new View('@ProfessionalServices/promoFunnel');
            $out .= $view->render();
        }
    }

    public function getGoalOverviewPromo(&$out)
    {
        if ($this->shouldShowPromoForPlugin('AbTesting')) {
            $view = new View('@ProfessionalServices/promoExperiments.twig');
            $out .= $view->render();
        }
    }

    public function getCustomVariablesPromo(&$out)
    {
        if ($this->shouldShowPromoForPlugin('CustomReports')) {
            $view = new View('@ProfessionalServices/promoCustomVariables.twig');
            $out .= $view->render();
        }
    }

    public function getEventsPromo(&$out, DataTable $dataTable)
    {
        if ($this->isRequestForDashboardWidget()) {
            return;
        }

        $promoView = new View('@ProfessionalServices/promoBelowEvents');
        $promoView->displayMediaAnalyticsAd = !$this->isPluginActivated('MediaAnalytics');
        $promoView->displayCrashAnalyticsAd = !$this->isPluginActivated('CrashAnalytics') && $this->hasErrorEventCategory($dataTable);
        $out .= $promoView->render();
    }

    private function hasErrorEventCategory(DataTable $dataTable): bool
    {
        return $dataTable->getRowIdFromLabel('JavaScript Errors') !== false;
    }

    public function getCampaignsPromo(&$out)
    {
        if ($this->isRequestForDashboardWidget()) {
            return;
        }

        $view = new View('@ProfessionalServices/promoBelowCampaigns');
        $view->displayMarketingCampaignsReportingAd = !$this->isPluginActivated('MarketingCampaignsReporting');
        $view->multiChannelConversionAttributionAd = !$this->isPluginActivated('MultiChannelConversionAttribution') && !empty($_REQUEST['idGoal']);
        $out .= $view->render();
    }

    public function getReferrerTypePromo(&$out)
    {
        if ($this->shouldShowPromoForPlugin('MultiChannelConversionAttribution') && !empty($_REQUEST['idGoal'])) {
            $view = new View('@ProfessionalServices/promoBelowReferrerTypes');
            $out .= $view->render();
        }
    }

    private function shouldShowPromoForPlugin($pluginName)
    {
        return !$this->isPluginActivated($pluginName) && !$this->isRequestForDashboardWidget();
    }

    private function isPluginActivated($pluginName)
    {
        return Plugin\Manager::getInstance()->isPluginActivated($pluginName);
    }

    public function getSeoWebVitalsPromo(&$out)
    {
        if ($this->shouldShowPromoForPlugin('SEOWebVitals')) {
            $view = new View('@ProfessionalServices/promoSEOWebVitals');
            $out .= $view->render();
        }
    }

    public static function dismissPromoWidget(string $widgetName): void
    {
        Option::set(self::getDismissedWidgetOptionName($widgetName), time());
    }

    public static function isPromoWidgetDismissed(string $widgetName): bool
    {
        return Option::get(self::getDismissedWidgetOptionName($widgetName)) > 0;
    }

    private static function getDismissedWidgetOptionName(string $widgetName): string
    {
        return sprintf(self::DISMISSED_WIDGET_OPTION_NAME, $widgetName, Piwik::getCurrentUserLogin());
    }
}
