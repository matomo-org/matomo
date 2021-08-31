<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\SegmentEditor;

use Piwik\API\Request;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Plugins\API\API as APIMetadata;
use Piwik\Plugins\Live\Live;
use Piwik\Plugins\UsersManager\API AS UsersManagerAPI;
use Piwik\View\UIControl;
use Piwik\Plugins\SegmentEditor\API as SegmentEditorAPI;

/**
 * Generates the HTML for the segment selector control (which includes the segment editor).
 */
class SegmentSelectorControl extends UIControl
{
    const TEMPLATE = "@SegmentEditor/_segmentSelector";

    /**
     * Constructor.
     */
    public function __construct($idSite = false)
    {
        parent::__construct();

        $this->jsClass = "SegmentSelectorControl";
        $this->cssIdentifier = "segmentEditorPanel";
        $this->cssClass = "piwikTopControl borderedControl piwikSelector";

        $this->idSite = $idSite ?: Common::getRequestVar('idSite', false, 'int');

        $this->selectedSegment = Common::getRequestVar('segment', false, 'string');

        $formatter = StaticContainer::get('Piwik\Plugins\SegmentEditor\SegmentFormatter');
        $this->segmentDescription = $formatter->getHumanReadable(Request::getRawSegmentFromRequest(), $this->idSite);

        $this->isAddingSegmentsForAllWebsitesEnabled = SegmentEditor::isAddingSegmentsForAllWebsitesEnabled();
        $this->isCreateRealtimeSegmentsEnabled = SegmentEditor::isCreateRealtimeSegmentsEnabled();

        $segments = APIMetadata::getInstance()->getSegmentsMetadata($this->idSite);

        $visitTitle = Piwik::translate('General_Visit');
        $segmentsByCategory = array();
        foreach ($segments as $segment) {
            if ($segment['category'] == $visitTitle
                && ($segment['type'] == 'metric' && $segment['segment'] != 'visitIp')
            ) {
                $metricsLabel = mb_strtolower(Piwik::translate('General_Metrics'));
                $segment['category'] .= ' (' . $metricsLabel . ')';
            }
            $segmentsByCategory[$segment['category']][] = $segment;
        }

        $this->createRealTimeSegmentsIsEnabled = $this->isCreatingRealTimeSegmentsEnabled();
        $this->segmentsByCategory   = $segmentsByCategory;
        $this->nameOfCurrentSegment = '';
        $this->isSegmentNotAppliedBecauseBrowserArchivingIsDisabled = 0;

        $this->availableSegments = Request::processRequest("SegmentEditor.getAll", ['idSite' => $this->idSite], $defaultRequest = []);
        foreach ($this->availableSegments as &$savedSegment) {
            $savedSegment['name'] = Common::sanitizeInputValue($savedSegment['name']);

            if (!empty($this->selectedSegment) && $this->selectedSegment == $savedSegment['definition']) {
                $this->nameOfCurrentSegment = $savedSegment['name'];
                $this->isSegmentNotAppliedBecauseBrowserArchivingIsDisabled =
                    $this->wouldApplySegment($savedSegment) ? 0 : 1;
            }
        }

        $this->authorizedToCreateSegments = SegmentEditorAPI::getInstance()->isUserCanAddNewSegment($this->idSite);
        $this->isUserAnonymous = Piwik::isUserIsAnonymous();
        $this->segmentTranslations = $this->getTranslations();
        $this->segmentProcessedOnRequest = Rules::isBrowserArchivingAvailableForSegments();
        $this->hideSegmentDefinitionChangeMessage = UsersManagerAPI::getInstance()->getUserPreference(
            'hideSegmentDefinitionChangeMessage',
            Piwik::getCurrentUserLogin()
        );
        $this->isBrowserArchivingEnabled = Rules::isBrowserTriggerEnabled();

        $this->isVisitorLogEnabled = Manager::getInstance()->isPluginActivated('Live') && Live::isVisitorLogEnabled($this->idSite);
    }

    public function getClientSideProperties()
    {
        return array('availableSegments',
                     'segmentTranslations',
                     'isSegmentNotAppliedBecauseBrowserArchivingIsDisabled',
                     'selectedSegment',
                     'authorizedToCreateSegments');
    }

    private function wouldApplySegment($savedSegment)
    {
        if (Rules::isBrowserArchivingAvailableForSegments()) {
            return true;
        }

        return (bool) $savedSegment['auto_archive'];
    }

    private function getTranslations()
    {
        $translationKeys = array(
            'General_OperationEquals',
            'General_OperationNotEquals',
            'General_OperationAtMost',
            'General_OperationAtLeast',
            'General_OperationLessThan',
            'General_OperationGreaterThan',
            'General_OperationContains',
            'General_OperationDoesNotContain',
            'General_OperationStartsWith',
            'General_OperationEndsWith',
            'General_OperationIs',
            'General_OperationIsNot',
            'General_OperationContains',
            'General_OperationDoesNotContain',
            'SegmentEditor_DefaultAllVisits',
            'General_DefaultAppended',
            'SegmentEditor_AddNewSegment',
            'General_Edit',
            'General_Search',
            'General_SearchNoResults',
        );
        $translations = array();
        foreach ($translationKeys as $key) {
            $translations[$key] = Piwik::translate($key);
        }
        return $translations;
    }

    protected function isCreatingRealTimeSegmentsEnabled()
    {
        // when browser archiving is disabled for segments, we force new segments to be created as pre-processed
        if(!Rules::isBrowserArchivingAvailableForSegments()) {
            return false;
        }

        return (bool) Config::getInstance()->General['enable_create_realtime_segments'];
    }

}
