<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_Visualization
 */

/**
 * 
 * @package Piwik_Visualization
 *
 */
class Piwik_View implements Piwik_iView
{
	private $template = '';
	private $smarty = false;
	private $variables = array();
	
	public function __construct( $templateFile, $smConf = array())
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
		array_walk($this->smarty->template_dir, array("Piwik_View","addPiwikPath"));

		$this->smarty->plugins_dir = $smConf->plugins_dir->toArray();
		array_walk($this->smarty->plugins_dir, array("Piwik_View","addPiwikPath"));

		$this->smarty->compile_dir = $smConf->compile_dir;
		Piwik_View::addPiwikPath($this->smarty->compile_dir, null);

		$this->smarty->cache_dir = $smConf->cache_dir;
		Piwik_View::addPiwikPath($this->smarty->cache_dir, null);

		$this->smarty->error_reporting = $smConf->debugging;
		$this->smarty->error_reporting = $smConf->error_reporting;

		$this->smarty->assign('tag', 'piwik=' . Piwik_Version::VERSION);
		$this->smarty->load_filter('output', 'cachebuster');

		$this->smarty->load_filter('output', 'trimwhitespace');
		
		// global value accessible to all templates: the piwik base URL for the current request
		$this->piwikUrl = Piwik_Url::getCurrentUrlWithoutFileName();
		
	}
	
	/**
	 * Directly assigns a variable to the view script.
	 * VAR names may not be prefixed with '_'.
	 *	@param string $key The variable name.
	 *	@param mixed $val The variable value.
	 *	@return void
	 */
	public function __set($key, $val)
	{
		$this->smarty->assign($key, $val);
	}

	/**
	 * Retrieves an assigned variable.
	 * VAR names may not be prefixed with '_'.
	 *	@param string $key The variable name.
	 *	@return mixed The variable value.
	 */
	public function __get($key)
	{
		return $this->smarty->get_template_vars($key);
	}

	public function render()
	{
		try {
			$this->currentModule = Piwik::getModule();
			$this->currentPluginName = Piwik::getCurrentPlugin()->getName();
			$this->userLogin = Piwik::getCurrentUserLogin();
			
			$showWebsiteSelectorInUserInterface = Zend_Registry::get('config')->General->show_website_selector_in_user_interface;
			if($showWebsiteSelectorInUserInterface)
			{
				$sites = Piwik_SitesManager_API::getSitesWithAtLeastViewAccess();
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

			$this->loginModule = Zend_Registry::get('auth')->getName();
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
 
		@header('Content-Type: text/html; charset=utf-8');
		@header("Pragma: ");
		@header("Cache-Control: no-store, must-revalidate");
		
		return $this->smarty->fetch($this->template);
	}
	
	public function addForm( $form )
	{
		// Create the renderer object	
		$renderer = new HTML_QuickForm_Renderer_ArraySmarty($this->smarty);
		
		// build the HTML for the form
		$form->accept($renderer);
		
		// assign array with form data
		$this->smarty->assign('form_data', $renderer->toArray());
		$this->smarty->assign('element_list', $form->getElementList());
	}
	
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

	public function clearCompiledTemplates()
	{
		$this->smarty->clear_compiled_tpl();
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

	static public function addPiwikPath(&$value, $key)
	{
		if($value[0] != '/' && $value[0] != DIRECTORY_SEPARATOR)
		{
			$value = PIWIK_INCLUDE_PATH ."/$value";
		}
	}
}
