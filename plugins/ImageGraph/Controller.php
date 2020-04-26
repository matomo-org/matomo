<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ImageGraph;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugins\API\API as APIPlugins;
use Piwik\SettingsPiwik;
use Piwik\View;

class Controller extends \Piwik\Plugin\Controller
{
    /**
     * @internal For Debugging only
     * Call metadata reports and draw the default graph for each report.
     */
    public function index()
    {
        Piwik::checkUserHasSomeAdminAccess();
        $idSite = $this->idSite ?: 1;
        $period = Common::getRequestVar('period', 'day', 'string');
        $date = Common::getRequestVar('date', 'today', 'string');
        $_GET['token_auth'] = Piwik::getCurrentUserTokenAuth();
        $reports = APIPlugins::getInstance()->getReportMetadata($idSite, $period, $date);
        $plot = array();
        foreach ($reports as $report) {
            if (!empty($report['imageGraphUrl'])) {
                $plot[] = array(
                    // Title
                    $report['category'] . ' › ' . $report['name'],
                    //URL
                    SettingsPiwik::getPiwikUrl() . $report['imageGraphUrl']
                );
            }
        }
        $view = new View('@ImageGraph/index');
        $view->titleAndUrls = $plot;
        return $view->render();
    }

    // Draw graphs for all sizes (DEBUG)
    public function testAllSizes()
    {
        Piwik::checkUserHasSuperUserAccess();

        $view = new View('@ImageGraph/testAllSizes');
        $this->setGeneralVariablesView($view);

        $period = Common::getRequestVar('period', 'day', 'string');
        $date = Common::getRequestVar('date', 'today', 'string');

        $_GET['token_auth'] = Piwik::getCurrentUserTokenAuth();
        $availableReports = APIPlugins::getInstance()->getReportMetadata($this->idSite, $period, $date);
        $view->availableReports = $availableReports;
        $view->graphTypes = array(
            '', // default graph type
//			'evolution',
//			'verticalBar',
//			'horizontalBar',
//			'pie',
//			'3dPie',
        );
        $view->graphSizes = array(
            array(null, null), // default graph size
            array(460, 150), // standard phone
            array(300, 150), // standard phone 2
            array(240, 150), // smallest mobile display
            array(800, 150), // landscape mode
            array(600, 300, $fontSize = 18, 300, 150), // iphone requires bigger font, then it will be scaled down by ios
        );
        return $view->render();
    }
}
