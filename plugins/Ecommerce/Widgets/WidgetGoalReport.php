<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Ecommerce\Widgets;

use Piwik\Common;
use Piwik\Plugin\WidgetConfig;
use Piwik\Site;
use Piwik\Piwik;

class WidgetGoalReport extends \Piwik\Plugin\Widget
{
    public static function configure(WidgetConfig $config)
    {
        $config->setCategory('Goals_Ecommerce');
        $config->setName('General_Overview');
        $config->setParameters(array('idGoal' => Piwik::LABEL_ID_GOAL_IS_ECOMMERCE_ORDER));

        $idSite = Common::getRequestVar('idSite', null, 'int');

        $site = new Site($idSite);
        $config->setIsEnabled($site->isEcommerceEnabled());
    }

}
