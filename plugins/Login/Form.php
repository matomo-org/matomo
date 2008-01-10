<?php
require_once "modules/Form.php";
class Piwik_Login_Form extends Piwik_Form
{
	function __construct()
	{
		parent::__construct();
	}
	
	function init()
	{
		$urlToGoAfter = Piwik_Url::getCurrentUrl();			
		
		$formElements = array(
			array('text', 'form_login', 'login:'),
			array('password', 'form_password', 'pass:'),
			array('hidden', 'form_url', $urlToGoAfter),
		);
		$this->addElements( $formElements );
		
		$formRules = array(
			array('form_login', sprintf('%s required', 'login'), 'required'),
			array('form_password', sprintf('%s required', 'password'), 'required'),
		);
		$this->addRules( $formRules );	
		
		$this->addElement('submit', 'submit', 'Go!');
		$this->addElement('submit', 'back', 'Cancel');
	
	}
	
	
}

