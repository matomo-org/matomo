<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Insights;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\View;

/**
 * Insights Controller
 */
class Controller extends \Piwik\Plugin\Controller
{

    public function getInsightOverview()
    {
        $idSite = Common::getRequestVar('idSite', null, 'int');
        $period = Common::getRequestVar('period', null, 'string');
        $date   = Common::getRequestVar('date', null, 'string');

        Piwik::checkUserHasViewAccess(array($idSite));

        $view = new View('@Insights/index.twig');
        $this->setBasicVariablesView($view);

        $view->moversAndShakers = API::getInstance()->getInsightsOverview($idSite, $period, $date);
        $view->showNoDataMessage = false;
        $view->showInsightsControls = false;
        $view->properties = array(
            'show_increase' => true,
            'show_decrease' => true,
            'order_by' => 'absolute'
        );

        return $view->render();
    }
}
