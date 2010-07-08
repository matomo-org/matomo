<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik
 * @package Piwik
 */
/*
 * Transition for pre-Piwik 0.4.4
 * @todo Remove this post-1.0
 */
if(!defined('PIWIK_USER_PATH'))
{
	define('PIWIK_USER_PATH', PIWIK_INCLUDE_PATH);
}

/**
 * View class to render the user interface
 *
 * @package Piwik
 */
class Piwik_View implements Piwik_iView
{
	// view types
	const STANDARD = 0; // REGULAR, FULL, CLASSIC
	const MOBILE = 1;
	const CLI = 2;

	private $template = '';
	private $smarty = false;
	private $variables = array();
	private $contentType = 'text/html; charset=utf-8';

	public function __construct( $templateFile, $smConf = array(), $filter = true )
	{
		$this->template = $templateFile;
		$this->smarty = new Piwik_Smarty();

		if(count($smConf) == 0)
		{
			$smConf = Zend_Registry::get('config')->smarty;
		}
		foreach($smConf as $key => $value)
		{
			$this->smarty->$key = $value;
		}

		$this->smarty->template_dir = $smConf->template_dir->toArray();
		array_walk($this->smarty->template_dir, array("Piwik_View","addPiwikPath"), PIWIK_INCLUDE_PATH);

		$this->smarty->plugins_dir = $smConf->plugins_dir->toArray();
		array_walk($this->smarty->plugins_dir, array("Piwik_View","addPiwikPath"), PIWIK_INCLUDE_PATH);

		$this->smarty->compile_dir = $smConf->compile_dir;
		Piwik_View::addPiwikPath($this->smarty->compile_dir, null, PIWIK_USER_PATH);

		$this->smarty->cache_dir = $smConf->cache_dir;
		Piwik_View::addPiwikPath($this->smarty->cache_dir, null, PIWIK_USER_PATH);

		$error_reporting = $smConf->error_reporting;
		if($error_reporting != (string)(int)$error_reporting)
		{
			$error_reporting = self::bitwise_eval($error_reporting);
		}
		$this->smarty->error_reporting = $error_reporting;

		$this->smarty->assign('tag', 'piwik=' . Piwik_Version::VERSION);
		if($filter)
		{
			$this->smarty->load_filter('output', 'cachebuster');
			$this->smarty->load_filter('output', 'ajaxcdn');
			$this->smarty->load_filter('output', 'trimwhitespace');
		}

		// global value accessible to all templates: the piwik base URL for the current request
		$this->piwikUrl = Piwik_Url::getCurrentUrlWithoutFileName();
	}
	
	/**
	 * Directly assigns a variable to the view script.
	 * VAR names may not be prefixed with '_'.
	 *
	 *	@param string $key The variable name.
	 *	@param mixed $val The variable value.
	 */
	public function __set($key, $val)
	{
		$this->smarty->assign($key, $val);
	}

	/**
	 * Retrieves an assigned variable.
	 * VAR names may not be prefixed with '_'.
	 *
	 *	@param string $key The variable name.
	 *	@return mixed The variable value.
	 */
	public function __get($key)
	{
		return $this->smarty->get_template_vars($key);
	}

	/**
	 * Renders the current view.
	 * 
	 * @return string Generated template
	 */
	public function render()
	{
		try {
			$this->currentModule = Piwik::getModule();
			$this->currentPluginName = Piwik::getCurrentPlugin()->getClassName();
			$this->userLogin = Piwik::getCurrentUserLogin();
			
			$showWebsiteSelectorInUserInterface = Zend_Registry::get('config')->General->show_website_selector_in_user_interface;
			if($showWebsiteSelectorInUserInterface)
			{
				$sites = Piwik_SitesManager_API::getInstance()->getSitesWithAtLeastViewAccess();
				usort($sites, create_function('$site1, $site2', 'return strcasecmp($site1["name"], $site2["name"]);'));
				$this->sites = $sites;
			}
			$this->showWebsiteSelectorInUserInterface = $showWebsiteSelectorInUserInterface;
			$this->url = Piwik_Url::getCurrentUrl();
			$this->token_auth = Piwik::getCurrentUserTokenAuth();
			$this->userHasSomeAdminAccess = Piwik::isUserHasSomeAdminAccess();
			$this->userIsSuperUser = Piwik::isUserIsSuperUser();
			$this->piwik_version = Piwik_Version::VERSION;
			$this->latest_version_available = Piwik_UpdateCheck::isNewestVersionAvailable();

			$this->loginModule = Piwik::getLoginPluginName();
		} catch(Exception $e) {
			// can fail, for example at installation (no plugin loaded yet)		
		}
		
		$this->totalTimeGeneration = Zend_Registry::get('timer')->getTime();
		try {
			$this->totalNumberOfQueries = Piwik::getQueryCount();
		}
		catch(Exception $e){
			$this->totalNumberOfQueries = 0;
		}
 
		@header('Content-Type: '.$this->contentType);
		@header("Pragma: ");
		@header("Cache-Control: no-store, must-revalidate");
		
		return $this->smarty->fetch($this->template);
	}

	/**
	 * Set Content-Type field in HTTP response
	 *
	 * @param string $contentType
	 */
	public function setContentType( $contentType )
	{
		$this->contentType = $contentType;
	}

