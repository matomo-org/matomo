<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_MobileMessaging
 */

require_once PIWIK_INCLUDE_PATH . '/plugins/UserCountry/functions.php';

/**
 *
 * @package Piwik_MobileMessaging
 */
class Piwik_MobileMessaging_Controller extends Piwik_Controller_Admin
{
    /*
     * Mobile Messaging Settings tab :
     *  - set delegated management
     *  - provide & validate SMS API credential
     *  - add & activate phone numbers
     *  - check remaining credits
     */
    function index()
    {
        Piwik::checkUserIsNotAnonymous();

        $view = Piwik_View::factory('Settings');

        $view->isSuperUser = Piwik::isUserIsSuperUser();

        $mobileMessagingAPI = Piwik_MobileMessaging_API::getInstance();
        $view->delegatedManagement = $mobileMessagingAPI->getDelegatedManagement();
        $view->credentialSupplied = $mobileMessagingAPI->areSMSAPICredentialProvided();
        $view->accountManagedByCurrentUser = $view->isSuperUser || $view->delegatedManagement;
        $view->strHelpAddPhone = Piwik_Translate('MobileMessaging_Settings_PhoneNumbers_HelpAdd', array(Piwik_Translate('UserSettings_SubmenuSettings'), Piwik_Translate('MobileMessaging_SettingsMenu')));
        if ($view->credentialSupplied && $view->accountManagedByCurrentUser) {
            $view->provider = $mobileMessagingAPI->getSMSProvider();
            $view->creditLeft = $mobileMessagingAPI->getCreditLeft();
        }

        $view->smsProviders = Piwik_MobileMessaging_SMSProvider::$availableSMSProviders;

        // construct the list of countries from the lang files
        $countries = array();
        foreach (Piwik_Common::getCountriesList() as $countryCode => $continentCode) {
            if (isset(Piwik_MobileMessaging_CountryCallingCodes::$countryCallingCodes[$countryCode])) {
                $countries[$countryCode] =
                    array(
                        'countryName'        => Piwik_CountryTranslate($countryCode),
                        'countryCallingCode' => Piwik_MobileMessaging_CountryCallingCodes::$countryCallingCodes[$countryCode],
                    );
            }
        }
        $view->countries = $countries;

        $view->defaultCountry = Piwik_Common::getCountry(
            Piwik_LanguagesManager::getLanguageCodeForCurrentUser(),
            true,
            Piwik_IP::getIpFromHeader()
        );

        $view->phoneNumbers = $mobileMessagingAPI->getPhoneNumbers();

        $this->setBasicVariablesView($view);

        $view->menu = Piwik_GetAdminMenu();
        echo $view->render();
    }
}
