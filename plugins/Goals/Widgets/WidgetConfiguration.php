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
use Piwik\Plugin\WidgetConfig;
use Piwik\Plugins\Goals\API;
use Piwik\WidgetsList;

class WidgetConfiguration extends \Piwik\Plugin\Widget
{
    public static function configure(WidgetConfig $config)
    {
        $config->disable();
    }

    public static function configureWidgetsList(WidgetsList $widgetsList)
    {
        $idSite = Common::getRequestVar('idSite', null, 'int');
        $goals  = API::getInstance()->getGoals($idSite);

        if (count($goals) > 0) {
            foreach ($goals as $goal) {
                $name   = Common::sanitizeInputValue($goal['name']);
                $params = array('idGoal' => $goal['idgoal']);

                $config = new WidgetConfig();
                $config->setCategory('Goals_Goals');
                $config->setName($name);
                $config->setModule('Goals');
                $config->setAction('widgetGoalReport');
                $config->setParameters($params);
                $widgetsList->addWidget($config);
            }
        }
    }
}
