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
class Piwik_Login_ResetPasswordForm extends Piwik_Form
{
	function __construct()
	{
		parent::__construct();
		// reset
		$this->updateAttributes('id="resetpasswordform" name="resetpasswordform"');
	}

	function init()
	{
		$resetToken = Piwik_Common::getRequestVar('token', '', 'string');

		$formElements = array(
			array('text', 'form_login'),
			array('password', 'form_password'),
			array('password', 'form_password_bis'),
			array('text', 'form_token'),
		);
		$this->addElements( $formElements );

		$defaults = array(
			'form_token' => $resetToken,
		);
		$this->setDefaults($defaults);

		$formRules = array(
			array('form_login', sprintf(Piwik_Translate('General_Required'), Piwik_Translate('Login_Login')), 'required'),
			array('form_password', sprintf(Piwik_Translate('General_Required'), Piwik_Translate('Login_Password')), 'required'),
			array('form_password_bis', sprintf(Piwik_Translate('General_Required'), Piwik_Translate('Login_PasswordRepeat')), 'required'),
			array('form_token', sprintf(Piwik_Translate('General_Required'), Piwik_Translate('Login_PasswordResetToken')), 'required'),
			array('form_password', Piwik_Translate( 'Login_PasswordsDoNotMatch'), 'fieldHaveSameValue', 'form_password_bis'),
		);
		$this->addRules( $formRules );

		$this->addElement('submit', 'submit');
	}
}
