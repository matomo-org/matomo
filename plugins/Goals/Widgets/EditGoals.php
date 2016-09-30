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

class EditGoals extends \Piwik\Widget\Widget
{
    public static function configure(WidgetConfig $config)
    {
        $idSite = Common::getRequestVar('idSite', 0, 'int');

        $config->setCategoryId('Goals_Goals');
        $config->setSubcategoryId('Goals_ManageGoals');
        $config->setIsNotWidgetizable();

        if (empty($idSite)) {
            $config->disable();
            return;
        }

        $goals  = API::getInstance()->getGoals($idSite);

        if (Piwik::isUserHasAdminAccess($idSite)) {
            $config->setName('Goals_ManageGoals');
        } else {
            $config->setName('Goals_CreateNewGOal');
        }

        if (count($goals) === 0) {
            $config->disable();
        }
    }
}
