<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugin;

use Piwik\Common;
use Piwik\Date;
use Piwik\Development;
use Piwik\Menu\MenuAdmin;
use Piwik\Menu\MenuTop;
use Piwik\Period;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Plugins\UsersManager\UserPreferences;
use Piwik\Site;

/**
 * Base class of all plugin menu providers. Plugins that define their own menu items can extend this class to easily
 * add new items, to remove or to rename existing items.
 *
 * Descendants of this class can overwrite any of these methods. Each method will be executed only once per request
 * and cached for any further menu requests.
 *
 * For an example, see the {@link https://github.com/piwik/piwik/blob/master/plugins/ExampleUI/Menu.php} plugin.
 *
 * @api
 * @since 2.4.0
 */
class Menu
{
    public function __construct()
    {
        // Constructor kept for BC (because called in implementations)
    }

    private function getModule()
    {
        $className = get_class($this);
        $className = explode('\\', $className);

        return $className[2];
    }

    /**
     * Generates a URL for the default action of the plugin controller.
     *
     * Example:
     * ```
     * $menu->addItem('MyPlugin_MyPlugin', '', $this->urlForDefaultAction(), $orderId = 30);
     * // will add a menu item that leads to the default action of the plugin controller when a user clicks on it.
     * // The default action is usually the `index` action - meaning the `index()` method the controller -
     * // but the default action can be customized within a controller
     * ```
     *
     * @param  array $additionalParams  Optional URL parameters that will be appended to the URL
     * @return array
     *
     * @since 2.7.0
     * @api
     */
    protected function urlForDefaultAction($additionalParams = array())
    {
        $params = (array) $additionalParams;
        $params['action'] = '';
        $params['module'] = $this->getModule();

        return $params;
    }

    /**
     * Generates a URL for the given action. In your plugin controller you have to create a method with the same name
     * as this method will be executed when a user clicks on the menu item. If you want to generate a URL for the
     * action of another module, meaning not your plugin, you should use the method {@link urlForModuleAction()}.
     *
     * @param  string $controllerAction  The name of the action that should be executed within your controller
     * @param  array  $additionalParams  Optional URL parameters that will be appended to the URL
     * @return array
     *
     * @since 2.7.0
     * @api
     */
    protected function urlForAction($controllerAction, $additionalParams = array())
    {
        $module = $this->getModule();
        $this->checkisValidCallable($module, $controllerAction);

        $params = (array) $additionalParams;
        $params['action'] = $controllerAction;
        $params['module'] = $module;

        return $params;
    }

    /**
     * Generates a URL for the given action of the given module. We usually do not recommend to use this method as you
     * should make sure the method of that module actually exists. If the plugin owner of that module changes the method
     * in a future version your link might no longer work. If you want to link to an action of your controller use the
     * method {@link urlForAction()}. Note: We will generate a link only if the given module is installed and activated.
     *
     * @param  string $module            The name of the module/plugin the action belongs to. The module name is case sensitive.
     * @param  string $controllerAction  The name of the action that should be executed within your controller
     * @param  array  $additionalParams  Optional URL parameters that will be appended to the URL
     * @return array|null   Returns null if the given module is either not installed or not activated. Returns the array
     *                      of query parameter names and values to the given module action otherwise.
     *
     * @since 2.7.0
     * // not API for now
     */
    protected function urlForModuleAction($module, $controllerAction, $additionalParams = array())
    {
        $this->checkisValidCallable($module, $controllerAction);

        $pluginManager = PluginManager::getInstance();

        if (!$pluginManager->isPluginLoaded($module) ||
            !$pluginManager->isPluginActivated($module)) {
            return null;
        }

        $params = (array) $additionalParams;
        $params['action'] = $controllerAction;
        $params['module'] = $module;

        return $params;
    }

    /**
     * Generates a URL to the given action of the current module, and it will also append some URL query parameters from the
     * User preferences: idSite, period, date. If you do not need the parameters idSite, period and date to be generated
     * use {@link urlForAction()} instead.
     *
     * @param  string $controllerAction  The name of the action that should be executed within your controller
     * @param  array  $additionalParams  Optional URL parameters that will be appended to the URL
     * @return array   Returns the array of query parameter names and values to the given module action and idSite date and period.
     *
     */
    protected function urlForActionWithDefaultUserParams($controllerAction, $additionalParams = array())
    {
        $module = $this->getModule();

        return $this->urlForModuleActionWithDefaultUserParams($module, $controllerAction, $additionalParams);
    }

