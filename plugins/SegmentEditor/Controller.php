<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_SegmentEditor
 */

/**
 * @package Piwik_SegmentEditor
 */
class Piwik_SegmentEditor_Controller extends Piwik_Controller
{

    public function getSelector()
    {
        $view = Piwik_View::factory('@SegmentEditor/selector');
        $idSite = Piwik_Common::getRequestVar('idSite');
        $this->setGeneralVariablesView($view);
        $segments = Piwik_API_API::getInstance()->getSegmentsMetadata($idSite);

        $segmentsByCategory = $customVariablesSegments = array();
        foreach($segments as $segment) {
            if($segment['category'] == 'Visit'
                && $segment['type'] == 'metric') {
                $segment['category'] .= ' (' . lcfirst(Piwik_Translate('General_Metrics')) . ')';
            }
            $segmentsByCategory[$segment['category']][] = $segment;
        }
        uksort($segmentsByCategory, array($this, 'sortCustomVariablesLast'));

        $view->segmentsByCategory = $segmentsByCategory;


        $savedSegments = Piwik_SegmentEditor_API::getInstance()->getAll($idSite);
        $view->savedSegmentsJson = Piwik_Common::json_encode($savedSegments);

        $out = $view->render();
        echo $out;
    }

    public function sortCustomVariablesLast($a, $b)
    {
        if($a == Piwik_Translate('CustomVariables_CustomVariables')) {
            return 1;
        }
        return -1;
    }
}
