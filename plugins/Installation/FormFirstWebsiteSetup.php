<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Installation;

use HTML_QuickForm2_DataSource_Array;
use HTML_QuickForm2_Factory;
use HTML_QuickForm2_Rule;
use Piwik\Log;
use Piwik\Access;
use Piwik\Piwik;
use Piwik\Plugins\SitesManager\API;
use Piwik\QuickForm2;

/**
 *
 */
class FormFirstWebsiteSetup extends QuickForm2
{
    function __construct($id = 'websitesetupform', $method = 'post', $attributes = null, $trackSubmit = false)
    {
        parent::__construct($id, $method, $attributes, $trackSubmit);
    }

    function init()
    {
        HTML_QuickForm2_Factory::registerRule('checkTimezone', 'Piwik\Plugins\Installation\Rule_isValidTimezone');

        $urlExample = 'http://example.org';
        $javascriptOnClickUrlExample = "javascript:if (this.value=='$urlExample'){this.value='http://';} this.style.color='black';";

        $timezones = API::getInstance()->getTimezonesList();
        $timezones = array_merge(array('No timezone' => Piwik::translate('SitesManager_SelectACity')), $timezones);

        $this->addElement('text', 'siteName')
            ->setLabel(Piwik::translate('Installation_SetupWebSiteName'))
            ->addRule('required', Piwik::translate('General_Required', Piwik::translate('Installation_SetupWebSiteName')));

        $url = $this->addElement('text', 'url')
            ->setLabel(Piwik::translate('Installation_SetupWebSiteURL'));
        $url->setAttribute('style', 'color:rgb(153, 153, 153);');
        $url->setAttribute('onfocus', $javascriptOnClickUrlExample);
        $url->setAttribute('onclick', $javascriptOnClickUrlExample);
        $url->addRule('required', Piwik::translate('General_Required', Piwik::translate('Installation_SetupWebSiteURL')));

        $tz = $this->addElement('select', 'timezone')
            ->setLabel(Piwik::translate('Installation_Timezone'))
            ->loadOptions($timezones);
        $tz->addRule('required', Piwik::translate('General_Required', Piwik::translate('Installation_Timezone')));
        $tz->addRule('checkTimezone', Piwik::translate('General_NotValid', Piwik::translate('Installation_Timezone')));
        $tz = $this->addElement('select', 'ecommerce')
            ->setLabel(Piwik::translate('Goals_Ecommerce'))
            ->loadOptions(array(
                               0 => Piwik::translate('SitesManager_NotAnEcommerceSite'),
                               1 => Piwik::translate('SitesManager_EnableEcommerce'),
                          ));

        $this->addElement('submit', 'submit', array('value' => Piwik::translate('General_Next') . ' »', 'class' => 'submit'));

        // default values
        $this->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
                                                                       'url' => $urlExample,
                                                                  )));
    }
}

/**
 * Timezone validation rule
 *
 */
class Rule_isValidTimezone extends HTML_QuickForm2_Rule
{
    function validateOwner()
    {
        try {
            $timezone = $this->owner->getValue();
            if (!empty($timezone)) {
                Access::doAsSuperUser(function () use ($timezone) {
                    API::getInstance()->setDefaultTimezone($timezone);
                });
            }
        } catch (\Exception $e) {
            return false;
        }
        return true;
    }
}
