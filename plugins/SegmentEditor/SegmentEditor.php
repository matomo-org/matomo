<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\SegmentEditor;

use Piwik\API\Request;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\DataAccess\ArchiveSelector;
use Piwik\Notification;
use Piwik\Piwik;
use Piwik\Plugins\CoreHome\SystemSummary;
use Piwik\Segment;
use Piwik\SettingsPiwik;
use Piwik\SettingsServer;
use Piwik\Site;
use Piwik\Period;
use Piwik\Url;
use Piwik\View;

/**
 */
class SegmentEditor extends \Piwik\Plugin
{
    const NO_DATA_UNPROCESSED_SEGMENT_ID = 'nodata_segment_not_processed';

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
            'Visualization.onLoadingError' => 'onLoadingError',
            'Archive.noArchivedData' => 'onNoArchiveData',
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

    public function onNoArchiveData()
    {
        $segmentInfo = $this->getSegmentIfIsUnprocessed();
        if (empty($segmentInfo)) {
            return;
        }

        list($segment, $isSegmentToPreprocess) = $segmentInfo;

        // this archive has no data, the report is for a segment that gets preprocessed, and the archive for this
        // data does not exist. this means the data will be processed later. we let the user know so they will not
        // be confused.
        $model = new Model();
        $storedSegment = $model->getSegmentByDefinition($segment->getString()) ?: null;

        throw new UnprocessedSegmentException($segment, $isSegmentToPreprocess, $storedSegment);
    }

    public function onLoadingError(\Exception $ex, View $dataTableView)
    {
        if (!($ex instanceof UnprocessedSegmentException)) {
            return;
        }

        $segment = $ex->getSegment();
        $segmentToPreprocess = $ex->getStoredSegment();

        $segmentDisplayName = !empty($segmentToPreprocess['name']) ? $segmentToPreprocess['name'] : $segment->getString();

        $view = new View('@SegmentEditor/_unprocessedSegmentMessage.twig');
        $view->isSegmentToPreprocess = $ex->isSegmentToPreprocess();
        $view->segmentName = $segmentDisplayName;
        $view->visitorLogLink = '#' . Url::getCurrentQueryStringWithParametersModified([
            'category' => 'General_Visitors',
            'subcategory' => 'Live_VisitorLog',
        ]);

        $notification = new Notification($view->render());
        $notification->priority = Notification::PRIORITY_HIGH;
        $notification->context = Notification::CONTEXT_INFO;
        $notification->flags = Notification::FLAG_NO_CLEAR;
        $notification->type = Notification::TYPE_TRANSIENT;
        $notification->raw = true;

        unset($dataTableView->error); // don't display the API error
        $dataTableView->notifications[self::NO_DATA_UNPROCESSED_SEGMENT_ID] = $notification;
    }

    private function getSegmentIfIsUnprocessed()
    {
        // don't do check unless this is the root API request
        if (Request::isRootApiRequestHandlingMethod()) {
            return null;
        }

        // don't do check during cron archiving
        if (SettingsServer::isArchivePhpTriggered()) {
            return null;
        }

        // get idSites
        $idSite = Common::getRequestVar('idSite', false);
        if (empty($idSite)
            || !is_numeric($idSite)
        ) {
            return null;
        }

        // get segment
        $segment = Request::getRawSegmentFromRequest();
        if (empty($segment)) {
            return null;
        }
        $segment = new Segment($segment, [$idSite]);

        // get period
        $date = Common::getRequestVar('date', false);
        $period = Common::getRequestVar('period', false);
        $period = Period\Factory::build($period, $date);

        // check if archiving is enabled. if so, the segment should have been processed.
        $isArchivingDisabled = Rules::isArchivingDisabledFor([$idSite], $segment, $period);
        if (!$isArchivingDisabled) {
            return null;
        }

        // check if requested segment is segment to preprocess
        $isSegmentToPreprocess = Rules::isSegmentPreProcessed([$idSite], $segment);

        return [$segment, $isSegmentToPreprocess];
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
