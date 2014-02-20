<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Installation;

use Exception;
use Piwik\Access;
use Piwik\API\Request;
use Piwik\AssetManager;
use Piwik\Common;
use Piwik\Config;
use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\Db\Adapter;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Filechecks;
use Piwik\Filesystem;
use Piwik\Http;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\CoreUpdater\CoreUpdater;
use Piwik\Plugins\LanguagesManager\LanguagesManager;
use Piwik\Plugins\SitesManager\API as APISitesManager;
use Piwik\Plugins\UserCountry\LocationProvider;
use Piwik\Plugins\UsersManager\API as APIUsersManager;
use Piwik\ProxyHeaders;
use Piwik\Session\SessionNamespace;
use Piwik\SettingsServer;
use Piwik\Updater;
use Piwik\UpdaterErrorException;
use Piwik\Url;
use Piwik\Version;
use Zend_Db_Adapter_Exception;

/**
 * Installation controller
 *
 */
class Controller extends \Piwik\Plugin\ControllerAdmin
{
    // public so plugins can add/delete installation steps
    public $steps = array(
        'welcome'           => 'Installation_Welcome',
        'systemCheck'       => 'Installation_SystemCheck',
        'databaseSetup'     => 'Installation_DatabaseSetup',
        'databaseCheck'     => 'Installation_DatabaseCheck',
        'tablesCreation'    => 'Installation_Tables',
        'generalSetup'      => 'Installation_SuperUser',
        'firstWebsiteSetup' => 'Installation_SetupWebsite',
        'trackingCode'      => 'General_JsTrackingTag',
        'finished'          => 'Installation_Congratulations',
    );

    protected $session;

    public function __construct()
    {
        $this->session = new SessionNamespace('Installation');
        if (!isset($this->session->currentStepDone)) {
            $this->session->currentStepDone = '';
            $this->session->skipThisStep = array();
        }
    }

    protected static function initServerFilesForSecurity()
    {
        if (SettingsServer::isIIS()) {
            ServerFilesGenerator::createWebConfigFiles();
        } else {
            ServerFilesGenerator::createHtAccessFiles();
        }
        ServerFilesGenerator::createWebRootFiles();
    }

    /**
     * Get installation steps
     *
     * @return array installation steps
     */
    public function getInstallationSteps()
    {
        return $this->steps;
    }

    /**
     * Get default action (first installation step)
     *
     * @return string function name
     */
    function getDefaultAction()
    {
        $steps = array_keys($this->steps);
        return $steps[0];
    }

    /**
     * Installation Step 1: Welcome
     */
    function welcome($message = false)
    {
        // Delete merged js/css files to force regenerations based on updated activated plugin list
        Filesystem::deleteAllCacheOnUpdate();

        $view = new View(
            '@Installation/welcome',
            $this->getInstallationSteps(),
            __FUNCTION__
        );

        $view->newInstall = !$this->isFinishedInstallation();
        $view->errorMessage = $message;
        $this->skipThisStep(__FUNCTION__);
        $view->showNextStep = $view->newInstall;
        $this->session->currentStepDone = __FUNCTION__;
        return $view->render();
    }

    /**
     * Installation Step 2: System Check
     */
    function systemCheck()
    {
        $this->checkPreviousStepIsValid(__FUNCTION__);

        $view = new View(
            '@Installation/systemCheck',
            $this->getInstallationSteps(),
            __FUNCTION__
        );
        $this->skipThisStep(__FUNCTION__);

        $view->duringInstall = true;

        $this->setupSystemCheckView($view);
        $this->session->general_infos = $view->infos['general_infos'];
        $this->session->general_infos['salt'] = Common::generateUniqId();

        // make sure DB sessions are used if the filesystem is NFS
        if ($view->infos['is_nfs']) {
            $this->session->general_infos['session_save_handler'] = 'dbtable';
        }

        $view->showNextStep = !$view->problemWithSomeDirectories
            && $view->infos['phpVersion_ok']
            && count($view->infos['adapters'])
            && !count($view->infos['missing_extensions'])
            && !count($view->infos['missing_functions']);
        // On the system check page, if all is green, display Next link at the top
        $view->showNextStepAtTop = $view->showNextStep;

        $this->session->currentStepDone = __FUNCTION__;

        return $view->render();
    }