	/**
	 * Add form to view
	 *
	 * @param Piwik_QuickForm2 $form
	 */
	public function addForm( $form )
	{
		if($form instanceof Piwik_QuickForm2)
		{
			HTML_QuickForm2_Renderer::register('smarty', 'HTML_QuickForm2_Renderer_Smarty');

			// Create the renderer object	
			$renderer = HTML_QuickForm2_Renderer::factory('smarty');
			$renderer->setOption('group_errors', true);

			// build the HTML for the form
			$form->render($renderer);

			// assign array with form data
			$this->smarty->assign('form_data', $renderer->toArray());
			$this->smarty->assign('element_list', $form->getElementList());
		}
	}

	/**
	 * Assign value to a variable for use in Smarty template
	 *
	 * @param string|array $var
	 * @param mixed $value
	 */
	public function assign($var, $value=null)
	{
		if (is_string($var))
		{
			$this->smarty->assign($var, $value);
		}
		elseif (is_array($var))
		{
			foreach ($var as $key => $value)
			{
				$this->smarty->assign($key, $value);
			}
		}
	}

	/**
	 * Clear compiled Smarty templates 
	 */
	static public function clearCompiledTemplates()
	{
		$view = Piwik_View::factory();
		$view->smarty->clear_compiled_tpl();
	}

/*
	public function isCached($template)
	{
		if ($this->smarty->is_cached($template))
		{
			return true;
		}
		return false;
	}
	public function setCaching($caching)
	{
		$this->smarty->caching = $caching;
	}
*/

	/**
	 * Prepend relative paths with absolute Piwik path
	 *
	 * @param string $value relative path (pass by reference)
	 * @param int $key (don't care)
	 * @param string $path Piwik root
	 */
	static public function addPiwikPath(&$value, $key, $path)
	{
		if($value[0] != '/' && $value[0] != DIRECTORY_SEPARATOR)
		{
			$value = $path ."/$value";
		}
	}

	/**
	 * Evaluate expression containing only bitwise operators.
	 * Replaces defined constants with corresponding values.
	 * Does not use eval() or create_function().
	 *
	 * @param string $expression Expression.
	 * @return string
	 */
	static public function bitwise_eval($expression)
	{
		// replace defined constants
		$buf = get_defined_constants(true);

		// use only the 'Core' PHP constants, e.g., E_ALL, E_STRICT, ...
		$consts = isset($buf['Core']) ? $buf['Core'] : (isset($buf['mhash']) ? $buf['mhash'] : $buf['internal']);
		$expression = str_replace(' ', '', strtr($expression, $consts));

		// bitwise operators in order of precedence (highest to lowest)
		// @todo: boolean ! (NOT) and parentheses aren't handled
		$expression = preg_replace_callback('/~(-?[0-9]+)/', create_function('$matches', 'return (string)((~(int)$matches[1]));'), $expression);
		$expression = preg_replace_callback('/(-?[0-9]+)&(-?[0-9]+)/', create_function('$matches', 'return (string)((int)$matches[1]&(int)$matches[2]);'), $expression);
		$expression = preg_replace_callback('/(-?[0-9]+)\^(-?[0-9]+)/', create_function('$matches', 'return (string)((int)$matches[1]^(int)$matches[2]);'), $expression);
		$expression = preg_replace_callback('/(-?[0-9]+)\|(-?[0-9]+)/', create_function('$matches', 'return (string)((int)$matches[1]|(int)$matches[2]);'), $expression);

		return (string)((int)$expression & PHP_INT_MAX);
	}

	/**
	 * View factory method
	 *
	 * @param $templateName Template name (e.g., 'index')
	 * @param $viewType     View type (e.g., Piwik_View::CLI)
	 */
	static public function factory( $templateName = null, $viewType = null)
	{
		Piwik_PostEvent('View.getViewType', $viewType);

		// get caller
		$bt = @debug_backtrace();
		if($bt === null || !isset($bt[0]))
		{
			throw new Exception("View factory cannot be invoked");
		}
		$path = dirname($bt[0]['file']);

		// determine best view type
		if($viewType === null)
		{
			if(Piwik_Common::isPhpCliMode())
			{
				$viewType = self::CLI;
			}
			else
			{
				$viewType = self::STANDARD;
			}
		}

		// get template filename
		if($viewType == self::CLI)
		{
			$templateFile = $path.'/templates/cli_'.$templateName.'.tpl';
			if(file_exists($templateFile))
			{
				return new Piwik_View($templateFile, array(), false);
			}

			$viewType = self::STANDARD;
		}

		if($viewType == self::MOBILE)
		{
			$templateFile = $path.'/templates/mobile_'.$templateName.'.tpl';
			if(!file_exists($templateFile))
			{
				$viewType = self::STANDARD;
			}
		}

		if($viewType != self::MOBILE)
		{
			$templateFile = $path.'/templates/'.$templateName.'.tpl';
		}
		
		// Specified template not found
		// We allow for no specified template 
		if(!empty($templateName)
			&& !file_exists($templateFile))
		{
			throw new Exception('Template not found: '.$templateFile);
		}
		return new Piwik_View($templateFile);
	}
}
