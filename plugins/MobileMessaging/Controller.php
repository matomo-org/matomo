<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\MobileMessaging;

use Piwik\Common;
use Piwik\IP;
use Piwik\Piwik;
use Piwik\Plugins\LanguagesManager\LanguagesManager;
use Piwik\Plugins\MobileMessaging\SMSProvider;
use Piwik\View;

require_once PIWIK_INCLUDE_PATH . '/plugins/UserCountry/functions.php';

/**
 *
 */
class Controller extends \Piwik\Plugin\ControllerAdmin
{
    /*
     * Mobile Messaging Settings tab :
     *  - set delegated management
     *  - provide & validate SMS API credential
     *  - add & activate phone numbers
     *  - check remaining credits
     */
    public function index()
    {
        Piwik::checkUserIsNotAnonymous();

        $view = new View('@MobileMessaging/index');

        $view->isSuperUser = Piwik::hasUserSuperUserAccess();

        $mobileMessagingAPI = API::getInstance();
        $view->delegatedManagement = $mobileMessagingAPI->getDelegatedManagement();
        $view->credentialSupplied = $mobileMessagingAPI->areSMSAPICredentialProvided();
        $view->accountManagedByCurrentUser = $view->isSuperUser || $view->delegatedManagement;
        $view->strHelpAddPhone = Piwik::translate('MobileMessaging_Settings_PhoneNumbers_HelpAdd', array(Piwik::translate('General_Settings'), Piwik::translate('MobileMessaging_SettingsMenu')));
        if ($view->credentialSupplied && $view->accountManagedByCurrentUser) {
            $view->provider = $mobileMessagingAPI->getSMSProvider();
            $view->creditLeft = $mobileMessagingAPI->getCreditLeft();
        }

        $view->smsProviders = SMSProvider::$availableSMSProviders;

        // construct the list of countries from the lang files
        $countries = array();
        foreach (Common::getCountriesList() as $countryCode => $continentCode) {
            if (isset(CountryCallingCodes::$countryCallingCodes[$countryCode])) {
                $countries[$countryCode] =
                    array(
                        'countryName'        => \Piwik\Plugins\UserCountry\countryTranslate($countryCode),
                        'countryCallingCode' => CountryCallingCodes::$countryCallingCodes[$countryCode],
                    );
            }
        }
        $view->countries = $countries;

        $view->defaultCountry = Common::getCountry(
            LanguagesManager::getLanguageCodeForCurrentUser(),
            true,
            IP::getIpFromHeader()
        );

        $view->phoneNumbers = $mobileMessagingAPI->getPhoneNumbers();

        $this->setBasicVariablesView($view);

        return $view->render();
    }
}
