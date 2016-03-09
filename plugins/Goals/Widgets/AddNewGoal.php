<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Goals\Widgets;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugins\Goals\API;
use Piwik\Widget\WidgetConfig;

class AddNewGoal extends \Piwik\Widget\Widget
{
    public static function configure(WidgetConfig $config)
    {
        $idSite = Common::getRequestVar('idSite', null, 'int');
        $goals  = API::getInstance()->getGoals($idSite);

        $config->setCategoryId('Goals_Goals');
        $config->setSubcategoryId('Goals_AddNewGoal');
        $config->setParameters(array('idGoal' => ''));
        $config->setIsNotWidgetizable();

        if (Piwik::isUserHasAdminAccess($idSite)) {
            $config->setName('Goals_AddNewGoal');
        } else {
            $config->setName('Goals_CreateNewGOal');
        }

        if (count($goals) !== 0) {
            $config->disable();
        }
    }
}
