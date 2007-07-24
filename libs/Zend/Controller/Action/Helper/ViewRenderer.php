<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Controller
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */ 

/** Zend_Controller_Action_Helper_Abstract */
require_once 'Zend/Controller/Action/Helper/Abstract.php';

/** Zend_View_Interface */
require_once 'Zend/View/Interface.php';

/** Zend_View */
require_once 'Zend/View.php';

/**
 * View script integration
 *
 * Zend_Controller_Action_Helper_ViewRenderer provides transparent view 
 * integration for action controllers. It allows you to create a view object 
 * once, and populate it throughout all actions. Several global options may be 
 * set:
 *
 * - noController: if set true, render() will not look for view scripts in 
 *   subdirectories named after the controller
 * - viewSuffix: what view script filename suffix to use
 *
 * The helper autoinitializes the action controller view preDispatch(). It 
 * determines the path to the class file, and then determines the view base 
 * directory from there. It also uses the module name as a class prefix for 
 * helpers and views such that if your module name is 'Search', it will set the 
 * helper class prefix to 'Search_View_Helper' and the filter class prefix to ;
 * 'Search_View_Filter'.
 *
 * Usage:
 * <code>
 * // In your bootstrap:
 * Zend_Controller_Action_HelperBroker::addHelper(new Wopnet_Controller_Action_Helper_Abstract());
 *
 * // In your action controller methods:
 * $viewHelper = $this->_helper->getHelper('view');
 *
 * // Don't use controller subdirectories
 * $viewHelper->setNoController(true);
 *
 * // Specify a different script to render:
 * $this->_helper->view('form');
 *
 * </code>
 * 
 * @uses       Zend_Controller_Action_Helper_Abstract
 * @package    Zend_Controller
 * @subpackage Zend_Controller_Action
 * @copyright  Copyright (c) 2005-2007 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Controller_Action_Helper_ViewRenderer extends Zend_Controller_Action_Helper_Abstract
{
    /**
     * @var Zend_View_Interface
     */
    public $view;

    /**
     * Word delimiters
     * @var array
     */
    protected $_delimiters;

    /**
     * Front controller instance
     * @var Zend_Controller_Front
     */
    protected $_frontController;

    /**
     * Whether or not to autorender using controller name as subdirectory; 
     * global setting (not reset at next invocation)
     * @var boolean
     */
    protected $_neverController = false;

    /**
     * Whether or not to autorender postDispatch; global setting (not reset at 
     * next invocation)
     * @var boolean
     */
    protected $_neverRender     = false;

    /**
     * Whether or not to use a controller name as a subdirectory when rendering
     * @var boolean
     */
    protected $_noController    = false;

    /**
     * Whether or not to autorender postDispatch; per controller/action setting (reset 
     * at next invocation)
     * @var boolean
     */
    protected $_noRender        = false;

    /**
     * Characters representing path delimiters in the controller
     * @var string|array
     */
    protected $_pathDelimiters;

    /**
     * Which named segment of the response to utilize
     * @var string
     */
    protected $_responseSegment = null;

    /**
     * Which action view script to render
     * @var string
     */
    protected $_scriptAction    = null;

    /**
     * View object basePath
     * @var string
     */
    protected $_viewBasePathSpec = ':moduleDir/views';

    /**
     * View script path specification string
     * @var string
     */
    protected $_viewScriptPathSpec = ':controller/:action.:suffix';

    /**
     * View script path specification string, minus controller segment
     * @var string
     */
    protected $_viewScriptPathNoControllerSpec = ':action.:suffix';

    /**
     * View script suffix
     * @var string
     */
    protected $_viewSuffix      = 'phtml';

    /**
     * Constructor
     *
     * Optionally set view object and options.
     * 
     * @param  Zend_View_Interface $view 
     * @param  array $options 
     * @return void
     */
    public function __construct(Zend_View_Interface $view = null, array $options = array())
    {
        if (null !== $view) {
            $this->setView($view);
        }

        if (!empty($options)) {
            $this->_setOptions($options);
        }
    }

    /**
     * Set the view object
     * 
     * @param Zend_View_Interface $view 
     * @return Zend_Controller_Action_Helper_ViewRenderer
     */
    public function setView(Zend_View_Interface $view)
    {
        $this->view = $view;
        return $this;
    }

    /**
     * Retrieve front controller instance
     * 
     * @return Zend_Controller_Front
     */
    public function getFrontController()
    {
        if (null === $this->_frontController) {
            $this->_frontController = Zend_Controller_Front::getInstance();
        }

        return $this->_frontController;
    }

    /**
     * Generate a class prefix for helper and filter classes
     * 
     * @return string
     */
    protected function _generateDefaultPrefix()
    {
        if ((null === $this->_actionController) || !strstr(get_class($this->_actionController), '_')) {
            $prefix = 'Zend_View';
        } else {
            $class = get_class($this->_actionController);
            $prefix = substr($class, 0, strpos($class, '_')) . '_View';
        }

        return $prefix;
    }

    /**
     * Retrieve base path based on location of current action controller
     * 
     * @return string
     */
    protected function _getBasePath()
    {
        if (null === $this->_actionController) {
            return '.' . DIRECTORY_SEPARATOR . 'views';
        }

        $path = $this->_translateSpec($this->getViewBasePathSpec());
        return $path;
    }

    /**
     * Set options
     * 
     * @param  array $options 
     * @return Zend_Controller_Action_Helper_ViewRenderer
     */
    protected function _setOptions(array $options)
    {
        foreach ($options as $key => $value)
        {
            switch ($key) {
                case 'neverRender':
                case 'neverController':
                case 'noController':
                case 'noRender':
                    $property = '_' . $key;
                    $this->{$property} = ($value) ? true : false;
                    break;
                case 'responseSegment':
                case 'scriptAction':
                case 'viewBasePathSpec':
                case 'viewScriptPathSpec':
                case 'viewScriptPathNoControllerSpec':
                case 'viewSuffix':
                    $property = '_' . $key;
                    $this->{$property} = (string) $value;
                    break;
                default:
                    break;
            }
        }

        return $this;
    }

    /**
     * Initialize the view object
     *
     * $options may contain the following keys:
     * - neverRender - flag dis/enabling postDispatch() autorender (affects all subsequent calls)
     * - noController - flag indicating whether or not to look for view scripts in subdirectories named after the controller
     * - noRender - flag indicating whether or not to autorender postDispatch()
     * - responseSegment - which named response segment to render a view script to
     * - scriptAction - what action script to render
     * - viewBasePathSpec - specification to use for determining view base path
     * - viewScriptPathSpec - specification to use for determining view script paths
     * - viewScriptPathNoControllerSpec - specification to use for determining view script paths when noController flag is set
     * - viewSuffix - what view script filename suffix to use
     * 
     * @param  string $path 
     * @param  string $prefix 
     * @param  array $options 
     * @return void
     */
    public function initView($path = null, $prefix = null, array $options = array())
    {
        if (null === $this->view) {
            $this->setView(new Zend_View());
        }

        // Reset some flags every time
        $options['noController'] = (isset($options['noController'])) ? $options['noController'] : false;
        $options['noRender']     = (isset($options['noRender'])) ? $options['noRender'] : false;
        $this->_scriptAction     = null;
        $this->_responseSegment  = null;

        // Set options first; may be used to determine other initializations
        $this->_setOptions($options);

        // Get base view path
        if (empty($path)) {
            $path = $this->_getBasePath();
            if (empty($path)) {
                throw new Zend_Controller_Action_Exception('ViewRenderer initialization failed: retrieved view base path is empty');
            }
        }

        if (null === $prefix) {
            $prefix = $this->_generateDefaultPrefix();
        }

        // Determine if this path has already been registered
        $currentPaths = $this->view->getScriptPaths();
        $path         = str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
        $pathExists   = false;
        foreach ($currentPaths as $tmpPath) {
            if (strstr($tmpPath, $path)) {
                $pathExists = true;
                break;
            }
        }
        if (!$pathExists) {
            $this->view->addBasePath($path, $prefix);
        }

        // Register view with action controller (unless already registered)
        if ((null !== $this->_actionController) && (null === $this->_actionController->view)) {
            $this->_actionController->view       = $this->view;
            $this->_actionController->viewSuffix = $this->_viewSuffix;
        }
    }

    /**
     * init - initialize view
     * 
     * @return void
     */
    public function init()
    {
        if ($this->getFrontController()->getParam('noViewRenderer')) {
            return;
        }

        $this->initView();
    }

    /**
     * Set view basePath specification
     *
     * Specification can contain one or more of the following:
     * - :moduleDir - current module directory
     * - :controller - name of current controller in the request
     * - :action - name of current action in the request
     * - :module - name of current module in the request
     * 
     * @param  string $path 
     * @return Zend_Controller_Action_Helper_ViewRenderer
     */
    public function setViewBasePathSpec($path)
    {
        $this->_viewBasePathSpec = (string) $path;
        return $this;
    }

    /**
     * Retrieve the current view basePath specification string
     * 
     * @return string
     */
    public function getViewBasePathSpec()
    {
        return $this->_viewBasePathSpec;
    }

    /**
     * Set view script path specification
     *
     * Specification can contain one or more of the following:
     * - :moduleDir - current module directory
     * - :controller - name of current controller in the request
     * - :action - name of current action in the request
     * - :module - name of current module in the request
     * 
     * @param  string $path 
     * @return Zend_Controller_Action_Helper_ViewRenderer
     */
    public function setViewScriptPathSpec($path)
    {
        $this->_viewScriptPathSpec = (string) $path;
        return $this;
    }

    /**
     * Retrieve the current view script path specification string
     * 
     * @return string
     */
    public function getViewScriptPathSpec()
    {
        return $this->_viewScriptPathSpec;
    }

    /**
     * Set view script path specification (no controller variant)
     *
     * Specification can contain one or more of the following:
     * - :moduleDir - current module directory
     * - :controller - name of current controller in the request
     * - :action - name of current action in the request
     * - :module - name of current module in the request
     *
     * :controller will likely be ignored in this variant.
     * 
     * @param  string $path 
     * @return Zend_Controller_Action_Helper_ViewRenderer
     */
    public function setViewScriptPathNoControllerSpec($path)
    {
        $this->_viewScriptPathNoControllerSpec = (string) $path;
        return $this;
    }

    /**
     * Retrieve the current view script path specification string (no controller variant)
     * 
     * @return string
     */
    public function getViewScriptPathNoControllerSpec()
    {
        return $this->_viewScriptPathNoControllerSpec;
    }

    /**
     * Get a view script based on an action and/or other variables
     *
     * Uses values found in current request if no values passed in $vars.
     *
     * If {@link $_noController} is set, uses {@link $_viewScriptPathNoControllerSpec};
     * otherwise, uses {@link $_viewScriptPathSpec}.
     * 
     * @param  string $action 
     * @param  array $vars 
     * @return string
     */
    public function getViewScript($action = null, array $vars = array())
    {
        $request = $this->getRequest();
        if ((null === $action) && (!isset($vars['action']))) {
            $action = $this->getScriptAction();
            if (null === $action) {
                $action = $request->getActionName();
            }
            $vars['action'] = $action;
        } elseif (null !== $action) {
            $vars['action'] = $action;
        }

        $path = ($this->getNoController() || $this->getNeverController())
              ? $this->_translateSpec($this->getViewScriptPathNoControllerSpec(), $vars)
              : $this->_translateSpec($this->getViewScriptPathSpec(), $vars);

        return $path;
    }

    /**
     * Set the neverRender flag (i.e., globally dis/enable autorendering)
     * 
     * @param  boolean $flag 
     * @return Zend_Controller_Action_Helper_ViewRenderer
     */
    public function setNeverRender($flag = true)
    {
        $this->_neverRender = ($flag) ? true : false;
        return $this;
    }

    /**
     * Retrieve neverRender flag value
     * 
     * @return boolean
     */
    public function getNeverRender()
    {
        return $this->_neverRender;
    }

    /**
     * Set the noRender flag (i.e., whether or not to autorender)
     * 
     * @param  boolean $flag 
     * @return Zend_Controller_Action_Helper_ViewRenderer
     */
    public function setNoRender($flag = true)
    {
        $this->_noRender = ($flag) ? true : false;
        return $this;
    }

    /**
     * Retrieve noRender flag value
     * 
     * @return boolean
     */
    public function getNoRender()
    {
        return $this->_noRender;
    }

    /**
     * Set the view script to use
     * 
     * @param  string $name 
     * @return Zend_Controller_Action_Helper_ViewRenderer
     */
    public function setScriptAction($name)
    {
        $this->_scriptAction = (string) $name;
        return $this;
    }

    /**
     * Retrieve view script name
     * 
     * @return string
     */
    public function getScriptAction()
    {
        return $this->_scriptAction;
    }

    /**
     * Set the response segment name
     * 
     * @param  string $name 
     * @return Zend_Controller_Action_Helper_ViewRenderer
     */
    public function setResponseSegment($name)
    {
        if (null === $name) {
            $this->_responseSegment = null;
        } else {
            $this->_responseSegment = (string) $name;
        }

        return $this;
    }

    /**
     * Retrieve named response segment name
     * 
     * @return string
     */
    public function getResponseSegment()
    {
        return $this->_responseSegment;
    }

    /**
     * Set the noController flag (i.e., whether or not to render into controller subdirectories)
     * 
     * @param  boolean $flag 
     * @return Zend_Controller_Action_Helper_ViewRenderer
     */
    public function setNoController($flag = true)
    {
        $this->_noController = ($flag) ? true : false;
        return $this;
    }

    /**
     * Retrieve noController flag value
     * 
     * @return boolean
     */
    public function getNoController()
    {
        return $this->_noController;
    }

    /**
     * Set the neverController flag (i.e., whether or not to render into controller subdirectories)
     * 
     * @param  boolean $flag 
     * @return Zend_Controller_Action_Helper_ViewRenderer
     */
    public function setNeverController($flag = true)
    {
        $this->_neverController = ($flag) ? true : false;
        return $this;
    }

    /**
     * Retrieve neverController flag value
     * 
     * @return boolean
     */
    public function getNeverController()
    {
        return $this->_neverController;
    }

    /**
     * Set view script suffix 
     * 
     * @param  string $suffix 
     * @return Zend_Controller_Action_Helper_ViewRenderer
     */
    public function setViewSuffix($suffix)
    {
        $this->_viewSuffix = (string) $suffix;
        return $this;
    }

    /**
     * Get view script suffix 
     * 
     * @return string
     */
    public function getViewSuffix()
    {
        return $this->_viewSuffix;
    }

    /**
     * Set options for rendering a view script
     * 
     * @param  string $action View script to render
     * @param  string $name Response named segment to render to
     * @param  boolean $noController Whether or not to render within a subdirectory named after the controller
     * @return Zend_Controller_Action_Helper_ViewRenderer
     */
    public function setRender($action = null, $name = null, $noController = null)
    {
        if (null !== $action) {
            $this->setScriptAction($action);
        }

        if (null !== $name) {
            $this->setResponseSegment($name);
        }

        if (null !== $noController) {
            $this->setNoController($noController);
        }

        return $this;
    }

    /**
     * Inject values into a spec string
     *
     * Allowed variables are:
     * - :moduleDir - current module directory
     * - :module - current module name
     * - :controller - current controller name
     * - :action - current action name
     * - :suffix - view script file suffix
     * 
     * @param  string $spec 
     * @param  array $vars 
     * @return string
     */
    protected function _translateSpec($spec, array $vars = array())
    {
        $front      = $this->getFrontController();
        $request    = $this->getRequest();
        $module     = $request->getModuleName();
        $controller = $request->getControllerName();
        $action     = $request->getActionName();
        $suffix     = $this->getViewSuffix();

        // Need to get default module name if null returned, so that we get a 
        // controller directory
        if (null === $module) {
            $module = $front->getDispatcher()->getDefaultModule();
        }
        $moduleDir  = $front->getControllerDirectory($module);
        if ((null === $moduleDir) || is_array($moduleDir)) {
            throw new Zend_Controller_Action_Exception('ViewRenderer cannot locate module directory');
        }
        $moduleDir = dirname($moduleDir);

        foreach ($vars as $key => $value) {
            switch ($key) {
                case 'module':
                case 'controller':
                case 'action':
                case 'moduleDir':
                case 'suffix':
                    $$key = (string) $value;
                    break;
                default:
                    break;
            }
        }

        // Module, controller, and action names need normalized delimiters
        $dispatcher = $front->getDispatcher();
        if (null === $this->_pathDelimiters) {
            $this->_pathDelimiters = $dispatcher->getPathDelimiter();
        }
        if (null === $this->_delimiters) {
            $wordDelimiters    = $dispatcher->getWordDelimiter();
            $pathDelimiters    = $dispatcher->getPathDelimiter();
            $this->_delimiters = array_unique(array_merge($wordDelimiters, (array) $this->_pathDelimiters));
        }

        $replacements = array(
            ':moduleDir'  => $moduleDir,
            ':module'     => str_replace($this->_delimiters, '-', strtolower($module)),
            ':controller' => str_replace($this->_delimiters, '-', strtolower(str_replace($this->_pathDelimiters, '/', $controller))),
            ':action'     => str_replace($this->_delimiters, '-', strtolower($action)),
            ':suffix'     => $suffix
        );
        $value = str_replace(array_keys($replacements), array_values($replacements), $spec);
        $value = preg_replace('/-+/', '-', $value);
        return $value;
    }

    /**
     * Render a view script (optionally to a named response segment)
     *
     * Sets the noRender flag to true when called.
     * 
     * @param  string $script 
     * @param  string $name 
     * @return void
     */
    public function renderScript($script, $name = null)
    {
        if (null === $name) {
            $name = $this->getResponseSegment();
        }

        $this->getResponse()->appendBody(
            $this->view->render($script),
            $name
        );

        $this->setNoRender();
    }

    /**
     * Render a view based on path specifications
     *
     * Renders a view based on the view script path specifications.
     *
     * @param  string $action 
     * @param  string $name 
     * @param  boolean $noController 
     * @return void
     */
    public function render($action = null, $name = null, $noController = null)
    {
        $this->setRender($action, $name, $noController);
        $path = $this->getViewScript();
        $this->renderScript($path, $name);
    }

    /**
     * Render a script based on specification variables
     *
     * Pass an action, and one or more specification variables (view script suffix) 
     * to determine the view script path, and render that script.
     * 
     * @param  string $action 
     * @param  array $vars 
     * @param  string $name 
     * @return void
     */
    public function renderBySpec($action = null, array $vars = array(), $name = null)
    {
        if (null !== $name) {
            $this->setResponseSegment($name);
        }

        $path = $this->getViewScript($action, $vars);

        $this->renderScript($path);
    }

    /**
     * postDispatch - auto render a view
     *
     * Only autorenders if: 
     * - _noRender is false
     * - action controller is present
     * - request has not been re-dispatched (i.e., _forward() has not been called)
     * - response is not a redirect
     * 
     * @return void
     */
    public function postDispatch()
    {
        if ($this->getFrontController()->getParam('noViewRenderer')) {
            return;
        }

        if (!$this->_noRender 
            && (null !== $this->_actionController)
            && $this->getRequest()->isDispatched()
            && !$this->getResponse()->isRedirect())
        {
            $this->render();
        }
    }

    /**
     * Use this helper as a method; proxies to setRender()
     * 
     * @param  string $action 
     * @param  string $name 
     * @param  boolean $noController 
     * @return void
     */
    public function direct($action = null, $name = null, $noController = null)
    {
        $this->setRender($action, $name, $noController);
    }
}
