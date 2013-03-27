<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_Login
 */

/**
 *
 * @package Piwik_Login
 */
class Piwik_Login_FormResetPassword extends Piwik_QuickForm2
{
    function __construct($id = 'resetpasswordform', $method = 'post', $attributes = null, $trackSubmit = false)
    {
        parent::__construct($id, $method, $attributes, $trackSubmit);
    }

    function init()
    {
        $this->addElement('text', 'form_login')
            ->addRule('required', Piwik_Translate('General_Required', Piwik_Translate('General_Username')));

        $password = $this->addElement('password', 'form_password');
        $password->addRule('required', Piwik_Translate('General_Required', Piwik_Translate('Login_Password')));

        $passwordBis = $this->addElement('password', 'form_password_bis');
        $passwordBis->addRule('required', Piwik_Translate('General_Required', Piwik_Translate('Login_PasswordRepeat')));
        $passwordBis->addRule('eq', Piwik_Translate('Login_PasswordsDoNotMatch'), $password);

        $this->addElement('hidden', 'form_nonce');

        $this->addElement('submit', 'submit');
    }
}