    /**
     * Installation Step 3: Database Set-up
     * @throws Exception|Zend_Db_Adapter_Exception
     */
    function databaseSetup()
    {
        $this->checkPreviousStepIsValid(__FUNCTION__);

        // case the user hits the back button
        $this->session->skipThisStep = array(
            'firstWebsiteSetup' => false,
            'trackingCode'      => false,
        );

        $this->skipThisStep(__FUNCTION__);

        $view = new View(
            '@Installation/databaseSetup',
            $this->getInstallationSteps(),
            __FUNCTION__
        );

        $view->showNextStep = false;

        $form = new FormDatabaseSetup();

        if ($form->validate()) {
            try {
                $dbInfos = $form->createDatabaseObject();
                $this->session->databaseCreated = true;

                DbHelper::checkDatabaseVersion();
                $this->session->databaseVersionOk = true;

                $this->createConfigFileIfNeeded($dbInfos);

                $this->redirectToNextStep(__FUNCTION__);
            } catch (Exception $e) {
                $view->errorMessage = Common::sanitizeInputValue($e->getMessage());
            }
        }
        $view->addForm($form);

        return $view->render();
    }

    /**
     * Installation Step 4: Database Check
     */
    function databaseCheck()
    {
        $this->checkPreviousStepIsValid(__FUNCTION__);
        $view = new View(
            '@Installation/databaseCheck',
            $this->getInstallationSteps(),
            __FUNCTION__
        );

        $error = false;
        $this->skipThisStep(__FUNCTION__);

        if (isset($this->session->databaseVersionOk)
            && $this->session->databaseVersionOk === true
        ) {
            $view->databaseVersionOk = true;
        } else {
            $error = true;
        }

        if (isset($this->session->databaseCreated)
            && $this->session->databaseCreated === true
        ) {
            $view->databaseName = Config::getInstance()->database['dbname'];
            $view->databaseCreated = true;
        } else {
            $error = true;
        }

        $db = Db::get();

        try {
            $db->checkClientVersion();
        } catch (Exception $e) {
            $view->clientVersionWarning = $e->getMessage();
            $error = true;
        }

        if (!DbHelper::isDatabaseConnectionUTF8()) {
            Config::getInstance()->database['charset'] = 'utf8';
            Config::getInstance()->forceSave();
        }

        $view->showNextStep = true;
        $this->session->currentStepDone = __FUNCTION__;

        if ($error === false) {
            $this->redirectToNextStep(__FUNCTION__);
        }
        return $view->render();
    }

    /**
     * Installation Step 5: Table Creation
     */
    function tablesCreation()
    {
        $this->checkPreviousStepIsValid(__FUNCTION__);

        $view = new View(
            '@Installation/tablesCreation',
            $this->getInstallationSteps(),
            __FUNCTION__
        );
        $this->skipThisStep(__FUNCTION__);

        if (Common::getRequestVar('deleteTables', 0, 'int') == 1) {
            DbHelper::dropTables();
            $view->existingTablesDeleted = true;

            // when the user decides to drop the tables then we dont skip the next steps anymore
            // workaround ZF-1743
            $tmp = $this->session->skipThisStep;
            $tmp['firstWebsiteSetup'] = false;
            $tmp['trackingCode'] = false;
            $this->session->skipThisStep = $tmp;
        }

        $tablesInstalled = DbHelper::getTablesInstalled();
        $view->tablesInstalled = '';

        if (count($tablesInstalled) > 0) {

            // we have existing tables
            $view->tablesInstalled     = implode(', ', $tablesInstalled);
            $view->someTablesInstalled = true;

            Access::getInstance();
            Piwik::setUserHasSuperUserAccess();
            if ($this->hasEnoughTablesToReuseDb($tablesInstalled) &&
                count(APISitesManager::getInstance()->getAllSitesId()) > 0 &&
                count(APIUsersManager::getInstance()->getUsers()) > 0
            ) {
                $view->showReuseExistingTables = true;
                // when the user reuses the same tables we skip the website creation step
                // workaround ZF-1743
                $tmp = $this->session->skipThisStep;
                $tmp['firstWebsiteSetup'] = true;
                $tmp['trackingCode'] = true;
                $this->session->skipThisStep = $tmp;
            }
        } else {
            DbHelper::createTables();
            DbHelper::createAnonymousUser();

            Updater::recordComponentSuccessfullyUpdated('core', Version::VERSION);
            $view->tablesCreated = true;
            $view->showNextStep = true;
        }

        $this->session->currentStepDone = __FUNCTION__;
        return $view->render();
    }

