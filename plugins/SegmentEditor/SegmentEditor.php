<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\SegmentEditor;

use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Plugins\CoreHome\SystemSummary;

/**
 */
class SegmentEditor extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            'Segments.getKnownSegmentsToArchiveForSite'  => 'getKnownSegmentsToArchiveForSite',
            'Segments.getKnownSegmentsToArchiveAllSites' => 'getKnownSegmentsToArchiveAllSites',
            'AssetManager.getJavaScriptFiles'            => 'getJsFiles',
            'AssetManager.getStylesheetFiles'            => 'getStylesheetFiles',
            'Template.nextToCalendar'                    => 'getSegmentEditorHtml',
            'System.addSystemSummaryItems'               => 'addSystemSummaryItems',
            'Translate.getClientSideTranslationKeys'     => 'getClientSideTranslationKeys',
        );
    }

    public function addSystemSummaryItems(&$systemSummary)
    {
        $storedSegments = StaticContainer::get('Piwik\Plugins\SegmentEditor\Services\StoredSegmentService');
        $segments = $storedSegments->getAllSegmentsAndIgnoreVisibility();
        $numSegments = count($segments);
        $systemSummary[] = new SystemSummary\Item($key = 'segments', Piwik::translate('CoreHome_SystemSummaryNSegments', $numSegments), $value = null, $url = null, $icon = 'icon-segment', $order = 6);
    }

    function getSegmentEditorHtml(&$out)
    {
        $selector = new SegmentSelectorControl();
        $out .= $selector->render();
    }

    public function getKnownSegmentsToArchiveAllSites(&$segments)
    {
        $this->getKnownSegmentsToArchiveForSite($segments, $idSite = false);
    }

    /**
     * Adds the pre-processed segments to the list of Segments.
     * Used by CronArchive, ArchiveProcessor\Rules, etc.
     *
     * @param $segments
     * @param $idSite
     */
    public function getKnownSegmentsToArchiveForSite(&$segments, $idSite)
    {
        $model = new Model();
        $segmentToAutoArchive = $model->getSegmentsToAutoArchive($idSite);

        foreach ($segmentToAutoArchive as $segmentInfo) {
            $segments[] = $segmentInfo['definition'];
        }

        $segments = array_unique($segments);
    }

    public function install()
    {
        Model::install();
    }

    public function getJsFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/SegmentEditor/javascripts/Segmentation.js";
        $jsFiles[] = "plugins/SegmentEditor/angularjs/segment-generator/segmentgenerator-model.js";
        $jsFiles[] = "plugins/SegmentEditor/angularjs/segment-generator/segmentgenerator.controller.js";
        $jsFiles[] = "plugins/SegmentEditor/angularjs/segment-generator/segmentgenerator.directive.js";
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/SegmentEditor/stylesheets/segmentation.less";
        $stylesheets[] = "plugins/SegmentEditor/angularjs/segment-generator/segmentgenerator.directive.less";
    }

    /**
     * Returns whether adding segments for all websites is enabled or not.
     *
     * @return bool
     */
    public static function isAddingSegmentsForAllWebsitesEnabled()
    {
        return Config::getInstance()->General['allow_adding_segments_for_all_websites'] == 1;
    }

    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = 'SegmentEditor_CustomSegment';
        $translationKeys[] = 'SegmentEditor_VisibleToSuperUser';
        $translationKeys[] = 'SegmentEditor_SharedWithYou';
        $translationKeys[] = 'SegmentEditor_ChooseASegment';
        $translationKeys[] = 'SegmentEditor_CurrentlySelectedSegment';
        $translationKeys[] = 'SegmentEditor_OperatorAND';
        $translationKeys[] = 'SegmentEditor_OperatorOR';
        $translationKeys[] = 'SegmentEditor_AddANDorORCondition';
        $translationKeys[] = 'General_OperationEquals';
        $translationKeys[] = 'General_OperationNotEquals';
        $translationKeys[] = 'General_OperationAtMost';
        $translationKeys[] = 'General_OperationAtLeast';
        $translationKeys[] = 'General_OperationLessThan';
        $translationKeys[] = 'General_OperationGreaterThan';
        $translationKeys[] = 'General_OperationIs';
        $translationKeys[] = 'General_OperationIsNot';
        $translationKeys[] = 'General_OperationContains';
        $translationKeys[] = 'General_OperationDoesNotContain';
        $translationKeys[] = 'General_OperationStartsWith';
        $translationKeys[] = 'General_OperationEndsWith';
    }
}
