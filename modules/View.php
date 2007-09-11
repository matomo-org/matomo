<?php
require_once 'Smarty/Smarty.class.php';

class Piwik_View
{
	private $template = '';
	private $smarty = false;
	private $variables = array();
	
	public function __construct( $templateFile, $smConf = array())
	{
		$this->template = $templateFile;
		$this->smarty = new Smarty();

		if(count($smConf) == 0)
		{
			$smConf = Zend_Registry::get('config')->smarty;
		}
		foreach($smConf as $key => $value)
		{
			$this->smarty->$key = $value;
		}
		$this->smarty->template_dir = $smConf->template_dir->toArray();
		$this->smarty->plugins_dir = $smConf->plugins_dir->toArray();
		
//		$this->smarty->load_filter('output','trimwhitespace');
		
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
		$this->totalTimeGeneration = Zend_Registry::get('timer')->getTime();
//		$this->smarty->assign('smarty', $this);
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
		$this->smarty->assign('element_list', $form->getElementList());//$renderer->toArray());
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

/*	public function isCached($template)
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
}
?>
