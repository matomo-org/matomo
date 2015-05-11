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
use Piwik\Plugins\Goals\API;
use Piwik\WidgetsList;

class WidgetConfig extends \Piwik\Plugin\Widget
{
    protected $category = 'Goals_Goals';

    public function isEnabled()
    {
        return false;
    }

    public function configureWidgetsList(WidgetsList $widgetsList)
    {
        $idSite = Common::getRequestVar('idSite', null, 'int');
        $goals  = API::getInstance()->getGoals($idSite);

        if (count($goals) > 0) {
            foreach ($goals as $goal) {
                $name   = Common::sanitizeInputValue($goal['name']);
                $params = array('idGoal' => $goal['idgoal']);

                $widgetsList->add($this->getCategory(), $name, $this->getModule(), 'widgetGoalReport', $params);
            }
        }
    }
}
