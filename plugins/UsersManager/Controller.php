<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UsersManager;

use Exception;
use Piwik\API\Request;
use Piwik\API\ResponseBuilder;
use Piwik\Common;
use Piwik\Config\GeneralConfig;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Nonce;
use Piwik\Notification;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Plugin\ControllerAdmin;
use Piwik\Plugins\LanguagesManager\API as APILanguagesManager;
use Piwik\Plugins\LanguagesManager\LanguagesManager;
use Piwik\Plugins\Login\PasswordVerifier;
use Piwik\Plugins\UsersManager\API as APIUsersManager;
use Piwik\SettingsPiwik;
use Piwik\Site;
use Piwik\Tracker\IgnoreCookie;
use Piwik\Translation\Translator;
use Piwik\Url;
use Piwik\View;
use Piwik\Session\SessionInitializer;
use Piwik\Plugins\CoreAdminHome\Emails\TokenAuthCreatedEmail;
use Piwik\Plugins\CoreAdminHome\Emails\TokenAuthDeletedEmail;

class Controller extends ControllerAdmin
{
    const NONCE_CHANGE_PASSWORD = 'changePasswordNonce';
    const NONCE_ADD_AUTH_TOKEN = 'addAuthTokenNonce';
    const NONCE_DELETE_AUTH_TOKEN = 'deleteAuthTokenNonce';

    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var PasswordVerifier
     */
    private $passwordVerify;

    /**
     * @var Model
     */
    private $userModel;

    public function __construct(Translator $translator, PasswordVerifier $passwordVerify, Model $userModel)
    {
        $this->translator = $translator;
        $this->passwordVerify = $passwordVerify;
        $this->userModel = $userModel;

        parent::__construct();
    }

    /**
     * The "Manage Users and Permissions" Admin UI screen
     */
    public function index()
    {
        Piwik::checkUserIsNotAnonymous();
        Piwik::checkUserHasSomeAdminAccess();
        UsersManager::dieIfUsersAdminIsDisabled();

        $view = new View('@UsersManager/index');

        $IdSitesAdmin = Request::processRequest('SitesManager.getSitesIdWithAdminAccess');
        $idSiteSelected = 1;

        if (count($IdSitesAdmin) > 0) {
            $defaultWebsiteId = $IdSitesAdmin[0];
            $idSiteSelected = $this->idSite ?: $defaultWebsiteId;
        }

        if (!Piwik::isUserHasAdminAccess($idSiteSelected) && count($IdSitesAdmin) > 0) {
            // make sure to show a website where user actually has admin access
            $idSiteSelected = $IdSitesAdmin[0];
        }

        $defaultReportSiteName = Site::getNameFor($idSiteSelected);

        $view->inviteTokenExpiryDays = GeneralConfig::getConfigValue('default_invite_user_token_expiry_days');
        $view->idSiteSelected = $idSiteSelected;
        $view->defaultReportSiteName = $defaultReportSiteName;
        $view->currentUserRole = Piwik::hasUserSuperUserAccess() ? 'superuser' : 'admin';
        $view->accessLevels = [
            ['key' => 'noaccess', 'value' => Piwik::translate('UsersManager_PrivNone'), 'type' => 'role'],
            ['key' => 'view', 'value' => Piwik::translate('UsersManager_PrivView'), 'type' => 'role'],
            ['key' => 'write', 'value' => Piwik::translate('UsersManager_PrivWrite'), 'type' => 'role'],
            ['key' => 'admin', 'value' => Piwik::translate('UsersManager_PrivAdmin'), 'type' => 'role'],
            ['key' => 'superuser', 'value' => Piwik::translate('Installation_SuperUser'), 'type' => 'role', 'disabled' => true],
        ];
        $view->filterAccessLevels = [
            ['key' => '', 'value' => '', 'type' => 'role'], // show all
            ['key' => 'noaccess', 'value' => Piwik::translate('UsersManager_PrivNone'), 'type' => 'role'],
            ['key' => 'some', 'value' => Piwik::translate('UsersManager_AtLeastView'), 'type' => 'role'],
            ['key' => 'view', 'value' => Piwik::translate('UsersManager_PrivView'), 'type' => 'role'],
            ['key' => 'write', 'value' => Piwik::translate('UsersManager_PrivWrite'), 'type' => 'role'],
            ['key' => 'admin', 'value' => Piwik::translate('UsersManager_PrivAdmin'), 'type' => 'role'],
            ['key' => 'superuser', 'value' => Piwik::translate('Installation_SuperUser'), 'type' => 'role'],
        ];

        $view->statusAccessLevels = [
          ['key' => '', 'value' => ''], // show all
          ['key' => 'pending', 'value' => Piwik::translate('UsersManager_Pending')],
          ['key' => 'active', 'value' => Piwik::translate('UsersManager_Active')],
          ['key' => 'expired', 'value' => Piwik::translate('UsersManager_Expired')],
        ];

        $capabilities = Request::processRequest('UsersManager.getAvailableCapabilities', [], []);
        foreach ($capabilities as $capability) {
            $capabilityEntry = [
                'key' => $capability['id'],
                'value' => $capability['category'] . ': ' . $capability['name'],
                'type' => 'capability'
            ];
            $view->accessLevels[] = $capabilityEntry;
            $view->filterAccessLevels[] = $capabilityEntry;
        }

        $this->setBasicVariablesView($view);

        return $view->render();
    }