    function reuseTables()
    {
        $this->checkPreviousStepIsValid(__FUNCTION__);

        $steps = $this->getInstallationSteps();
        $steps['tablesCreation'] = 'Installation_ReusingTables';

        $view = new View(
            '@Installation/reuseTables',
            $steps,
            'tablesCreation'
        );

        Access::getInstance();
        Piwik::setUserHasSuperUserAccess();

        $updater = new Updater();
        $componentsWithUpdateFile = CoreUpdater::getComponentUpdates($updater);

        if (empty($componentsWithUpdateFile)) {
            $this->session->currentStepDone = 'tablesCreation';
            $this->redirectToNextStep('tablesCreation');
            return '';
        }

        $oldVersion = Option::get('version_core');

        $result = CoreUpdater::updateComponents($updater, $componentsWithUpdateFile);

        $view->coreError       = $result['coreError'];
        $view->warningMessages = $result['warnings'];
        $view->errorMessages   = $result['errors'];
        $view->deactivatedPlugins = $result['deactivatedPlugins'];
        $view->currentVersion  = Version::VERSION;
        $view->oldVersion  = $oldVersion;
        $view->showNextStep = true;

        $this->session->currentStepDone = 'tablesCreation';

        return $view->render();
    }

    /**
     * Installation Step 6: General Set-up (superuser login/password/email and subscriptions)
     */
    function generalSetup()
    {
        $this->checkPreviousStepIsValid(__FUNCTION__);

        $view = new View(
            '@Installation/generalSetup',
            $this->getInstallationSteps(),
            __FUNCTION__
        );
        $this->skipThisStep(__FUNCTION__);

        $form = new FormGeneralSetup();

        if ($form->validate()) {

            try {
                $this->createSuperUser($form->getSubmitValue('login'),
                                       $form->getSubmitValue('password'),
                                       $form->getSubmitValue('email'));

                $url  = Config::getInstance()->General['api_service_url'];
                $url .= '/1.0/subscribeNewsletter/';
                $params = array(
                    'email'     => $form->getSubmitValue('email'),
                    'security'  => $form->getSubmitValue('subscribe_newsletter_security'),
                    'community' => $form->getSubmitValue('subscribe_newsletter_community'),
                    'url'       => Url::getCurrentUrlWithoutQueryString(),
                );
                if ($params['security'] == '1'
                    || $params['community'] == '1'
                ) {
                    if (!isset($params['security'])) {
                        $params['security'] = '0';
                    }
                    if (!isset($params['community'])) {
                        $params['community'] = '0';
                    }
                    $url .= '?' . http_build_query($params, '', '&');
                    try {
                        Http::sendHttpRequest($url, $timeout = 2);
                    } catch (Exception $e) {
                        // e.g., disable_functions = fsockopen; allow_url_open = Off
                    }
                }
                $this->redirectToNextStep(__FUNCTION__);

            } catch (Exception $e) {
                $view->errorMessage = $e->getMessage();
            }
        }

        $view->addForm($form);

        return $view->render();
    }

    /**
     * Installation Step 7: Configure first web-site
     */
    public function firstWebsiteSetup()
    {
        $this->checkPreviousStepIsValid(__FUNCTION__);

        $view = new View(
            '@Installation/firstWebsiteSetup',
            $this->getInstallationSteps(),
            __FUNCTION__
        );
        $this->skipThisStep(__FUNCTION__);

        $form = new FormFirstWebsiteSetup();
        if (!isset($this->session->generalSetupSuccessMessage)) {
            $view->displayGeneralSetupSuccess = true;
            $this->session->generalSetupSuccessMessage = true;
        }

        $this->initObjectsToCallAPI();
        if ($form->validate()) {
            $name = Common::unsanitizeInputValue($form->getSubmitValue('siteName'));
            $url = Common::unsanitizeInputValue($form->getSubmitValue('url'));
            $ecommerce = (int)$form->getSubmitValue('ecommerce');

            try {
                $result = APISitesManager::getInstance()->addSite($name, $url, $ecommerce);
                $this->session->site_idSite = $result;
                $this->session->site_name = $name;
                $this->session->site_url = $url;

                $this->redirectToNextStep(__FUNCTION__);
            } catch (Exception $e) {
                $view->errorMessage = $e->getMessage();
            }
        }
        $view->addForm($form);
        return $view->render();
    }

