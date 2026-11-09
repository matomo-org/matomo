<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Live;

use Piwik\Cache;
use Piwik\API\Request;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Plugins\CoreHome\CoreHome;
use Piwik\Plugins\SegmentEditor\Settings\LimitSegments;
use Piwik\Policy\PolicyEnforcementBypass;
use Piwik\Site;
use Piwik\Plugins\Live\Settings\VisitorLogDisabled as VisitorLogDisabledSetting;

class Live extends \Piwik\Plugin
{
    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return [
            'AssetManager.getJavaScriptFiles'        => 'getJsFiles',
            'AssetManager.getStylesheetFiles'        => 'getStylesheetFiles',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'Live.renderAction'                      => 'renderAction',
            'Live.renderActionTooltip'               => 'renderActionTooltip',
            'Live.renderVisitorDetails'              => 'renderVisitorDetails',
            'Live.renderVisitorIcons'                => 'renderVisitorIcons',
            'Template.jsGlobalVariables'             => 'addJsGlobalVariables',
            'API.getPagesComparisonsDisabledFor'     => 'getPagesComparisonsDisabledFor',
        ];
    }

    public function getPagesComparisonsDisabledFor(&$pages)
    {
        $pages[] = 'General_Visitors.Live_VisitorLog';
        $pages[] = 'General_Visitors.General_RealTime';
    }

    public function addJsGlobalVariables(&$out)
    {
        $actionsToDisplayCollapsed = (int)StaticContainer::get('Live.pageViewActionsToDisplayCollapsed');
        $out .= "
        piwik.visitorLogEnabled = " . json_encode(self::isVisitorLogAvailable()) . ";
        piwik.visitorProfileEnabled = " . json_encode(self::isVisitorProfileAvailable()) . ";
        piwik.visitorLogUnavailableSegments = " . json_encode(self::getSegmentsUnavailableToTheVisitorLog()) . ";
        piwik.visitorLogActionsToDisplayCollapsed = $actionsToDisplayCollapsed;
        ";
    }

    /**
     * Returns the segments the visits log of the requested site cannot filter by while it only lists the visits of
     * visitors who gave consent, as the visitor profile is not enabled and the compliance policy may limit segments
     *
     * @return string[]
     */
    private static function getSegmentsUnavailableToTheVisitorLog(): array
    {
        $idSite = (int) Common::getRequestVar('idSite', 0, 'int');
        if ($idSite <= 0 || !self::isVisitorLogLimitedToConsentedVisits($idSite)) {
            return [];
        }

        $segments = ['userId', 'visitorId'];
        if (LimitSegments::getInstance($idSite)->getValue()) {
            $segments = array_merge($segments, array_column(CoreHome::SEGMENTS_REMOVED_BY_LIMIT_SEGMENTS, 1));
        }

        return array_values(array_unique($segments));
    }

    /**
     * Throws an exception if visits log is disabled
     *
     * @param int|string|int[]|null $idSite
     * @throws \Exception
     */
    public static function checkIsVisitorLogEnabled($idSite = null): void
    {
        if (VisitorLogDisabledSetting::getInstance()->getValue() === true) {
            throw new \Exception('Visits log is deactivated globally. A user with super user access can enable this feature in the general settings.');
        }

        if (empty($idSite)) {
            $idSite = Common::getRequestVar('idSite', '', 'string');
        }

        if (!empty($idSite)) {
            $idSites = Site::getIdSitesFromIdSitesString($idSite);

            foreach ($idSites as $idSite) {
                if (VisitorLogDisabledSetting::getInstance($idSite)->getValue() === true) {
                    throw new \Exception('Visits log is deactivated in website settings. A user with at least admin access can enable this feature in the settings for this website (idSite=' . $idSite . ').');
                }
            }
        }
    }

    /**
     * Returns whether visits log is enabled (for the given site)
     *
     * @param int|string|int[]|null $idSite
     */
    public static function isVisitorLogEnabled($idSite = null): bool
    {
        try {
            self::checkIsVisitorLogEnabled($idSite);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Returns whether the real-time widget should be limited to the aggregated counters only,
     * i.e. the detailed visits log can list no visit (for the given site).
     */
    public static function shouldShowAggregatedRealtimeOnly(?int $idSite = null): bool
    {
        return !self::isVisitorLogAvailable($idSite);
    }

    /**
     * Throws an exception if visitor profile is disabled
     *
     * @param int|string|int[]|null $idSite
     * @throws \Exception
     */
    public static function checkIsVisitorProfileEnabled($idSite = null): void
    {
        self::checkIsVisitorLogEnabled($idSite); // visitor log is required for visitor profile
        self::checkIsVisitorProfileSettingEnabled($idSite);
    }

    /**
     * Throws an exception if visitor profile is disabled by the system or website settings, without looking at
     * whether the visitor log is enabled
     *
     * @param int|string|int[]|null $idSite
     * @throws \Exception
     */
    public static function checkIsVisitorProfileSettingEnabled($idSite = null): void
    {
        $systemSettings = new SystemSettings();

        if ($systemSettings->disableVisitorProfile->getValue() === true) {
            throw new \Exception('Visitor profile is deactivated globally. A user with super user access can enable this feature in the general settings.');
        }

        if (empty($idSite)) {
            $idSite = Common::getRequestVar('idSite', '', 'string');
        }

        if (!empty($idSite)) {
            $idSites = Site::getIdSitesFromIdSitesString($idSite);

            foreach ($idSites as $idSite) {
                $settings = new MeasurableSettings($idSite);

                if ($settings->disableVisitorProfile->getValue() === true) {
                    throw new \Exception('Visitor profile is deactivated in website settings. A user with at least admin access can enable this feature in the settings for this website (idSite=' . $idSite . ').');
                }
            }
        }
    }

    /**
     * Returns whether visitor profile is enabled (for the given site)
     *
     * @param int|string|int[]|null $idSite
     */
    public static function isVisitorProfileEnabled($idSite = null): bool
    {
        try {
            self::checkIsVisitorProfileEnabled($idSite);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Returns whether visitor profile is enabled by the system and website settings (for the given site), without
     * looking at whether the visitor log is enabled
     *
     * @param int|string|int[]|null $idSite
     */
    public static function isVisitorProfileSettingEnabled($idSite = null): bool
    {
        try {
            self::checkIsVisitorProfileSettingEnabled($idSite);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Returns whether only the visits of visitors who gave consent may be looked at one by one on the given site:
     * its compliance policy disables the visits log, while neither its own settings nor the system settings disable
     * the visits log or the visitor profile. Those visits may hold identifying data, such as a User ID, so they must
     * stay reachable to answer their visitors' requests to access or erase their data.
     */
    public static function isVisitorProfileLimitedToConsentedVisits(int $idSite): bool
    {
        return self::isVisitorLogLimitedToConsentedVisits($idSite)
            && self::isVisitorProfileSettingEnabled($idSite);
    }

    /**
     * Returns whether the visits log of the given site may only list the visits of visitors who gave consent: its
     * compliance policy disables the visits log, while neither its own settings nor the system settings do.
     */
    public static function isVisitorLogLimitedToConsentedVisits(int $idSite): bool
    {
        if (PolicyEnforcementBypass::isActive()) {
            // the policy does not apply, as for the visits log setting itself
            return false;
        }

        return VisitorLogDisabledSetting::isEnforced($idSite)
            && true !== VisitorLogDisabledSetting::getSystemValue()
            && true !== VisitorLogDisabledSetting::getMeasurableValue($idSite);
    }

    /**
     * Throws an exception if the visits log can list no visit at all, neither every visit nor only the visits of
     * visitors who gave consent (see {@link isVisitorLogLimitedToConsentedVisits()})
     *
     * @param int|string|int[]|null $idSite
     * @throws \Exception
     */
    public static function checkIsVisitorLogAvailable($idSite = null): void
    {
        try {
            self::checkIsVisitorLogEnabled($idSite);
        } catch (\Exception $e) {
            $idSites = self::getIdSitesFromParameterOrRequest($idSite);
            if (empty($idSites)) {
                throw $e;
            }

            foreach ($idSites as $siteId) {
                if (!self::isVisitorLogEnabled($siteId) && !self::isVisitorLogLimitedToConsentedVisits($siteId)) {
                    throw $e;
                }
            }
        }
    }

    /**
     * Returns whether the visits log can list visits (for the given site), every visit or only the visits of
     * visitors who gave consent
     *
     * @param int|string|int[]|null $idSite
     */
    public static function isVisitorLogAvailable($idSite = null): bool
    {
        try {
            self::checkIsVisitorLogAvailable($idSite);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Throws an exception if the visitor profile can show no visitor at all, neither every visitor nor only the
     * visitors who gave consent (see {@link isVisitorProfileLimitedToConsentedVisits()})
     *
     * @param int|string|int[]|null $idSite
     * @throws \Exception
     */
    public static function checkIsVisitorProfileAvailable($idSite = null): void
    {
        try {
            self::checkIsVisitorProfileEnabled($idSite);
        } catch (\Exception $e) {
            $idSites = self::getIdSitesFromParameterOrRequest($idSite);
            if (empty($idSites)) {
                throw $e;
            }

            foreach ($idSites as $siteId) {
                if (self::isVisitorProfileEnabled($siteId) || self::isVisitorProfileLimitedToConsentedVisits($siteId)) {
                    continue;
                }

                if (self::isVisitorLogLimitedToConsentedVisits($siteId)) {
                    // the profile is disabled by its own setting, tell so rather than that the visits log is
                    self::checkIsVisitorProfileSettingEnabled($siteId);
                }

                throw $e;
            }
        }
    }

    /**
     * Returns whether the visitor profile can show visitors (for the given site), every visitor or only the
     * visitors who gave consent
     *
     * @param int|string|int[]|null $idSite
     */
    public static function isVisitorProfileAvailable($idSite = null): bool
    {
        try {
            self::checkIsVisitorProfileAvailable($idSite);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * @param int|string|int[]|null $idSite
     * @return int[]
     */
    private static function getIdSitesFromParameterOrRequest($idSite): array
    {
        if (empty($idSite)) {
            $idSite = Common::getRequestVar('idSite', '', 'string');
        }

        if (empty($idSite)) {
            return [];
        }

        return array_map('intval', Site::getIdSitesFromIdSitesString($idSite));
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/Live/stylesheets/live.less";
        $stylesheets[] = "plugins/Live/stylesheets/visitor_profile.less";
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "node_modules/visibilityjs/lib/visibility.core.js";
        $jsFiles[] = "plugins/Live/javascripts/SegmentedVisitorLog.js";
        $jsFiles[] = "plugins/Live/javascripts/visitorActions.js";
        $jsFiles[] = "plugins/Live/javascripts/visitorProfile.js";
        $jsFiles[] = "plugins/Live/javascripts/visitorLog.js";
        $jsFiles[] = "plugins/Live/javascripts/rowaction.js";
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = "Live_VisitorProfile";
        $translationKeys[] = "Live_VisitorProfileNotFound";
        $translationKeys[] = 'General_ErrorRequest';
        $translationKeys[] = "Live_ClickToViewAllActions";
        $translationKeys[] = "Live_NoMoreVisits";
        $translationKeys[] = 'Live_OnlyConsentedVisitsShown';
        $translationKeys[] = "Live_ShowMap";
        $translationKeys[] = "Live_HideMap";
        $translationKeys[] = "Live_PageRefreshed";
        $translationKeys[] = "Live_RowActionTooltipTitle";
        $translationKeys[] = "Live_RowActionTooltipDefault";
        $translationKeys[] = "Live_RowActionTooltipWithDimension";
        $translationKeys[] = "Live_SegmentedVisitorLogTitle";
        $translationKeys[] = "General_Segment";
        $translationKeys[] = "General_And";
        $translationKeys[] = 'Live_ClickToSeeAllContents';
        $translationKeys[] = 'General_ColumnNbVisits';
        $translationKeys[] = 'Live_LastHours';
        $translationKeys[] = 'Live_LastMinutes';
        $translationKeys[] = 'Live_VisitorsInRealTime';
        $translationKeys[] = 'Live_OnClickPause';
        $translationKeys[] = 'Live_OnClickStart';
        $translationKeys[] = 'Live_LinkVisitorLog';
        $translationKeys[] = 'Live_VisitorLog';
        $translationKeys[] = 'General_ColumnNbVisitsDocumentation';
        $translationKeys[] = 'General_ColumnNbActionsDocumentation';
        $translationKeys[] = 'Live_RealTimeVisitorCount';
        $translationKeys[] = 'Live_NbVisitor';
        $translationKeys[] = 'Live_NbVisitors';
        $translationKeys[] = 'General_OneVisit';
        $translationKeys[] = 'General_NVisits';
        $translationKeys[] = 'General_OneAction';
        $translationKeys[] = 'VisitsSummary_NbActionsDescription';
        $translationKeys[] = 'Intl_OneMinute';
        $translationKeys[] = 'Intl_NMinutes';
        $translationKeys[] = 'Live_SimpleRealTimeWidget_Message';
        $translationKeys[] = 'Live_QueryMaxExecutionTimeExceeded';
    }

    public function renderAction(&$renderedAction, $action, $previousAction, $visitorDetails)
    {
        $visitorDetailsInstances = Visitor::getAllVisitorDetailsInstances();
        foreach ($visitorDetailsInstances as $instance) {
            $renderedAction .= $instance->renderAction($action, $previousAction, $visitorDetails);
        }
    }

    public function renderActionTooltip(&$tooltip, $action, $visitInfo)
    {
        $detailEntries = [];
        $visitorDetailsInstances = Visitor::getAllVisitorDetailsInstances();

        foreach ($visitorDetailsInstances as $instance) {
            $detailEntries = array_merge($detailEntries, $instance->renderActionTooltip($action, $visitInfo));
        }

        usort($detailEntries, function ($a, $b) {
            return version_compare($a[0], $b[0]);
        });

        foreach ($detailEntries as $detailEntry) {
            $tooltip .= self::escapeActionTooltipEntry($detailEntry[1]);
        }
    }

    /**
     * Escapes one entry of the visitor log action tooltip.
     *
     * The tooltip renders its title as HTML, so an entry needs two layers: one the attribute parse
     * consumes and one the render does. Escaping happens here rather than through `Common`, which
     * would also strip the line breaks the entries are separated by.
     *
     * @ignore
     */
    public static function escapeActionTooltipEntry(?string $entry): string
    {
        $text = html_entity_decode((string) $entry, ENT_QUOTES, 'UTF-8');

        return htmlspecialchars(htmlspecialchars($text, ENT_QUOTES, 'UTF-8'), ENT_QUOTES, 'UTF-8');
    }

    public function renderVisitorDetails(&$renderedDetails, $visitorDetails)
    {
        $detailEntries = [];
        $visitorDetailsInstances = Visitor::getAllVisitorDetailsInstances();

        foreach ($visitorDetailsInstances as $instance) {
            $detailEntries = array_merge($detailEntries, $instance->renderVisitorDetails($visitorDetails));
        }

        usort($detailEntries, function ($a, $b) {
            return version_compare($a[0], $b[0]);
        });

        foreach ($detailEntries as $detailEntry) {
            $renderedDetails .= $detailEntry[1];
        }
    }

    public function renderVisitorIcons(&$renderedDetails, $visitorDetails)
    {
        $visitorDetailsInstances = Visitor::getAllVisitorDetailsInstances();
        foreach ($visitorDetailsInstances as $instance) {
            $renderedDetails .= $instance->renderIcons($visitorDetails);
        }
    }

    /**
     * Returns the segment for the most recent visitor id
     *
     * This method uses the transient cache to ensure it returns always the same id within one request
     * as `Request::processRequest('Live.getMostRecentVisitorId')` might return different ids on each call
     *
     * @return mixed|string
     */
    public static function getSegmentWithVisitorId()
    {
        $cache   = Cache::getTransientCache();
        $cacheId = 'segmentWithVisitorId';

        if ($cache->contains($cacheId)) {
            return $cache->fetch($cacheId);
        }

        $segment = Request::getRawSegmentFromRequest();
        if (!empty($segment)) {
            $segment = urldecode($segment) . ';';
        }

        $idVisitor = Common::getRequestVar('visitorId', false);
        if ($idVisitor === false) {
            $idVisitor = Request::processRequest('Live.getMostRecentVisitorId');
        }

        $result = urlencode($segment . 'visitorId==' . $idVisitor);
        $cache->save($cacheId, $result);

        return $result;
    }
}
