<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Live;

use Piwik\Cache;
use Piwik\CacheId;
use Piwik\API\Request;
use Piwik\Common;
use Piwik\Container\StaticContainer;

/**
 *
 */
class Live extends \Piwik\Plugin
{
    const ProfileEnabledCacheKey = 'Live.ProfileEnabled';
    const LogEnabledCacheKey = 'Live.LogEnabled';
    const CurrentSiteCacheKey = 'Live.CurrentSite';

    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'AssetManager.getJavaScriptFiles'        => 'getJsFiles',
            'AssetManager.getStylesheetFiles'        => 'getStylesheetFiles',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'Live.renderAction'                      => 'renderAction',
            'Live.renderActionTooltip'               => 'renderActionTooltip',
            'Live.renderVisitorDetails'              => 'renderVisitorDetails',
            'Live.renderVisitorIcons'                => 'renderVisitorIcons',
            'Template.jsGlobalVariables'             => 'addJsGlobalVariables',
            'API.getPagesComparisonsDisabledFor'     => 'getPagesComparisonsDisabledFor',
        );
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
        piwik.visitorLogEnabled = ".json_encode(self::isVisitorLogEnabled()).";
        piwik.visitorProfileEnabled = ".json_encode(self::isVisitorProfileEnabled()).";
        piwik.visitorLogActionsToDisplayCollapsed = $actionsToDisplayCollapsed;
        ";
    }

    public static function isVisitorLogEnabled($idSite = null)
    {
        [$profileEnabled, $logEnabled] = self::getSettings($idSite);

        return $logEnabled;
    }

    public static function isVisitorProfileEnabled($idSite = null)
    {
        [$profileEnabled, $logEnabled] = self::getSettings($idSite);

        return $profileEnabled;
    }

    private static function getSettings($idSite = null)
    {
        if (empty($idSite)) {
            $idSite = Common::getRequestVar('idSite', 0, 'int');
        }

        $cache = Cache::getTransientCache();
        $siteIdLoaded = $cache->fetch(self::CurrentSiteCacheKey);
        $visitorProfileCached = $cache->contains(self::ProfileEnabledCacheKey);
        $visitorLogCached = $cache->contains(self::LogEnabledCacheKey);

        if ($visitorProfileCached && $visitorLogCached && $idSite == $siteIdLoaded) {
            return [
                $cache->fetch(self::ProfileEnabledCacheKey),
                $cache->fetch(self::LogEnabledCacheKey),
            ];
        }

        $siteIdLoaded = $idSite;
        $visitorProfileEnabled = true;
        $visitorLogEnabled = true;

        try {
            if (!empty($idSite)) {
                $settings = new MeasurableSettings($idSite);

                $visitorProfileEnabled = $settings->activateVisitorProfile->getValue();
                $visitorLogEnabled     = $settings->activateVisitorLog->getValue();
            }

            $systemSettings = new SystemSettings();

            if ($systemSettings->activateVisitorProfile->getValue() === false) {
                $visitorProfileEnabled = false;
            }

            if ($systemSettings->activateVisitorLog->getValue() === false) {
                $visitorLogEnabled = false;
            }

            $cache->save(self::CurrentSiteCacheKey, $siteIdLoaded);
            $cache->save(self::ProfileEnabledCacheKey, $visitorProfileEnabled);
            $cache->save(self::LogEnabledCacheKey, $visitorLogEnabled);
        } catch (\Exception $e) {
            // method might be called in a state where site can't be loaded (e.g. missing or outdated authentication)
            // so simply ignore errors
        }

        return [$visitorProfileEnabled, $visitorLogEnabled];
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/Live/stylesheets/live.less";
        $stylesheets[] = "plugins/Live/stylesheets/visitor_profile.less";
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "node_modules/visibilityjs/lib/visibility.core.js";
        $jsFiles[] = "plugins/Live/javascripts/live.js";
        $jsFiles[] = "plugins/Live/javascripts/SegmentedVisitorLog.js";
        $jsFiles[] = "plugins/Live/javascripts/visitorActions.js";
        $jsFiles[] = "plugins/Live/javascripts/visitorProfile.js";
        $jsFiles[] = "plugins/Live/javascripts/visitorLog.js";
        $jsFiles[] = "plugins/Live/javascripts/rowaction.js";
        $jsFiles[] = "plugins/Live/angularjs/live-widget-refresh/live-widget-refresh.directive.js";
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = "Live_VisitorProfile";
        $translationKeys[] = "Live_ClickToViewAllActions";
        $translationKeys[] = "Live_NoMoreVisits";
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

        usort($detailEntries, function($a, $b) {
            return version_compare($a[0], $b[0]);
        });

        foreach ($detailEntries AS $detailEntry) {
            $tooltip .= $detailEntry[1];
        }
    }

    public function renderVisitorDetails(&$renderedDetails, $visitorDetails)
    {
        $detailEntries = [];
        $visitorDetailsInstances = Visitor::getAllVisitorDetailsInstances();

        foreach ($visitorDetailsInstances as $instance) {
            $detailEntries = array_merge($detailEntries, $instance->renderVisitorDetails($visitorDetails));
        }

        usort($detailEntries, function($a, $b) {
            return version_compare($a[0], $b[0]);
        });

        foreach ($detailEntries AS $detailEntry) {
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