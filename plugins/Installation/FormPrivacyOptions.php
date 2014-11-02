<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Installation;

use HTML_QuickForm2_DataSource_Array;
use Piwik\Piwik;
use Piwik\QuickForm2;

class FormPrivacyOptions extends QuickForm2
{
    public function __construct($id = 'privacyoptionsform', $method = 'post', $attributes = null, $trackSubmit = false)
    {
        parent::__construct($id, $method, $attributes, $trackSubmit);
    }

    public function init()
    {
        $this->addElement('checkbox', 'anonymise_ip_addresses', null,
            array(
                'content' => '&nbsp;&nbsp;' . Piwik::translate('Installation_AnonymiseIPAddressesField'),
            ));

        $this->addElement('submit', 'submit', array('value' => Piwik::translate('General_ContinueToPiwik') . ' »', 'class' => 'submit'));

        // default values
        $this->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
            'anonymise_ip_addresses' => 1,
        )));
    }
}
