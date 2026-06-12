<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugin;

use Piwik\Common;
use Piwik\Menu\MenuAi;

/**
 * Base class of plugin controllers that render pages within the AI Insights section.
 *
 * Extend this class so the AI Insights menu and the active top menu route are assigned
 * to the view automatically.
 *
 * See {@link Controller} to learn more about Piwik controllers.
 */
abstract class ControllerAi extends Controller
{
    /**
     * Assigns the standard page variables plus AI Insights menu variables.
     *
     * @param \Piwik\View $view
     * @return void
     */
    protected function setGeneralVariablesView($view)
    {
        parent::setGeneralVariablesView($view);
        $this->setAiInsightsVariablesView($view);
    }

    /**
     * @param \Piwik\View $view
     * @return void
     */
    protected function setAiInsightsVariablesView($view)
    {
        $menu = MenuAi::getInstance();
        $defaultUrl = $menu->getDefaultUrl();

        $view->aiMenu = $menu->getMenu();
        $view->aiTopMenuUrl = $defaultUrl;
        $view->currentCategory = Request::getStringParameter('category', '');
        $view->currentSubcategory = Request::getStringParameter('subcategory', '');

        if (is_array($defaultUrl)) {
            $view->topMenuModule = $defaultUrl['module'] ?? null;
            $view->topMenuAction = $defaultUrl['action'] ?? null;
        }
    }
}
