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
use Piwik\Intl\Data\Provider\RegionDataProvider;
use Piwik\IP;
use Piwik\Piwik;
use Piwik\Plugin\ControllerAdmin;
use Piwik\Plugins\LanguagesManager\LanguagesManager;
use Piwik\Plugins\MobileMessaging\SMSProvider;
use Piwik\Translation\Translator;
use Piwik\View;

require_once PIWIK_INCLUDE_PATH . '/plugins/UserCountry/functions.php';

class Controller extends ControllerAdmin
{
    /**
     * @var RegionDataProvider
     */
    private $regionDataProvider;

    /**
     * @var Translator
     */
    private $translator;

    public function __construct(RegionDataProvider $regionDataProvider, Translator $translator)
    {
        $this->regionDataProvider = $regionDataProvider;
        $this->translator = $translator;

        parent::__construct();
    }

    /**
     * Mobile Messaging Settings tab :
     *  - set delegated management
     *  - provide & validate SMS API credential
     *  - add & activate phone numbers
     *  - check remaining credits
     */
    public function index()
    {
        Piwik::checkUserHasSuperUserAccess();

        $view = new View('@MobileMessaging/index');
        $this->setManageVariables($view);

        return $view->render();
    }

    /**
     * Mobile Messaging Settings tab :
     *  - set delegated management
     *  - provide & validate SMS API credential
     *  - add & activate phone numbers
     *  - check remaining credits
     */
    public function userSettings()
    {
        Piwik::checkUserIsNotAnonymous();

        $view = new View('@MobileMessaging/userSettings');
        $this->setManageVariables($view);

        return $view->render();
    }

    private function setManageVariables(View $view)
    {
        $view->isSuperUser = Piwik::hasUserSuperUserAccess();

        $mobileMessagingAPI = API::getInstance();
        $view->delegatedManagement = $mobileMessagingAPI->getDelegatedManagement();
        $view->credentialSupplied = $mobileMessagingAPI->areSMSAPICredentialProvided();
        $view->accountManagedByCurrentUser = $view->isSuperUser || $view->delegatedManagement;
        $view->strHelpAddPhone = $this->translator->translate('MobileMessaging_Settings_PhoneNumbers_HelpAdd', array(
            $this->translator->translate('General_Settings'),
            $this->translator->translate('MobileMessaging_SettingsMenu')
        ));
        $view->creditLeft = 0;
        $view->provider = '';
        if ($view->credentialSupplied && $view->accountManagedByCurrentUser) {
            $view->provider = $mobileMessagingAPI->getSMSProvider();
            $view->creditLeft = $mobileMessagingAPI->getCreditLeft();
        }

        $view->smsProviders = SMSProvider::getAvailableSMSProviders();

        // construct the list of countries from the lang files
        $countries = array();
        foreach ($this->regionDataProvider->getCountryList() as $countryCode => $continentCode) {
            if (isset(CountryCallingCodes::$countryCallingCodes[$countryCode])) {
                $countries[$countryCode] = array(
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
    }
}
