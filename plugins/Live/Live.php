<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Live;

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
        return array(
            'AssetManager.getJavaScriptFiles'        => 'getJsFiles',
            'AssetManager.getStylesheetFiles'        => 'getStylesheetFiles',
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys',
            'Live.renderAction'                      => 'renderAction',
            'Live.renderActionTooltip'               => 'renderActionTooltip',
            'Live.renderVisitorDetails'              => 'renderVisitorDetails',
            'Live.renderVisitorIcons'                => 'renderVisitorIcons',
        );
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/Live/stylesheets/live.less";
        $stylesheets[] = "plugins/Live/stylesheets/visitor_profile.less";
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "libs/bower_components/visibilityjs/lib/visibility.core.js";
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
}