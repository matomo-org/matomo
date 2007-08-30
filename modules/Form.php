<?php

require_once "HTML/QuickForm.php";
require_once "HTML/QuickForm/Renderer/ArraySmarty.php";

class Piwik_Form extends HTML_QuickForm
{
	function __construct( $action = '' )
	{
		parent::HTML_QuickForm('form', 'POST', $action);
	}
	
}
