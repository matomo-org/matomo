<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ExamplePlugin;

use Piwik\View;

/**
 *
 */
class Controller extends \Piwik\Plugin\Controller
{

    public function index()
    {
        $view = new View('@ExamplePlugin/index.twig');
        $this->setBasicVariablesView($view);
        $view->answerToLife = '42';

        return $view->render();
    }
}
