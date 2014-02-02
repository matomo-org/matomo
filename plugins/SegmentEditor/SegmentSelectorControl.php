<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\SegmentEditor;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugins\API\API as APIMetadata;
use Piwik\View;
use Piwik\Access;

/**
 * Generates the HTML for the segment selector control (which includes the segment editor).
 */
class SegmentSelectorControl extends View
{
    const TEMPLATE = "@SegmentEditor/_segmentSelector";

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(self::TEMPLATE);

        $this->isSuperUser = Access::getInstance()->hasSuperUserAccess();
        $this->idSite = Common::getRequestVar('idSite', false, 'int');

        $segments = APIMetadata::getInstance()->getSegmentsMetadata($this->idSite);

        $segmentsByCategory = $customVariablesSegments = array();
        foreach ($segments as $segment) {
            if ($segment['category'] == Piwik::translate('General_Visit')
                && ($segment['type'] == 'metric' && $segment['segment'] != 'visitIp')
            ) {
                $metricsLabel = Piwik::translate('General_Metrics');
                $metricsLabel[0] = strtolower($metricsLabel[0]);
                $segment['category'] .= ' (' . $metricsLabel . ')';
            }
            $segmentsByCategory[$segment['category']][] = $segment;
        }
        uksort($segmentsByCategory, array($this, 'sortSegmentCategories'));

        $this->segmentsByCategory = $segmentsByCategory;

        $savedSegments = API::getInstance()->getAll($this->idSite);
        foreach ($savedSegments as &$savedSegment) {
            $savedSegment['name'] = Common::sanitizeInputValue($savedSegment['name']);
        }
        $this->savedSegmentsJson = Common::json_encode($savedSegments);
        $this->authorizedToCreateSegments = !Piwik::isUserIsAnonymous();

        $this->segmentTranslations = Common::json_encode($this->getTranslations());
    }

    public function sortSegmentCategories($a, $b)
    {
        // Custom Variables last
        if ($a == Piwik::translate('CustomVariables_CustomVariables')) {
            return 1;
        }
        return 0;
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
}
