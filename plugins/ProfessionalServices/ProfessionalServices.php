<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ProfessionalServices;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\DataTable;
use Piwik\Log\LoggerInterface;
use Piwik\Piwik;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\DailyTriggerCache;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\PromotionRenderer;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\PromotionSelector;
use Piwik\Plugins\ProfessionalServices\PluginPromotions\UserPromotionState;
use Piwik\View;
use Piwik\Plugin;

class ProfessionalServices extends \Piwik\Plugin
{
    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            'Template.beforeDashboardWidgets' => 'renderDashboardPromotion',
            'SitesManager.deleteSite.end' => 'deletePromotionTriggerCache',
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
        $stylesheets[] = 'plugins/ProfessionalServices/stylesheets/productPromotion.less';
    }

    /**
     * Renders at most one contextual plugin promotion above the widgets of the default or
     * a custom dashboard. Every dashboard is rendered through this template, and no other
     * page is, so the promotion cannot leak onto All Websites or a report page.
     */
    public function renderDashboardPromotion(&$out)
    {
        if (!$this->isTheAppsOwnDashboard() || !$this->canBuildPromotions()) {
            return;
        }

        try {
            $selected = StaticContainer::get(PromotionSelector::class)->select();

            if (null === $selected) {
                return;
            }

            StaticContainer::get(UserPromotionState::class)->recordShown(
                $selected->getPromotion()->getPluginName(),
                $selected->getPromotion()->getTriggerName(),
                $selected->getTriggerResult()->toArray()
            );

            $out .= StaticContainer::get(PromotionRenderer::class)->render($selected);
        } catch (\Throwable $e) {
            // A promotion is never important enough to break a dashboard. Throwable rather
            // than Exception because the likeliest failure here is not an exception at
            // all: the copy carries placeholders, and translating a string whose
            // translation has lost one throws a ValueError out of sprintf.
            // `{exception}` rather than `{message}`: ExceptionToTextProcessor replaces the
            // whole message with the formatted exception when the context carries one and
            // the message does not name it, which would throw this sentence away.
            StaticContainer::get(LoggerInterface::class)->warning(
                'Could not render the dashboard plugin promotion: {exception}',
                ['exception' => $e]
            );
        }
    }

    /**
     * Whether the promotions can be constructed at all.
     *
     * Asking the container for the selector builds the registry, which builds all 22
     * triggers, three of which reach `Marketplace\Api\Service` - and its `$domain` is
     * defined in the Marketplace plugin's own DI config, which is only loaded while that
     * plugin is activated. Without this gate a deactivated Marketplace turns every
     * dashboard render into a PHP-DI definition error; the catch below would log it, and
     * on the default `log_writers[] = screen` that dump lands on the page itself.
     *
     * Checked before the container is touched, because by the time an exception is thrown
     * the damage is already a warning notification on a page the user did nothing to
     * deserve.
     */
    private function canBuildPromotions(): bool
    {
        return Plugin\Manager::getInstance()->isPluginActivated('Marketplace');
    }

    /**
     * Whether this render is the dashboard inside the app, as opposed to a copy of it
     * somewhere the promotion has no business being.
     *
     * `Dashboard/index.twig` includes the same template to build the exported widget, and
     * Widgetize can render it into an iframe on someone else's page. Both post this event,
     * neither is a place to advertise: the reader may not even be a Matomo user, and the
     * dismiss control writes to whoever's session happens to be behind the export.
     */
    private function isTheAppsOwnDashboard(): bool
    {
        return Piwik::getModule() === 'Dashboard' && Piwik::getAction() === 'embeddedIndex';
    }

    public function deletePromotionTriggerCache($idSite)
    {
        DailyTriggerCache::deleteForSite((int) $idSite);
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = 'Marketplace_RequestTrialSubmitted';
        $translationKeys[] = 'Marketplace_TrialRequested';
        $translationKeys[] = 'ProfessionalServices_DismissedNotification';
        $translationKeys[] = 'ProfessionalServices_PromotionTrialRequestFailed';
        $translationKeys[] = 'ProfessionalServices_PromotionDismissFailed';
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
}