    /**
     * Installation Step 8: Display JavaScript tracking code
     */
    public function trackingCode()
    {
        $this->checkPreviousStepIsValid(__FUNCTION__);

        $view = new View(
            '@Installation/trackingCode',
            $this->getInstallationSteps(),
            __FUNCTION__
        );
        $this->skipThisStep(__FUNCTION__);

        if (!isset($this->session->firstWebsiteSetupSuccessMessage)) {
            $view->displayfirstWebsiteSetupSuccess = true;
            $this->session->firstWebsiteSetupSuccessMessage = true;
        }
        $siteName = $this->session->site_name;
        $idSite = $this->session->site_idSite;

        // Load the Tracking code and help text from the SitesManager
        $viewTrackingHelp = new \Piwik\View('@SitesManager/_displayJavascriptCode');
        $viewTrackingHelp->displaySiteName = $siteName;
        $viewTrackingHelp->jsTag = Piwik::getJavascriptCode($idSite, Url::getCurrentUrlWithoutFileName());
        $viewTrackingHelp->idSite = $idSite;
        $viewTrackingHelp->piwikUrl = Url::getCurrentUrlWithoutFileName();

        $view->trackingHelp = $viewTrackingHelp->render();
        $view->displaySiteName = $siteName;

        $view->showNextStep = true;

        $this->session->currentStepDone = __FUNCTION__;
        return $view->render();
    }

    /**
     * Installation Step 9: Finished!
     */
    public function finished()
    {
        $this->checkPreviousStepIsValid(__FUNCTION__);

        $view = new View(
            '@Installation/finished',
            $this->getInstallationSteps(),
            __FUNCTION__
        );
        $this->skipThisStep(__FUNCTION__);

        if (!$this->isFinishedInstallation()) {
            $this->addTrustedHosts();
            $this->markInstallationAsCompleted();
        }

        $view->showNextStep = false;

        $this->session->currentStepDone = __FUNCTION__;
        $output = $view->render();

        $this->session->unsetAll();

        return $output;
    }

    /**
     * This controller action renders an admin tab that runs the installation
     * system check, so people can see if there are any issues w/ their running
     * Piwik installation.
     *
     * This admin tab is only viewable by the Super User.
     */
    public function systemCheckPage()
    {
        Piwik::checkUserHasSuperUserAccess();

        $view = new View(
            '@Installation/systemCheckPage',
            $this->getInstallationSteps(),
            __FUNCTION__
        );
        $this->setBasicVariablesView($view);

        $view->duringInstall = false;

        $this->setupSystemCheckView($view);

        $infos = $view->infos;
        $infos['extra'] = self::performAdminPageOnlySystemCheck();
        $view->infos = $infos;

        return $view->render();
    }

    /**
     * Instantiate access and log objects
     */
    protected function initObjectsToCallAPI()
    {
        Piwik::setUserHasSuperUserAccess();
    }

    /**
     * Write configuration file from session-store
     */
    protected function createConfigFileIfNeeded($dbInfos)
    {
        $config = Config::getInstance();

        try {
            // expect exception since config.ini.php doesn't exist yet
            $config->checkLocalConfigFound();

        } catch (Exception $e) {

            if (!empty($this->session->general_infos)) {
                $config->General = $this->session->general_infos;
            }
        }

        $config->General['installation_in_progress'] = 1;
        $config->database = $dbInfos;
        $config->forceSave();

        unset($this->session->general_infos);
    }

    /**
     * Write configuration file from session-store
     */
    protected function markInstallationAsCompleted()
    {
        $config = Config::getInstance();
        unset($config->General['installation_in_progress']);
        $config->forceSave();
    }