    /**
     * Returns default date for Piwik reports
     *
     * @param string $user
     * @return string today, yesterday, week, month, year
     */
    protected function getDefaultDateForUser($user)
    {
        return APIUsersManager::getInstance()->getUserPreference(APIUsersManager::PREFERENCE_DEFAULT_REPORT_DATE, $user);
    }

    /**
     * Returns the enabled dates that users can select,
     * in their User Settings page "Report date to load by default"
     *
     * @throws
     * @return array
     */
    protected function getDefaultDates()
    {
        $dates = array(
            'today'      => $this->translator->translate('Intl_Today'),
            'yesterday'  => $this->translator->translate('Intl_Yesterday'),
            'previous7'  => $this->translator->translate('General_PreviousDays', 7),
            'previous30' => $this->translator->translate('General_PreviousDays', 30),
            'last7'      => $this->translator->translate('General_LastDays', 7),
            'last30'     => $this->translator->translate('General_LastDays', 30),
            'week'       => $this->translator->translate('General_CurrentWeek'),
            'month'      => $this->translator->translate('General_CurrentMonth'),
            'year'       => $this->translator->translate('General_CurrentYear'),
        );

        $mappingDatesToPeriods = array(
            'today' => 'day',
            'yesterday' => 'day',
            'previous7' => 'range',
            'previous30' => 'range',
            'last7' => 'range',
            'last30' => 'range',
            'week' => 'week',
            'month' => 'month',
            'year' => 'year',
        );

        // assertion
        if (count($dates) != count($mappingDatesToPeriods)) {
            throw new Exception("some metadata is missing in getDefaultDates()");
        }

        $allowedPeriods = self::getEnabledPeriodsInUI();
        $allowedDates = array_intersect($mappingDatesToPeriods, $allowedPeriods);
        $dates = array_intersect_key($dates, $allowedDates);

        /**
         * Triggered when the list of available dates is requested, for example for the
         * User Settings > Report date to load by default.
         *
         * @param array &$dates Array of (date => translation)
         */
        Piwik::postEvent('UsersManager.getDefaultDates', array(&$dates));

        return $dates;
    }

