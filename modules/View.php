<?php
require_once 'Smarty/Smarty.class.php';

class Piwik_View
{
	private $smarty = false;
	private $variables = array();
	
	public function __construct( $templateFile, $smConf = array())
	{
		$this->template = $templateFile;
		$this->smarty = new Smarty();

		foreach($smConf as $key => $value)
		{
			$this->smarty->$key = $value;
		}
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
		$this->variables[$key] = $val;
	}

	/**
	 * Retrieves an assigned variable.
	 * VAR names may not be prefixed with '_'.
	 *	@param string $key The variable name.
	 *	@return mixed The variable value.
	 */
	public function __get($key)
	{
		if(!isset($this->variables[$key]))
		{
			throw new Exception("Variable $key not known!");
		}
		return $this->variables[$key];
	}

	public function render()
	{
		$this->smarty->assign('smarty', $this);
		return $this->smarty->fetch($this->template);
	}
	
	public function addForm( $form )
	{
		// Create the renderer object	
		$renderer = new HTML_QuickForm_Renderer_ArraySmarty($this->smarty);
		
		// build the HTML for the form
		$form->accept($renderer);
		
		// assign array with form data
		$this->smarty->assign('form', $renderer->toArray());
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