    /**
     * Save language selection in session-store
     */
    public function saveLanguage()
    {
        $language = Common::getRequestVar('language');
        LanguagesManager::setLanguageForSession($language);
        Url::redirectToReferrer();
    }

    /**
     * Prints out the CSS for installer/updater
     *
     * During installation and update process, we load a minimal Less file.
     * At this point Piwik may not be setup yet to write files in tmp/assets/
     * so in this case we compile and return the string on every request.
     */
    public function getBaseCss()
    {
        @header('Content-Type: text/css');
        return AssetManager::getInstance()->getCompiledBaseCss()->getContent();
    }

    /**
     * The previous step is valid if it is either
     * - any step before (OK to go back)
     * - the current step (case when validating a form)
     * If step is invalid, then exit.
     *
     * @param string $currentStep Current step
     */
    protected function checkPreviousStepIsValid($currentStep)
    {
        $error = false;

        if (empty($this->session->currentStepDone)) {
            $error = true;
        } else if ($currentStep == 'finished' && $this->session->currentStepDone == 'finished') {
            // ok to refresh this page or use language selector
        } else if ($currentStep == 'reuseTables' && in_array($this->session->currentStepDone, array('tablesCreation', 'reuseTables'))) {
            // this is ok, we cannot add 'reuseTables' to steps as it would appear in the menu otherwise
        } else {
            if ($this->isFinishedInstallation()) {
                $error = true;
            }

            $steps = array_keys($this->steps);

            // the currentStep
            $currentStepId = array_search($currentStep, $steps);

            // the step before
            $previousStepId = array_search($this->session->currentStepDone, $steps);

            // not OK if currentStepId > previous+1
            if ($currentStepId > $previousStepId + 1) {
                $error = true;
            }
        }
        if ($error) {
            \Piwik\Plugins\Login\Controller::clearSession();
            $message = Piwik::translate('Installation_ErrorInvalidState',
                array('<br /><strong>',
                      '</strong>',
                      '<a href=\'' . Common::sanitizeInputValue(Url::getCurrentUrlWithoutFileName()) . '\'>',
                      '</a>')
            );
            Piwik::exitWithErrorMessage($message);
        }
    }

    /**
     * Redirect to next step
     *
     * @param string $currentStep Current step
     * @return void
     */
    protected function redirectToNextStep($currentStep)
    {
        $steps = array_keys($this->steps);
        $this->session->currentStepDone = $currentStep;
        $nextStep = $steps[1 + array_search($currentStep, $steps)];
        Piwik::redirectToModule('Installation', $nextStep);
    }

    /**
     * Skip this step (typically to mark the current function as completed)
     *
     * @param string $step function name
     */
    protected function skipThisStep($step)
    {
        $skipThisStep = $this->session->skipThisStep;
        if (isset($skipThisStep[$step]) && $skipThisStep[$step]) {
            $this->redirectToNextStep($step);
        }
    }

    /**
     * Extract host from URL
     *
     * @param string $url URL
     *
     * @return string|false
     */
    protected function extractHost($url)
    {
        $urlParts = parse_url($url);
        if (isset($urlParts['host']) && strlen($host = $urlParts['host'])) {
            return $host;
        }

        return false;
    }

    /**
     * Add trusted hosts
     */
    protected function addTrustedHosts()
    {
        $trustedHosts = array();

        // extract host from the request header
        if (($host = $this->extractHost('http://' . Url::getHost())) !== false) {
            $trustedHosts[] = $host;
        }

        // extract host from first web site
        if (($host = $this->extractHost(urldecode($this->session->site_url))) !== false) {
            $trustedHosts[] = $host;
        }

        $trustedHosts = array_unique($trustedHosts);
        if (count($trustedHosts)) {

            $general = Config::getInstance()->General;
            $general['trusted_hosts'] = $trustedHosts;
            Config::getInstance()->General = $general;

            Config::getInstance()->forceSave();
        }
    }