    /**
     * The "User Settings" admin UI screen view
     */
    public function userSettings()
    {
        Piwik::checkUserIsNotAnonymous();

        $view = new View('@UsersManager/userSettings');

        $userLogin = Piwik::getCurrentUserLogin();
        $user = Request::processRequest('UsersManager.getUser', array('userLogin' => $userLogin));
        $view->userEmail = $user['email'];
        $view->userTokenAuth = Piwik::getCurrentUserTokenAuth();
        $view->ignoreSalt = $this->getIgnoreCookieSalt();
        $view->isUsersAdminEnabled = UsersManager::isUsersAdminEnabled();

        $newsletterSignupOptionKey = NewsletterSignup::NEWSLETTER_SIGNUP_OPTION . $userLogin;
        $view->showNewsletterSignup = Option::get($newsletterSignupOptionKey) === false
                                    && SettingsPiwik::isInternetEnabled();

        $userPreferences = new UserPreferences();
        $defaultReport   = $userPreferences->getDefaultReport();

        if ($defaultReport === false) {
            $defaultReport = $userPreferences->getDefaultWebsiteId();
        }

        $view->defaultReport = $defaultReport;

        if ($defaultReport == 'MultiSites') {

            $defaultSiteId = $userPreferences->getDefaultWebsiteId();
            $reportOptionsValue = $defaultSiteId;

            $view->defaultReportIdSite   = $defaultSiteId;
            $view->defaultReportSiteName = Site::getNameFor($defaultSiteId);
        } else {
            $reportOptionsValue = $defaultReport;
            $view->defaultReportIdSite   = $defaultReport;
            $view->defaultReportSiteName = Site::getNameFor($defaultReport);
        }

        $defaultReportOptions = array();
        if (Plugin\Manager::getInstance()->isPluginActivated('MultiSites')) {
            $defaultReportOptions[] = array('key' => 'MultiSites', 'value' => Piwik::translate('General_AllWebsitesDashboard'));
        }

        $defaultReportOptions[] = array('key' => $reportOptionsValue, 'value' => Piwik::translate('General_DashboardForASpecificWebsite'));

        $view->defaultReportOptions = $defaultReportOptions;
        $view->defaultDate = $this->getDefaultDateForUser($userLogin);
        $view->availableDefaultDates = $this->getDefaultDates();

        $languages = APILanguagesManager::getInstance()->getAvailableLanguageNames();
        $languageOptions = array();
        foreach ($languages as $language) {
            $languageOptions[] = array(
                'key' => $language['code'],
                'value' => $language['name']
            );
        }

        $view->languageOptions = $languageOptions;
        $view->currentLanguageCode = LanguagesManager::getLanguageCodeForCurrentUser();
        $view->currentTimeformat = (int) LanguagesManager::uses12HourClockForCurrentUser();
        $view->ignoreCookieSet = IgnoreCookie::isIgnoreCookieFound();
        $view->piwikHost = Url::getCurrentHost();
        $this->setBasicVariablesView($view);

        $view->timeFormats = array(
            '1' => Piwik::translate('General_12HourClock'),
            '0' => Piwik::translate('General_24HourClock')
        );

        return $view->render();
    }

    /**
     * The "User Security" admin UI screen view
     */
    public function userSecurity()
    {
        Piwik::checkUserIsNotAnonymous();

        $tokens = $this->userModel->getAllNonSystemTokensForLogin(Piwik::getCurrentUserLogin());
        $tokens = array_map(function ($token){
            foreach (['date_created', 'last_used', 'date_expired'] as $key) {
                if (!empty($token[$key])) {
                    $token[$key] = Date::factory($token[$key])->getLocalized(Date::DATE_FORMAT_LONG);
                }
            }

            return $token;
        }, $tokens);
        $hasTokensWithExpireDate = !empty(array_filter(array_column($tokens, 'date_expired')));

        return $this->renderTemplate('userSecurity', array(
            'isUsersAdminEnabled' => UsersManager::isUsersAdminEnabled(),
            'changePasswordNonce' => Nonce::getNonce(self::NONCE_CHANGE_PASSWORD),
            'deleteTokenNonce' => Nonce::getNonce(self::NONCE_DELETE_AUTH_TOKEN),
            'hasTokensWithExpireDate' => $hasTokensWithExpireDate,
            'tokens' => $tokens
        ));
    }

