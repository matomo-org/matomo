<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitorInterest;

use Piwik\ArchiveProcessor;
use Piwik\FrontController;
use Piwik\Metrics;
use Piwik\Piwik;
use Piwik\Plugin\ViewDataTable;
use Piwik\Plugins\CoreVisualizations\Visualizations\Cloud;
use Piwik\Plugins\CoreVisualizations\Visualizations\Graph;

/**
 *
 */
class VisitorInterest extends \Piwik\Plugin
{
    /**
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'API.getReportMetadata' => 'getReportMetadata'
        );
    }

    public function getReportMetadata(&$reports)
    {
        $reports[] = array(
            'category'          => Piwik::translate('General_Visitors'),
            'name'              => Piwik::translate('VisitorInterest_WidgetLengths'),
            'module'            => 'VisitorInterest',
            'action'            => 'getNumberOfVisitsPerVisitDuration',
            'dimension'         => Piwik::translate('VisitorInterest_ColumnVisitDuration'),
            'metrics'           => array('nb_visits'),
            'processedMetrics'  => false,
            'constantRowsCount' => true,
            'documentation'     => Piwik::translate('VisitorInterest_WidgetLengthsDocumentation')
                . '<br />' . Piwik::translate('General_ChangeTagCloudView'),
            'order'             => 15
        );

        $reports[] = array(
            'category'          => Piwik::translate('General_Visitors'),
            'name'              => Piwik::translate('VisitorInterest_WidgetPages'),
            'module'            => 'VisitorInterest',
            'action'            => 'getNumberOfVisitsPerPage',
            'dimension'         => Piwik::translate('VisitorInterest_ColumnPagesPerVisit'),
            'metrics'           => array('nb_visits'),
            'processedMetrics'  => false,
            'constantRowsCount' => true,
            'documentation'     => Piwik::translate('VisitorInterest_WidgetPagesDocumentation')
                . '<br />' . Piwik::translate('General_ChangeTagCloudView'),
            'order'             => 20
        );

        $reports[] = array(
            'category'          => Piwik::translate('General_Visitors'),
            'name'              => Piwik::translate('VisitorInterest_visitsByVisitCount'),
            'module'            => 'VisitorInterest',
            'action'            => 'getNumberOfVisitsByVisitCount',
            'dimension'         => Piwik::translate('VisitorInterest_visitsByVisitCount'),
            'metrics'           => array(
                'nb_visits',
                'nb_visits_percentage' => Piwik::translate('General_ColumnPercentageVisits'),
            ),
            'processedMetrics'  => false,
            'constantRowsCount' => true,
            'documentation'     => Piwik::translate('VisitorInterest_WidgetVisitsByNumDocumentation')
                . '<br />' . Piwik::translate('General_ChangeTagCloudView'),
            'order'             => 25
        );

        $reports[] = array(
            'category'          => Piwik::translate('General_Visitors'),
            'name'              => Piwik::translate('VisitorInterest_VisitsByDaysSinceLast'),
            'module'            => 'VisitorInterest',
            'action'            => 'getNumberOfVisitsByDaysSinceLast',
            'dimension'         => Piwik::translate('VisitorInterest_VisitsByDaysSinceLast'),
            'metrics'           => array('nb_visits'),
            'processedMetrics'  => false,
            'constantRowsCount' => true,
            'documentation'     => Piwik::translate('VisitorInterest_WidgetVisitsByDaysSinceLastDocumentation'),
            'order'             => 30
        );
    }

    function postLoad()
    {
        Piwik::addAction('Template.headerVisitsFrequency', array('Piwik\Plugins\VisitorInterest\VisitorInterest', 'headerVisitsFrequency'));
        Piwik::addAction('Template.footerVisitsFrequency', array('Piwik\Plugins\VisitorInterest\VisitorInterest', 'footerVisitsFrequency'));
    }

    static public function headerVisitsFrequency(&$out)
    {
        $out = '<div id="leftcolumn">';
    }

    static public function footerVisitsFrequency(&$out)
    {
        $out = '</div>
			<div id="rightcolumn">
			';
        $out .= FrontController::getInstance()->fetchDispatch('VisitorInterest', 'index');
        $out .= '</div>';
    }
}
