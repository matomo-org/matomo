<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Feedback;

use Piwik\View;
use Piwik\Version;
use Piwik\Container\StaticContainer;

class Controller extends \Piwik\Plugin\Controller
{
    function index()
    {
        $view = new View('@Feedback/index');
        $this->setGeneralVariablesView($view);
        $popularHelpTopics = StaticContainer::get('popularHelpTopics');
        $view->popularHelpTopics = $popularHelpTopics;
        $view->piwikVersion = Version::VERSION;
        return $view->render();
    }

}