    /**
     * The "User Security" admin UI screen view
     */
    public function deleteToken()
    {
        Piwik::checkUserIsNotAnonymous();

        $idTokenAuth = Common::getRequestVar('idtokenauth', '', 'string');

        if (!empty($idTokenAuth)) {
            $params = array(
                'module' => 'UsersManager',
                'action' => 'deleteToken',
                'idtokenauth' => $idTokenAuth,
                'nonce' => Nonce::getNonce(self::NONCE_DELETE_AUTH_TOKEN)
            );

            if (!$this->passwordVerify->requirePasswordVerifiedRecently($params)) {
                throw new Exception('Not allowed');
            }

            Nonce::checkNonce(self::NONCE_DELETE_AUTH_TOKEN);

            if ($idTokenAuth === 'all') {
                $this->userModel->deleteAllTokensForUser(Piwik::getCurrentUserLogin());

                $notification = new Notification(Piwik::translate('UsersManager_TokensSuccessfullyDeleted'));
                $notification->context = Notification::CONTEXT_SUCCESS;
                Notification\Manager::notify('successdeletetokens', $notification);

                $container = StaticContainer::getContainer();
                $email = $container->make(TokenAuthDeletedEmail::class, array(
                    'login' => Piwik::getCurrentUserLogin(),
                    'emailAddress' => Piwik::getCurrentUserEmail(),
                    'tokenDescription' => '',
                    'all' => true
                ));
                $email->safeSend();
            } elseif (is_numeric($idTokenAuth)) {
                $description = $this->userModel->getUserTokenDescriptionByIdTokenAuth($idTokenAuth, Piwik::getCurrentUserLogin());
                $this->userModel->deleteToken($idTokenAuth, Piwik::getCurrentUserLogin());

                $notification = new Notification(Piwik::translate('UsersManager_TokenSuccessfullyDeleted'));
                $notification->context = Notification::CONTEXT_SUCCESS;
                Notification\Manager::notify('successdeletetoken', $notification);

                $container = StaticContainer::getContainer();
                $email = $container->make(TokenAuthDeletedEmail::class, array(
                    'login' => Piwik::getCurrentUserLogin(),
                    'emailAddress' => Piwik::getCurrentUserEmail(),
                    'tokenDescription' => $description
                ));
                $email->safeSend();
            }
        }

        $this->redirectToIndex('UsersManager', 'userSecurity');
    }

    /**
     * The "User Security" admin UI screen view
     */
    public function addNewToken()
    {
        Piwik::checkUserIsNotAnonymous();

        $params = array('module' => 'UsersManager', 'action' => 'addNewToken');

        if (!$this->passwordVerify->requirePasswordVerifiedRecently($params)) {
            throw new Exception('Not allowed');
        }

        $noDescription = false;

        if (!empty($_POST['description'])) {
            Nonce::checkNonce(self::NONCE_ADD_AUTH_TOKEN);

            $description = Common::getRequestVar('description', '', 'string');
            $login = Piwik::getCurrentUserLogin();

            $generatedToken = $this->userModel->generateRandomTokenAuth();

            $this->userModel->addTokenAuth($login, $generatedToken, $description, Date::now()->getDatetime());

            $container = StaticContainer::getContainer();
            $email = $container->make(TokenAuthCreatedEmail::class, array(
                'login' => Piwik::getCurrentUserLogin(),
                'emailAddress' => Piwik::getCurrentUserEmail(),
                'tokenDescription' => $description
            ));
            $email->safeSend();

            return $this->renderTemplate('addNewTokenSuccess', array('generatedToken' => $generatedToken));
        } elseif (isset($_POST['description'])) {
            $noDescription = true;
        }

        return $this->renderTemplate('addNewToken', array(
           'nonce' => Nonce::getNonce(self::NONCE_ADD_AUTH_TOKEN),
           'noDescription' => $noDescription
        ));
    }

    /**
     * The "Anonymous Settings" admin UI screen view
     */
    public function anonymousSettings()
    {
        Piwik::checkUserHasSuperUserAccess();

        $view = new View('@UsersManager/anonymousSettings');

        $view->availableDefaultDates = $this->getDefaultDates();

        $this->initViewAnonymousUserSettings($view);
        $this->setBasicVariablesView($view);

        return $view->render();
    }

    public function setIgnoreCookie()
    {
        Piwik::checkUserHasSomeViewAccess();
        Piwik::checkUserIsNotAnonymous();

        $salt = Common::getRequestVar('ignoreSalt', false, 'string');
        if ($salt !== $this->getIgnoreCookieSalt()) {
            throw new Exception("Not authorized");
        }

        IgnoreCookie::setIgnoreCookie();
        Piwik::redirectToModule('UsersManager', 'userSettings', array('token_auth' => false));
    }