    /**
     * Get system information
     */
    public static function getSystemInformation()
    {
        global $piwik_minimumPHPVersion;
        $minimumMemoryLimit = Config::getInstance()->General['minimum_memory_limit'];

        $infos = array();

        $infos['general_infos'] = array();



        $directoriesToCheck = array(
                                '/tmp/',
                                '/tmp/assets/',
                                '/tmp/cache/',
                                '/tmp/climulti/',
                                '/tmp/latest/',
                                '/tmp/logs/',
                                '/tmp/sessions/',
                                '/tmp/tcpdf/',
                                '/tmp/templates_c/',
        );

        if (!DbHelper::isInstalled()) {
            // at install, need /config to be writable (so we can create config.ini.php)
            $directoriesToCheck[] = '/config/';
        }

        $infos['directories'] = Filechecks::checkDirectoriesWritable($directoriesToCheck);

        $infos['can_auto_update'] = Filechecks::canAutoUpdate();

        self::initServerFilesForSecurity();

        $infos['phpVersion_minimum'] = $piwik_minimumPHPVersion;
        $infos['phpVersion'] = PHP_VERSION;
        $infos['phpVersion_ok'] = version_compare($piwik_minimumPHPVersion, $infos['phpVersion']) === -1;

        // critical errors
        $extensions = @get_loaded_extensions();
        $needed_extensions = array(
            'zlib',
            'SPL',
            'iconv',
            'Reflection',
        );
        $infos['needed_extensions'] = $needed_extensions;
        $infos['missing_extensions'] = array();
        foreach ($needed_extensions as $needed_extension) {
            if (!in_array($needed_extension, $extensions)) {
                $infos['missing_extensions'][] = $needed_extension;
            }
        }

        $infos['pdo_ok'] = false;
        if (in_array('PDO', $extensions)) {
            $infos['pdo_ok'] = true;
        }

        $infos['adapters'] = Adapter::getAdapters();

        $needed_functions = array(
            'debug_backtrace',
            'create_function',
            'eval',
            'gzcompress',
            'gzuncompress',
            'pack',
        );
        $infos['needed_functions'] = $needed_functions;
        $infos['missing_functions'] = array();
        foreach ($needed_functions as $needed_function) {
            if (!self::functionExists($needed_function)) {
                $infos['missing_functions'][] = $needed_function;
            }
        }

        // warnings
        $desired_extensions = array(
            'json',
            'libxml',
            'dom',
            'SimpleXML',
        );
        $infos['desired_extensions'] = $desired_extensions;
        $infos['missing_desired_extensions'] = array();
        foreach ($desired_extensions as $desired_extension) {
            if (!in_array($desired_extension, $extensions)) {
                $infos['missing_desired_extensions'][] = $desired_extension;
            }
        }

        $desired_functions = array(
            'set_time_limit',
            'mail',
            'parse_ini_file',
            'glob',
        );
        $infos['desired_functions'] = $desired_functions;
        $infos['missing_desired_functions'] = array();
        foreach ($desired_functions as $desired_function) {
            if (!self::functionExists($desired_function)) {
                $infos['missing_desired_functions'][] = $desired_function;
            }
        }

        $infos['openurl'] = Http::getTransportMethod();

        $infos['gd_ok'] = SettingsServer::isGdExtensionEnabled();

        $infos['hasMbstring'] = false;
        $infos['multibyte_ok'] = true;
        if (function_exists('mb_internal_encoding')) {
            $infos['hasMbstring'] = true;
            if (((int)ini_get('mbstring.func_overload')) != 0) {
                $infos['multibyte_ok'] = false;
            }
        }

        $serverSoftware = isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : '';
        $infos['serverVersion'] = addslashes($serverSoftware);
        $infos['serverOs'] = @php_uname();
        $infos['serverTime'] = date('H:i:s');

        $infos['registerGlobals_ok'] = ini_get('register_globals') == 0;
        $infos['memoryMinimum'] = $minimumMemoryLimit;

        $infos['memory_ok'] = true;
        $infos['memoryCurrent'] = '';

        $raised = SettingsServer::raiseMemoryLimitIfNecessary();
        if (($memoryValue = SettingsServer::getMemoryLimitValue()) > 0) {
            $infos['memoryCurrent'] = $memoryValue . 'M';
            $infos['memory_ok'] = $memoryValue >= $minimumMemoryLimit;
        }

        $infos['isWindows'] = SettingsServer::isWindows();

        $integrityInfo = Filechecks::getFileIntegrityInformation();
        $infos['integrity'] = $integrityInfo[0];

        $infos['integrityErrorMessages'] = array();
        if (isset($integrityInfo[1])) {
            if ($infos['integrity'] == false) {
                $infos['integrityErrorMessages'][] = Piwik::translate('General_FileIntegrityWarningExplanation');
            }
            $infos['integrityErrorMessages'] = array_merge($infos['integrityErrorMessages'], array_slice($integrityInfo, 1));
        }

        $infos['timezone'] = SettingsServer::isTimezoneSupportEnabled();

        $infos['tracker_status'] = Common::getRequestVar('trackerStatus', 0, 'int');

        $infos['protocol'] = ProxyHeaders::getProtocolInformation();
        if (!\Piwik\ProxyHttp::isHttps() && $infos['protocol'] !== null) {
            $infos['general_infos']['assume_secure_protocol'] = '1';
        }
        if (count($headers = ProxyHeaders::getProxyClientHeaders()) > 0) {
            $infos['general_infos']['proxy_client_headers'] = $headers;
        }
        if (count($headers = ProxyHeaders::getProxyHostHeaders()) > 0) {
            $infos['general_infos']['proxy_host_headers'] = $headers;
        }

        // check if filesystem is NFS, if it is file based sessions won't work properly
        $infos['is_nfs'] = Filesystem::checkIfFileSystemIsNFS();
        $infos = self::enrichSystemChecks($infos);

        return $infos;
    }


