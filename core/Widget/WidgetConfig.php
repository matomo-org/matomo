<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Widget;

use Piwik\Access;
use Piwik\Piwik;
use Exception;

/**
 * Configures a widget. Use this class to configure a {@link Piwik\Widget\Widget`} or to
 * add a widget to the WidgetsList via {@link WidgetsList::addWidget}.
 *
 * @api since Piwik 3.0.0
 */
class WidgetConfig
{
    protected $categoryId = '';
    protected $subcategoryId = '';
    protected $module = '';
    protected $action = '';
    protected $parameters = array();
    protected $middlewareParameters = array();
    protected $name   = '';
    protected $order  = 99;
    protected $isEnabled = true;
    protected $isWidgetizable = true;
    protected $isWide = false;

    /**
     * Set the id of the category the widget belongs to.
     * @param  string $categoryId  Usually a translation key, eg 'General_Visits', 'Goals_Goals', ...
     * @return static
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    /**
     * Get the id of the category the widget belongs to.
     * @return string
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * Set the id of the subcategory the widget belongs to. If a subcategory is specified, the widget
     * will be shown in the Piwik reporting UI. The subcategoryId will be used as a translation key for
     * the submenu item.
     *
     * @param  string $subcategoryId  Usually a translation key, eg 'General_Overview', 'Actions_Pages', ...
     * @return static
     */
    public function setSubcategoryId($subcategoryId)
    {
        $this->subcategoryId = $subcategoryId;

        return $this;
    }

    /**
     * Get the currently set category ID.
     * @return string
     */
    public function getSubcategoryId()
    {
        return $this->subcategoryId;
    }

    /**
     * Set the module (aka plugin name) of the widget. The correct module is usually detected automatically and
     * not needed to be configured manually.
     *
     * @param string $module eg 'CoreHome'
     * @return static
     */
    public function setModule($module)
    {
        $this->module = $module;

        return $this;
    }

    public function getModule()
    {
        return $this->module;
    }

    /**
     * Set the action of the widget that shall be used in the URL to render the widget.
     * The correct action is usually detected automatically and not needed to be configured manually.
     *
     * @param string $action eg 'renderMyWidget'
     * @return static
     */
    public function setAction($action)
    {
        $this->action = $action;

        return $this;
    }

    /**
     * Get the currently set action.
     * @return string
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * Sets (overwrites) the parameters of the widget. These parameters will be added to the URL when rendering the
     * widget. You can access these parameters via `Piwik\Common::getRequestVar(...)`.
     *
     * @param array $parameters eg. ('urlparam' => 'urlvalue')
     * @return static
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;

        return $this;
    }

    /**
     * Add new parameters and only overwrite parameters that have the same name. See {@link setParameters()}
     *
     * @param  array $parameters eg. ('urlparam' => 'urlvalue')
     * @return static
     */
    public function addParameters($parameters)
    {
        $this->parameters = array_merge($this->parameters, $parameters);

        return $this;
    }

    /**
     * Get all URL parameters needed to render this widget.
     * @return array  Eg ('urlparam' => 'urlvalue').
     */
    public function getParameters()
    {
        $defaultParams = array(
            'module' => $this->getModule(),
            'action' => $this->getAction()
        );

        return $defaultParams + $this->parameters;
    }

    /**
     * Set the name of the widget.
     *
     * @param string $name Usually a translation key, eg 'VisitTime_ByServerTimeWidgetName'
     * @return static
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the name of the widget.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the order of the widget.
     *
     * @param int $order eg. 5
     * @return static
     */
    public function setOrder($order)
    {
        $this->order = (int) $order;

        return $this;
    }

    /**
     * Returns the order of the widget.
     * @return int
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Defines whether a widget is enabled or not. For instance some widgets might not be available to every user or
     * might depend on a setting (such as Ecommerce) of a site. In such a case you can perform any checks and then
     * return `true` or `false`. If your report is only available to users having super user access you can do the
     * following: `return Piwik::hasUserSuperUserAccess();`
     * @return bool
     */
    public function isEnabled()
    {
        return $this->isEnabled;
    }

    /**
     * Enable / disable the widget. See {@link isEnabled()}
     *
     * @param bool $isEnabled
     * @return static
     */
    public function setIsEnabled($isEnabled)
    {
        $this->isEnabled = (bool) $isEnabled;
        return $this;
    }