    /**
     * The Super User can modify Anonymous user settings
     * @param View $view
     */
    protected function initViewAnonymousUserSettings($view)
    {
        if (!Piwik::hasUserSuperUserAccess()) {
            return;
        }

        $userLogin = 'anonymous';

        // Which websites are available to the anonymous users?

        $anonymousSitesAccess = Request::processRequest('UsersManager.getSitesAccessFromUser', array('userLogin' => $userLogin));
        $anonymousSites = array();
        $idSites = array();
        foreach ($anonymousSitesAccess as $info) {
            $idSite = $info['site'];
            $idSites[] = $idSite;

            $site = Request::processRequest('SitesManager.getSiteFromId', array('idSite' => $idSite));
            // Work around manual website deletion
            if (!empty($site)) {
                $anonymousSites[] = array('key' => $idSite, 'value' => Common::unsanitizeInputValue($site['name']));
            }
        }
        $view->anonymousSites = $anonymousSites;

        $anonymousDefaultSite = '';

        // Which report is displayed by default to the anonymous user?
        $anonymousDefaultReport = Request::processRequest('UsersManager.getUserPreference', array('userLogin' => $userLogin, 'preferenceName' => APIUsersManager::PREFERENCE_DEFAULT_REPORT));
        if ($anonymousDefaultReport === false) {
            if (empty($anonymousSites)) {
                $anonymousDefaultReport = Piwik::getLoginPluginName();
            } else {
                // we manually imitate what would happen, in case the anonymous user logs in
                // and is redirected to the first website available to them in the list
                // @see getDefaultWebsiteId()
                $anonymousDefaultReport = '1';
                $anonymousDefaultSite = $anonymousSites[0]['key'];
            }
        }

        if (is_numeric($anonymousDefaultReport)) {
            $anonymousDefaultSite = $anonymousDefaultReport;
            $anonymousDefaultReport = '1'; // a website is selected, we make sure "Dashboard for a specific site" gets pre-selected
        }

        if ((empty($anonymousDefaultSite) || !in_array($anonymousDefaultSite, $idSites)) && !empty($idSites)) {
            $anonymousDefaultSite = $anonymousSites[0]['key'];
        }

        $view->anonymousDefaultReport = $anonymousDefaultReport;
        $view->anonymousDefaultSite = $anonymousDefaultSite;
        $view->anonymousDefaultDate = $this->getDefaultDateForUser($userLogin);

        $view->defaultReportOptions = array(
            array('key' => 'Login', 'value' => Piwik::translate('UsersManager_TheLoginScreen')),
            array('key' => 'MultiSites', 'value' => Piwik::translate('General_AllWebsitesDashboard'), 'disabled' => empty($anonymousSites)),
            array('key' => '1', 'value' => Piwik::translate('General_DashboardForASpecificWebsite')),
        );
    }

    /**
     * Records settings for the anonymous users (default report, default date)
     */
    public function recordAnonymousUserSettings()
    {
        $response = new ResponseBuilder(Common::getRequestVar('format'));
        try {
            Piwik::checkUserHasSuperUserAccess();
            $this->checkTokenInUrl();

            $anonymousDefaultReport = Common::getRequestVar('anonymousDefaultReport');
            $anonymousDefaultDate = Common::getRequestVar('anonymousDefaultDate');
            $userLogin = 'anonymous';
            APIUsersManager::getInstance()->setUserPreference($userLogin,
                APIUsersManager::PREFERENCE_DEFAULT_REPORT,
                $anonymousDefaultReport);
            APIUsersManager::getInstance()->setUserPreference($userLogin,
                APIUsersManager::PREFERENCE_DEFAULT_REPORT_DATE,
                $anonymousDefaultDate);
            $toReturn = $response->getResponse();
        } catch (Exception $e) {
            $toReturn = $response->getResponseException($e);
        }

        return $toReturn;
    }