    /**
     * @param $infos
     * @return mixed
     */
    public static function enrichSystemChecks($infos)
    {
        // determine whether there are any errors/warnings from the checks done above
        $infos['has_errors'] = false;
        $infos['has_warnings'] = false;
        if (in_array(0, $infos['directories']) // if a directory is not writable
            || !$infos['phpVersion_ok']
            || !empty($infos['missing_extensions'])
            || empty($infos['adapters'])
            || !empty($infos['missing_functions'])
        ) {
            $infos['has_errors'] = true;
        }

        if (   !empty($infos['missing_desired_extensions'])
            || !$infos['gd_ok']
            || !$infos['multibyte_ok']
            || !$infos['registerGlobals_ok']
            || !$infos['memory_ok']
            || !empty($infos['integrityErrorMessages'])
            || !$infos['timezone'] // if timezone support isn't available
            || $infos['tracker_status'] != 0
            || $infos['is_nfs']
        ) {
            $infos['has_warnings'] = true;
        }
        return $infos;
    }

    /**
     * Test if function exists.  Also handles case where function is disabled via Suhosin.
     *
     * @param string $functionName Function name
     * @return bool True if function exists (not disabled); False otherwise.
     */
    public static function functionExists($functionName)
    {
        // eval() is a language construct
        if ($functionName == 'eval') {
            // does not check suhosin.executor.eval.whitelist (or blacklist)
            if (extension_loaded('suhosin')) {
                return @ini_get("suhosin.executor.disable_eval") != "1";
            }
            return true;
        }

        $exists = function_exists($functionName);
        if (extension_loaded('suhosin')) {
            $blacklist = @ini_get("suhosin.executor.func.blacklist");
            if (!empty($blacklist)) {
                $blacklistFunctions = array_map('strtolower', array_map('trim', explode(',', $blacklist)));
                return $exists && !in_array($functionName, $blacklistFunctions);
            }
        }
        return $exists;
    }

    /**
     * Utility function, sets up a view that will display system check info.
     *
     * @param View $view
     */
    private function setupSystemCheckView($view)
    {
        $view->infos = self::getSystemInformation();

        $view->helpMessages = array(
            'zlib'            => 'Installation_SystemCheckZlibHelp',
            'SPL'             => 'Installation_SystemCheckSplHelp',
            'iconv'           => 'Installation_SystemCheckIconvHelp',
            'Reflection'      => 'Required extension that is built in PHP, see http://www.php.net/manual/en/book.reflection.php',
            'json'            => 'Installation_SystemCheckWarnJsonHelp',
            'libxml'          => 'Installation_SystemCheckWarnLibXmlHelp',
            'dom'             => 'Installation_SystemCheckWarnDomHelp',
            'SimpleXML'       => 'Installation_SystemCheckWarnSimpleXMLHelp',
            'set_time_limit'  => 'Installation_SystemCheckTimeLimitHelp',
            'mail'            => 'Installation_SystemCheckMailHelp',
            'parse_ini_file'  => 'Installation_SystemCheckParseIniFileHelp',
            'glob'            => 'Installation_SystemCheckGlobHelp',
            'debug_backtrace' => 'Installation_SystemCheckDebugBacktraceHelp',
            'create_function' => 'Installation_SystemCheckCreateFunctionHelp',
            'eval'            => 'Installation_SystemCheckEvalHelp',
            'gzcompress'      => 'Installation_SystemCheckGzcompressHelp',
            'gzuncompress'    => 'Installation_SystemCheckGzuncompressHelp',
            'pack'            => 'Installation_SystemCheckPackHelp',
        );

        $view->problemWithSomeDirectories = (false !== array_search(false, $view->infos['directories']));
    }

