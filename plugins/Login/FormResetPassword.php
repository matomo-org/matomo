<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Login;

use Piwik\Piwik;
use Piwik\QuickForm2;

/**
 *
 */
class FormResetPassword extends QuickForm2
{
    function __construct($id = 'resetpasswordform', $method = 'post', $attributes = null, $trackSubmit = false)
    {
        parent::__construct($id, $method, $attributes, $trackSubmit);
    }

    function init()
    {
        $this->addElement('text', 'form_login')
            ->addRule('required', Piwik::translate('General_Required', Piwik::translate('Login_LoginOrEmail')));

        $password = $this->addElement('password', 'form_password');
        $password->addRule('required', Piwik::translate('General_Required', Piwik::translate('General_Password')));

        $passwordBis = $this->addElement('password', 'form_password_bis');
        $passwordBis->addRule('required', Piwik::translate('General_Required', Piwik::translate('Login_PasswordRepeat')));
        $passwordBis->addRule('eq', Piwik::translate('Login_PasswordsDoNotMatch'), ['operand' => $password]);

        $this->addElement('hidden', 'form_nonce');

        $this->addElement('submit', 'submit');
    }
}
