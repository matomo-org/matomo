<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Ecommerce;

use Piwik\Common;
use Piwik\Site;
use Piwik\Piwik;

class Widgets extends \Piwik\Plugin\Widgets
{
    protected $category = 'Goals_Ecommerce';

    protected function init()
    {
        $idSite = $this->getIdSite();

        $site = new Site($idSite);
        if ($site->isEcommerceEnabled()) {
            $this->addWidget('General_Overview', 'widgetGoalReport', array('idGoal' => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER));
            $this->addWidget('Goals_EcommerceLog', 'getEcommerceLog');
        }
    }

    private function getIdSite()
    {
        return Common::getRequestVar('idSite', null, 'int');
    }

}
