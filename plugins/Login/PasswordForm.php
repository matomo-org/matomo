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
class Piwik_Login_PasswordForm extends Piwik_Form
{
	function __construct()
	{
		parent::__construct();
		// reset
		$this->updateAttributes('id="lostpasswordform" name="lostpasswordform"');
	}

	function init()
	{
		$formElements = array(
			array('text', 'form_login'),
		);
		$this->addElements( $formElements );

		$formRules = array(
			array('form_login', sprintf(Piwik_Translate('General_Required'), Piwik_Translate('Login_LoginOrEmail')), 'required'),
		);
		$this->addRules( $formRules );

		$this->addElement('submit', 'submit');
	}
}