    /**
     * Generates a URL to the given action of the given module, and it will also append some URL query parameters from the
     * User preferences: idSite, period, date. If you do not need the parameters idSite, period and date to be generated
     * use {@link urlForModuleAction()} instead.
     *
     * @param  string $module            The name of the module/plugin the action belongs to. The module name is case sensitive.
     * @param  string $controllerAction  The name of the action that should be executed within your controller
     * @param  array  $additionalParams  Optional URL parameters that will be appended to the URL
     * @return array|null   Returns the array of query parameter names and values to the given module action and idSite date and period.
     *                      Returns null if the module or action is invalid.
     *
     */
    protected function urlForModuleActionWithDefaultUserParams($module, $controllerAction, $additionalParams = array())
    {
        $urlModuleAction = $this->urlForModuleAction($module, $controllerAction);

        $date = Common::getRequestVar('date', false);
        if ($date) {
            $urlModuleAction['date'] = $date;
        }
        $period = Common::getRequestVar('period', false);
        if ($period) {
            $urlModuleAction['period'] = $period;
        }

        // We want the current query parameters to override the user's defaults
        return array_merge(
            $this->urlForDefaultUserParams(),
            $urlModuleAction,
            $additionalParams
        );
    }

    /**
     * Returns the &idSite=X&period=Y&date=Z query string fragment,
     * fetched from current logged-in user's preferences.
     *
     * @param bool $websiteId
     * @param bool $defaultPeriod
     * @param bool $defaultDate
     * @return array eg ['idSite' => 1, 'period' => 'day', 'date' => '2012-02-03']
     * @throws \Exception in case a website was not specified and a default website id could not be found
     */
    public function urlForDefaultUserParams($websiteId = false, $defaultPeriod = false, $defaultDate = false)
    {
        $userPreferences = new UserPreferences();
        if (empty($websiteId)) {
            $websiteId = $userPreferences->getDefaultWebsiteId();
        }
        if (empty($websiteId)) {
            throw new \Exception("A website ID was not specified and a website to default to could not be found.");
        }
        if (empty($defaultPeriod)) {
            $defaultPeriod = $userPreferences->getDefaultPeriod(false);
        }
        if (empty($defaultDate)) {
            $defaultDate = $userPreferences->getDefaultDate();
        }

        if ($defaultPeriod !== 'range' && !empty($defaultDate) && $defaultDate !== 'today') {
            // not easy to make it work for range... is rarely the default anyway especially when just setting up
            // Matomo as this logic is basically only applied on the first day a site is created
            // no need to run logic when today is selected. It basically runs currently only when "yesterday" is selected
            // as a default date but would also support future new default dates like past month etc.
            try {
                $siteCreationDate = Site::getCreationDateFor($websiteId);
                $siteTimezone = Site::getTimezoneFor($websiteId);

                if (!empty($siteCreationDate)) {
                    if (is_numeric($defaultDate)) {
                        $defaultDate = (int) $defaultDate; //prevent possible exception should defaultDate be a string timestamp
                    }
                    $siteCreationDate = Date::factory($siteCreationDate, $siteTimezone);
                    $defaultDateObj = Date::factory($defaultDate, $siteTimezone);

                    $period = Period\Factory::build($defaultPeriod, $defaultDateObj);
                    $endDate = $period->getDateEnd();

                    if ($endDate->isEarlier($siteCreationDate)) {
                        // when selected date is before site creation date or it is the site creation day
                        $defaultDate = $siteCreationDate->toString();
                    }
                }
            } catch (\Exception $e) {
                //ignore any error in case site was just deleted or the given date is not valid etc.
            }
        }
        return array(
            'idSite' => $websiteId,
            'period' => $defaultPeriod,
            'date'   => $defaultDate,
        );
    }

    /**
     * Configures the top menu which is supposed to contain analytics related items such as the
     * "All Websites Dashboard".
     */
    public function configureTopMenu(MenuTop $menu)
    {
    }

    /**
     * Configures the admin menu which is supposed to contain only administration related items such as
     * "Websites", "Users" or "Settings".
     */
    public function configureAdminMenu(MenuAdmin $menu)
    {
    }

    private function checkisValidCallable($module, $action)
    {
        if (!Development::isEnabled()) {
            return;
        }

        $prefix = 'Menu item added in ' . get_class($this) . ' will fail when being selected. ';

        if (!is_string($action)) {
            Development::error($prefix . 'No valid action is specified. Make sure the defined action that should be executed is a string.');
        }

        $reportAction = lcfirst(substr($action, 4));
        if (ReportsProvider::factory($module, $reportAction)) {
            return;
        }

        $controllerClass = '\\Piwik\\Plugins\\' . $module . '\\Controller';

        if (!Development::methodExists($controllerClass, $action)) {
            Development::error($prefix . 'The defined action "' . $action . '" does not exist in ' . $controllerClass . '". Make sure to define such a method.');
        }

        if (!Development::isCallableMethod($controllerClass, $action)) {
            Development::error($prefix . 'The defined action "' . $action . '" is not callable on "' . $controllerClass . '". Make sure the method is public.');
        }
    }
}
