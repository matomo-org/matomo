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
use Piwik\Menu\MenuReporting;
use Piwik\Piwik;
use Piwik\Site;
use Piwik\Url;

/**
 */
class Menu extends \Piwik\Plugin\Menu
{

    public function configureReportingMenu(MenuReporting $menu)
    {
        $idSite = Common::getRequestVar('idSite', null, 'int');

        $site = new Site($idSite);

        if ($site->isEcommerceEnabled()) {
            $ecommerceParams = array('idGoal' => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER);

            $menu->addItem('Goals_Ecommerce', '', array(), 24);
            $menu->addItem('Goals_Ecommerce', 'General_Overview', $this->urlForAction('ecommerceReport', $ecommerceParams), 1);
            $menu->addItem('Goals_Ecommerce', 'Goals_EcommerceLog', $this->urlForAction('ecommerceLogReport'), 2);
            $menu->addItem('Goals_Ecommerce', 'Goals_Products', $this->urlForAction('products', $ecommerceParams), 3);
            $menu->addItem('Goals_Ecommerce', 'Ecommerce_Sales', $this->urlForAction('sales', $ecommerceParams), 4);
        }

    }
}
