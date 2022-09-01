<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\MobileMessaging;

use Piwik\Common;
use Piwik\DataTable\Renderer\Json;
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
        Piwik::checkUserIsNotAnonymous();

        $view = new View('@MobileMessaging/index');
        $this->setManageVariables($view);

        return $view->render();
    }

    private function setManageVariables(View $view)
    {
        $view->isSuperUser = Piwik::hasUserSuperUserAccess();

        $mobileMessagingAPI = API::getInstance();
        $model = new Model();
        $view->delegatedManagement = $mobileMessagingAPI->getDelegatedManagement();
        $view->credentialSupplied = $mobileMessagingAPI->areSMSAPICredentialProvided();
        $view->accountManagedByCurrentUser = $view->isSuperUser || $view->delegatedManagement;
        $view->strHelpAddPhone = $this->translator->translate('MobileMessaging_Settings_PhoneNumbers_HelpAdd', array(
            $this->translator->translate('General_Settings'),
            $this->translator->translate('MobileMessaging_SettingsMenu')
        ));
        $view->credentialError = null;
        $view->creditLeft = 0;
        $currentProvider = '';
        if ($view->credentialSupplied && $view->accountManagedByCurrentUser) {
            $currentProvider = $mobileMessagingAPI->getSMSProvider();
            try {
                $view->creditLeft = $mobileMessagingAPI->getCreditLeft();
            } catch (\Exception $e) {
                $view->credentialError = $e->getMessage();
            }
        }

        $view->delegateManagementOptions = array(
            array('key' => '0',
                  'value' => Piwik::translate('General_No'),
                  'description' => Piwik::translate('General_Default') . '. ' .
                                   Piwik::translate('MobileMessaging_Settings_LetUsersManageAPICredential_No_Help')),
            array('key' => '1',
                  'value' => Piwik::translate('General_Yes'),
                  'description' => Piwik::translate('MobileMessaging_Settings_LetUsersManageAPICredential_Yes_Help'))
        );

        $providers = array();
        $providerOptions = array();
        foreach (SMSProvider::findAvailableSmsProviders() as $provider) {
            if (empty($currentProvider)) {
                $currentProvider = $provider->getId();
            }
            $providers[$provider->getId()] = $provider->getDescription();
            $providerOptions[$provider->getId()] = $provider->getId();
        }

        $view->provider = $currentProvider;
        $view->smsProviders = $providers;
        $view->smsProviderOptions = $providerOptions;

        $defaultCountry = Common::getCountry(
            LanguagesManager::getLanguageCodeForCurrentUser(),
            true,
            IP::getIpFromHeader()
        );

        $view->defaultCallingCode = '';

        // construct the list of countries from the lang files
        $countries = array(array('key' => '', 'value' => ''));
        foreach ($this->regionDataProvider->getCountryList() as $countryCode => $continentCode) {
            if (isset(CountryCallingCodes::$countryCallingCodes[$countryCode])) {

                if ($countryCode == $defaultCountry) {
                    $view->defaultCallingCode = CountryCallingCodes::$countryCallingCodes[$countryCode];
                }

                $countries[] = array(
                    'key' => CountryCallingCodes::$countryCallingCodes[$countryCode],
                    'value' => \Piwik\Plugins\UserCountry\countryTranslate($countryCode)
                );
            }
        }
        $view->countries = $countries;

        $view->phoneNumbers = $model->getPhoneNumbers(Piwik::getCurrentUserLogin());

        $this->setBasicVariablesView($view);
    }

    public function getCredentialFields()
    {
        $provider = Common::getRequestVar('provider', '');

        $credentialFields = array();

        foreach (SMSProvider::findAvailableSmsProviders() as $availableSmsProvider) {
            if ($availableSmsProvider->getId() == $provider) {
                $credentialFields = $availableSmsProvider->getCredentialFields();
                break;
            }
        }

        Json::sendHeaderJSON();
        return json_encode($credentialFields);
    }
}
