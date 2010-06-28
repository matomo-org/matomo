<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 *
 * @category Piwik_Plugins
 * @package Piwik_Login
 */

/**
 *
 * @package Piwik_Login
 */
class Piwik_Login_FormLogin extends Piwik_QuickForm
{
	function __construct()
	{
		parent::__construct();
		// reset
		$this->updateAttributes('id="loginform" name="loginform"');
	}

	function init()
	{
		$formElements = array(
			array('text', 'form_login'),
			array('password', 'form_password'),
			array('hidden', 'form_nonce'),
		);
		$this->addElements( $formElements );

		$formRules = array(
			array('form_login', sprintf(Piwik_Translate('General_Required'), Piwik_Translate('General_Username')), 'required'),
			array('form_password', sprintf(Piwik_Translate('General_Required'), Piwik_Translate('Login_Password')), 'required'),
		);
		$this->addRules( $formRules );

		$this->addElement('submit', 'submit');
	}
}
