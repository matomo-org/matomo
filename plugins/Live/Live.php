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
use Piwik\API\Request;
use Piwik\Common;
use Piwik\Container\StaticContainer;

/**
 *
 */
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
        piwik.visitorLogEnabled = " . json_encode(self::isVisitorLogEnabled()) . ";
        piwik.visitorProfileEnabled = " . json_encode(self::isVisitorProfileEnabled()) . ";
        piwik.visitorLogActionsToDisplayCollapsed = $actionsToDisplayCollapsed;
        ";
    }

    /**
     * Throws an exception if visits log is disabled
     *
     * @param null|int|array $idSite
     * @throws \Exception
     */
    public static function checkIsVisitorLogEnabled($idSite = null): void
    {
        $systemSettings = new SystemSettings();

        if ($systemSettings->disableVisitorLog->getValue() === true) {
            throw new \Exception('Visits log is deactivated globally. A user with super user access can enable this feature in the general settings.');
        }

        if (empty($idSite)) {
            $idSite = Common::getRequestVar('idSite', 0, 'int');
        }

        if (!empty($idSite)) {
            $idSites = is_array($idSite) ? $idSite : [$idSite];

            foreach ($idSites as $idSite) {
                $settings = new MeasurableSettings($idSite);

                if ($settings->disableVisitorLog->getValue() === true) {
                    throw new \Exception('Visits log is deactivated in website settings. A user with at least admin access can enable this feature in the settings for this website (idSite=' . $idSite . ').');
                }
            }
        }
    }

    /**
     * Returns whether visits log is enabled (for the given site)
     *
     * @param null|int|array $idSite
     * @return bool
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
     * Throws an exception if visitor profile is disabled
     *
     * @param null|int|array $idSite
     * @throws \Exception
     */
    public static function checkIsVisitorProfileEnabled($idSite = null): void
    {
        self::checkIsVisitorLogEnabled($idSite); // visitor log is required for visitor profile

        $systemSettings = new SystemSettings();

        if ($systemSettings->disableVisitorProfile->getValue() === true) {
            throw new \Exception('Visitor profile is deactivated globally. A user with super user access can enable this feature in the general settings.');
        }

        if (empty($idSite)) {
            $idSite = Common::getRequestVar('idSite', 0, 'int');
        }

        if (!empty($idSite)) {
            $idSites = is_array($idSite) ? $idSite : [$idSite];

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
     * @param null|int|array $idSite
     * @return bool
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

        usort($detailEntries, function ($a, $b) {
            return version_compare($a[0], $b[0]);
        });

        foreach ($detailEntries as $detailEntry) {
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