    /**
     * Records settings from the "User Settings" page
     * @throws Exception
     */
    public function recordUserSettings()
    {
        $response = new ResponseBuilder(Common::getRequestVar('format'));
        try {
            $this->checkTokenInUrl();

            $defaultReport = Common::getRequestVar('defaultReport');
            $defaultDate = Common::getRequestVar('defaultDate');
            $language = Common::getRequestVar('language');
            $timeFormat = Common::getRequestVar('timeformat');
            $userLogin = Piwik::getCurrentUserLogin();

            Piwik::checkUserHasSuperUserAccessOrIsTheUser($userLogin);

            $this->processEmailChange($userLogin);

            LanguagesManager::setLanguageForSession($language);

            Request::processRequest('LanguagesManager.setLanguageForUser', [
                'login' => $userLogin,
                'languageCode' => $language,
            ]);
            Request::processRequest('LanguagesManager.set12HourClockForUser', [
                'login' => $userLogin,
                'use12HourClock' => $timeFormat,
            ]);

            APIUsersManager::getInstance()->setUserPreference($userLogin,
                APIUsersManager::PREFERENCE_DEFAULT_REPORT,
                $defaultReport);
            APIUsersManager::getInstance()->setUserPreference($userLogin,
                APIUsersManager::PREFERENCE_DEFAULT_REPORT_DATE,
                $defaultDate);
            $toReturn = $response->getResponse();
        } catch (Exception $e) {
            $toReturn = $response->getResponseException($e);
        }

        return $toReturn;
    }


    /**
     * Records settings from the "User Settings" page
     * @throws Exception
     */
    public function recordPasswordChange()
    {
        $userLogin = Piwik::getCurrentUserLogin();

        Piwik::checkUserHasSuperUserAccessOrIsTheUser($userLogin);
        Nonce::checkNonce(self::NONCE_CHANGE_PASSWORD);

        $this->processPasswordChange($userLogin);

        $notification = new Notification(Piwik::translate('CoreAdminHome_SettingsSaveSuccess'));
        $notification->context = Notification::CONTEXT_SUCCESS;
        Notification\Manager::notify('successpass', $notification);
        $this->redirectToIndex('UsersManager',  'userSecurity');
    }

    private function noAdminAccessToWebsite($idSiteSelected, $defaultReportSiteName, $message)
    {
        $view = new View('@UsersManager/noWebsiteAdminAccess');

        $view->idSiteSelected = $idSiteSelected;
        $view->defaultReportSiteName = $defaultReportSiteName;
        $view->message = $message;
        $this->setBasicVariablesView($view);

        return $view->render();
    }

    private function processEmailChange($userLogin)
    {
        if (!UsersManager::isUsersAdminEnabled()) {
            return;
        }

        if (!Url::isValidHost()) {
            throw new Exception("Cannot change email with untrusted hostname!");
        }

        $email = Common::getRequestVar('email');
        $passwordCurrent = Common::getRequestvar('passwordConfirmation', false);

        // UI disables password change on invalid host, but check here anyway
        Request::processRequest('UsersManager.updateUser', [
            'userLogin' => $userLogin,
            'email' => $email,
            'passwordConfirmation' => $passwordCurrent
        ], $default = []);
    }

    private function processPasswordChange($userLogin)
    {
        if (!UsersManager::isUsersAdminEnabled()) {
            return;
        }

        if (!Url::isValidHost()) {
            // UI disables password change on invalid host, but check here anyway
            throw new Exception("Cannot change password with untrusted hostname!");
        }

        $newPassword = Common::getRequestvar('password', false);
        $passwordBis = Common::getRequestvar('passwordBis', false);
        $passwordCurrent = Common::getRequestvar('passwordConfirmation', false);

        if ($newPassword !== $passwordBis) {
            throw new Exception($this->translator->translate('Login_PasswordsDoNotMatch'));
        }

        Request::processRequest('UsersManager.updateUser', [
            'userLogin' => $userLogin,
            'password' => $newPassword,
            'passwordConfirmation' => $passwordCurrent
        ], $default = []);

        // logs the user in with the new password
        $newPassword = Common::unsanitizeInputValue($newPassword);
        $sessionInitializer = new SessionInitializer();
        $auth = StaticContainer::get('Piwik\Auth');
        $auth->setTokenAuth(null); // ensure authenticated through password
        $auth->setLogin($userLogin);
        $auth->setPassword($newPassword);
        $sessionInitializer->initSession($auth);
    }

    /**
     * @return string
     */
    private function getIgnoreCookieSalt()
    {
        return md5(SettingsPiwik::getSalt());
    }
}
