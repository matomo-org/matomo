<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\TwoFactorAuth;

use Piwik\Piwik;
use Piwik\QuickForm2;

/**
 *
 */
class FormTwoFactorAuthCode extends QuickForm2
{
    function __construct($id = 'login_form', $method = 'post', $attributes = null, $trackSubmit = false)
    {
        parent::__construct($id, $method, $attributes, $trackSubmit);
    }

    function init()
    {
        $this->addElement('text', 'form_authcode')
            ->addRule('required',
                Piwik::translate('General_Required', 'Authentication code'));

        $this->addElement('hidden', 'form_nonce');

        $this->addElement('submit', 'submit');
    }
}
