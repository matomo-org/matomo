<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Login
 */
namespace Piwik\Plugins\Login;

use HTML_QuickForm2_DataSource_Array;
use Piwik\Piwik;
use Piwik\QuickForm2;

/**
 *
 * @package Login
 */
class FormLogin extends QuickForm2
{
    function __construct($id = 'login_form', $method = 'post', $attributes = null, $trackSubmit = false)
    {
        parent::__construct($id, $method, $attributes, $trackSubmit);
    }

    function init()
    {
        $this->addElement('text', 'form_login')
            ->addRule('required', Piwik::translate('General_Required', Piwik::translate('General_Username')));

        $this->addElement('password', 'form_password')
            ->addRule('required', Piwik::translate('General_Required', Piwik::translate('General_Password')));

        $this->addElement('hidden', 'form_nonce');

        $this->addElement('checkbox', 'form_rememberme');

        $this->addElement('submit', 'submit');

        // default values
        $this->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
                                                                       'form_rememberme' => 0,
                                                                  )));
    }
}
