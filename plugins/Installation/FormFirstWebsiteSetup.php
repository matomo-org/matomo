<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Installation;

use DateTimeZone;
use HTML_QuickForm2_DataSource_Array;
use HTML_QuickForm2_Factory;
use HTML_QuickForm2_Rule;
use NumberFormatter;
use Piwik\Access;
use Piwik\Option;
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

        $urlExample = 'https://example.org';
        $javascriptOnClickUrlExample = "javascript:if (this.value=='$urlExample'){this.value='https://';} this.style.color='black';";

        $timezones = API::getInstance()->getTimezonesList();
        $timezones = array_merge(array('No timezone' => Piwik::translate('SitesManager_SelectACity')), $timezones);

        // Use server timezone as default, unless a default timezone has already
        // been defined from outside the installation wizard.
        // If the server timezone is UTC, it is likely a default not specified
        // explicitly by the sysadm, so ignore this.
        $timezone = Option::get(API::OPTION_DEFAULT_TIMEZONE) ?: PIWIK_DEFAULT_TIMEZONE;
        if (in_array(strtolower($timezone), array('utc', 'etc/utc', 'gmt', 'etc/gmt'))) {
            $timezone = null;
        }

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

        $this->addElement('submit', 'submit', array('value' => Piwik::translate('General_Next') . ' »', 'class' => 'btn'));

        // default values
        $this->addDataSource(new HTML_QuickForm2_DataSource_Array(array(
                                                                       'url' => $urlExample,
                                                                       'timezone' => $timezone,
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

        // If intl extension is installed, get default currency from timezone country.
        if (!Option::get(API::OPTION_DEFAULT_CURRENCY) && $timezone && class_exists('NumberFormatter')) {
            try {
                $zone = new DateTimeZone($timezone);
                $location = $zone->getLocation();
            } catch (\Exception $e) {
            }
            if (isset($location['country_code']) && $location['country_code'] !== '??') {
                $formatter = new NumberFormatter('en_' . $location['country_code'], NumberFormatter::CURRENCY);
                $currencyCode = $formatter->getTextAttribute(NumberFormatter::CURRENCY_CODE);
                if ($currencyCode) {
                    try {
                        Access::doAsSuperUser(function () use ($currencyCode) {
                            API::getInstance()->setDefaultCurrency($currencyCode);
                        });
                    } catch (\Exception $e) {
                    }
                }
            }
        }

        return true;
    }
}