    /**
     * Performs extra system checks for the 'System Check' admin page. These
     * checks are not performed during Installation.
     *
     * The following checks are performed:
     *  - Check for whether LOAD DATA INFILE can be used. The result of the check
     *    is stored in $result['load_data_infile_available']. The error message is
     *    stored in $result['load_data_infile_error'].
     *
     * - Check whether geo location is setup correctly
     *
     * @return array
     */
    public static function performAdminPageOnlySystemCheck()
    {
        $result = array();
        self::checkLoadDataInfile($result);
        self::checkGeolocation($result);
        return $result;
    }


    private static function checkGeolocation(&$result)
    {
        $currentProviderId = LocationProvider::getCurrentProviderId();
        $allProviders = LocationProvider::getAllProviderInfo();
        $isRecommendedProvider = in_array($currentProviderId, array( LocationProvider\GeoIp\Php::ID, $currentProviderId == LocationProvider\GeoIp\Pecl::ID));
        $isProviderInstalled = ($allProviders[$currentProviderId]['status'] == LocationProvider::INSTALLED);

        $result['geolocation_using_non_recommended'] = $result['geolocation_ok'] = false;
        if ($isRecommendedProvider && $isProviderInstalled) {
            $result['geolocation_ok'] = true;
        } elseif ($isProviderInstalled) {
            $result['geolocation_using_non_recommended'] = true;
        }
    }

    private static function checkLoadDataInfile(&$result)
    {
        // check if LOAD DATA INFILE works
        $optionTable = Common::prefixTable('option');
        $testOptionNames = array('test_system_check1', 'test_system_check2');

        $result['load_data_infile_available'] = false;
        try {
            $result['load_data_infile_available'] = Db\BatchInsert::tableInsertBatch(
                $optionTable,
                array('option_name', 'option_value'),
                array(
                    array($testOptionNames[0], '1'),
                    array($testOptionNames[1], '2'),
                ),
                $throwException = true
            );
        } catch (Exception $ex) {
            $result['load_data_infile_error'] = str_replace("\n", "<br/>", $ex->getMessage());
        }

        // delete the temporary rows that were created
        Db::exec("DELETE FROM `$optionTable` WHERE option_name IN ('" . implode("','", $testOptionNames) . "')");
    }

    private function createSuperUser($login, $password, $email)
    {
        $this->initObjectsToCallAPI();

        $api = APIUsersManager::getInstance();
        $api->addUser($login, $password, $email);

        $this->initObjectsToCallAPI();
        $api->setSuperUserAccess($login, true);
    }

    private function isFinishedInstallation()
    {
        $isConfigFileFound = file_exists(Config::getLocalConfigPath());

        if (!$isConfigFileFound) {
            return false;
        }

        $general = Config::getInstance()->General;

        $isInstallationInProgress = false;
        if (array_key_exists('installation_in_progress', $general)) {
            $isInstallationInProgress = (bool) $general['installation_in_progress'];
        }

        return !$isInstallationInProgress;
    }

    private function hasEnoughTablesToReuseDb($tablesInstalled)
    {
        if (empty($tablesInstalled) || !is_array($tablesInstalled)) {
            return false;
        }

        $archiveTables       = ArchiveTableCreator::getTablesArchivesInstalled();
        $baseTablesInstalled = count($tablesInstalled) - count($archiveTables);
        $minimumCountPiwikTables = 12;

        return $baseTablesInstalled >= $minimumCountPiwikTables;
    }

}
