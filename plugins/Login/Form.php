<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_Login
 */

require_once "modules/Form.php";

/**
 * 
 * @package Piwik_Login
 */
class Piwik_Login_Form extends Piwik_Form
{
	function __construct()
	{
		parent::__construct();
		// reset 
		$this->updateAttributes('id="loginform" name="loginform"');
	}
	
	function init()
	{
		$urlToGoAfter = Piwik_Url::getCurrentUrl();			
		
		// if the current url to redirect contains module=login we insteaed redirect to the referer url
		if(stripos($urlToGoAfter,'module=login') !== false)
		{
			$urlToGoAfter = Piwik_Url::getReferer();
		}
		
		$formElements = array(
			array('text', 'form_login', Piwik_Translate('Login_login')),
			array('password', 'form_password', Piwik_Translate('Login_password')),
			array('hidden', 'form_url', $urlToGoAfter),
		);
		$this->addElements( $formElements );
		
		$formRules = array(
			array('form_login', sprintf(Piwik_Translate('General_Required'), 'login'), 'required'),
			array('form_password', sprintf(Piwik_Translate('General_Required'), 'password'), 'required'),
		);
		$this->addRules( $formRules );	
		
		$this->addElement('submit', 'submit', Piwik_Translate('Login_Go'));
	
	}
	
	
}