    /**
     * Enables the widget. See {@link isEnabled()}
     */
    public function enable()
    {
        $this->setIsEnabled(true);
    }

    /**
     * Disables the widget. See {@link isEnabled()}
     */
    public function disable()
    {
        $this->setIsEnabled(false);
    }

    /**
     * This method checks whether the widget is available, see {@link isEnabled()}. If not, it triggers an exception
     * containing a message that will be displayed to the user. You can overwrite this message in case you want to
     * customize the error message. Eg.
     * ```
     * if (!$this->isEnabled()) {
     *     throw new Exception('Setting XYZ is not enabled or the user has not enough permission');
     * }
     * ```
     * @throws \Exception
     */
    public function checkIsEnabled()
    {
        if (!$this->isEnabled()) {
            // Some widgets are disabled when the user is not superuser. If the user is not logged in, we should
            // prompt them to do this first rather than showing them the "widget not enabled" error
            Access::getInstance()->checkUserIsNotAnonymous();

            throw new Exception(Piwik::translate('General_ExceptionWidgetNotEnabled'));
        }
    }

    /**
     * Returns the unique id of an widget based on module, action and the set parameters.
     *
     * @return string
     */
    public function getUniqueId()
    {
        $parameters = $this->getParameters();
        unset($parameters['module']);
        unset($parameters['action']);

        return WidgetsList::getWidgetUniqueId($this->getModule(), $this->getAction(), $parameters);
    }

    /**
     * Sets the widget as not widgetizable {@link isWidgetizeable()}.
     *
     * @return static
     */
    public function setIsNotWidgetizable()
    {
        $this->isWidgetizable = false;
        return $this;
    }

    /**
     * Sets the widget as widgetizable {@link isWidgetizeable()}.
     *
     * @return static
     */
    public function setIsWidgetizable()
    {
        $this->isWidgetizable = true;
        return $this;
    }

    /**
     * Detect whether the widget is widgetizable meaning it won't be able to add it to the dashboard and it won't
     * be possible to export the widget via an iframe if it is not widgetizable. This is usually not needed but useful
     * when you eg want to display a widget within the Piwik UI but not want to have it widgetizable.
     *
     * @return bool
     */
    public function isWidgetizeable()
    {
        return $this->isWidgetizable;
    }

    /**
     * If middleware parameters are specified, the corresponding action will be executed before showing the
     * actual widget in the UI. Only if this action (can be a controller method or API method) returns JSON `true`
     * the widget will be actually shown. It is similar to `isEnabled()` but the specified action is performed each
     * time the widget is requested in the UI whereas `isEnabled` is only checked once on the initial page load when
     * we load the initial list of widgets. So if your widget's visibility depends on archived data
     * (aka idSite/period/date) you should specify middle parameters. This has mainly two reasons:
     *
     * - This way the initial page load time is faster as we won't have to request archived data on the initial page
     * load for widgets that are potentially never shown.
     * - We execute that action every time before showing it. As the initial list of widgets is loaded on page load
     * it is possible that some archives have no data yet, but at a later time there might be actually archived data.
     * As we never reload the initial list of widgets we would still not show the widget even there we should. Example:
     * On page load there are no conversions, a few minutes later there might be conversions. As the middleware is
     * executed before showing it, we detect correctly that there are now conversions whereas `isEnabled` is only
     * checked once on the initial Piwik page load.
     *
     * @param array $parameters URL parameters eg array('module' => 'Goals', 'action' => 'Conversions')
     * @return static
     */
    public function setMiddlewareParameters($parameters)
    {
        $this->middlewareParameters = $parameters;
        return $this;
    }

    /**
     * Get defined middleware parameters (if any).
     *
     * @return array
     */
    public function getMiddlewareParameters()
    {
        return $this->middlewareParameters;
    }

    /**
     * Marks this widget as a "wide" widget that requires the full width.
     *
     * @return $this
     */
    public function setIsWide()
    {
        $this->isWide = true;
        return $this;
    }

    /**
     * Detect whether the widget should be shown wide or not.
     * @return bool
     */
    public function isWide()
    {
        return $this->isWide;
    }
}
