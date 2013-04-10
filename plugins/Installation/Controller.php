<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_Installation
 */

/**
 * Installation controller
 *
 * @package Piwik_Installation
 */
class Piwik_Installation_Controller extends Piwik_Controller_Admin
{
    // public so plugins can add/delete installation steps
    public $steps = array(
        'welcome'               => 'Installation_Welcome',
        'systemCheck'           => 'Installation_SystemCheck',
        'databaseSetup'         => 'Installation_DatabaseSetup',
        'databaseCheck'         => 'Installation_DatabaseCheck',
        'tablesCreation'        => 'Installation_Tables',
        'generalSetup'          => 'Installation_SuperUser',
        'firstWebsiteSetup'     => 'Installation_SetupWebsite',
        'displayJavascriptCode' => 'Installation_JsTag',
        'finished'              => 'Installation_Congratulations',
    );

    protected $pathView = 'Installation/templates/';

    protected $session;

    public function __construct()
    {
        $this->session = new Piwik_Session_Namespace('Piwik_Installation');
        if (!isset($this->session->currentStepDone)) {
            $this->session->currentStepDone = '';
            $this->session->skipThisStep = array();
        }

        Piwik_PostEvent('InstallationController.construct', $this);
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
        Piwik::deleteAllCacheOnUpdate();

        $view = new Piwik_Installation_View(
            $this->pathView . 'welcome.tpl',
            $this->getInstallationSteps(),
            __FUNCTION__
        );
        $view->newInstall = !file_exists(Piwik_Config::getLocalConfigPath());
        $view->errorMessage = $message;
        $this->skipThisStep(__FUNCTION__);
        $view->showNextStep = $view->newInstall;
        $this->session->currentStepDone = __FUNCTION__;
        echo $view->render();
    }

    /**
     * Installation Step 2: System Check
     */
    function systemCheck()
    {
        $this->checkPreviousStepIsValid(__FUNCTION__);

        $view = new Piwik_Installation_View(
            $this->pathView . 'systemCheck.tpl',
            $this->getInstallationSteps(),
            __FUNCTION__
        );
        $this->skipThisStep(__FUNCTION__);

        $view->duringInstall = true;

        $this->setupSystemCheckView($view);
        $this->session->general_infos = $view->infos['general_infos'];

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

        echo $view->render();
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
            'firstWebsiteSetup'     => false,
            'displayJavascriptCode' => false,
        );

        $view = new Piwik_Installation_View(
            $this->pathView . 'databaseSetup.tpl',
            $this->getInstallationSteps(),
            __FUNCTION__
        );
        $this->skipThisStep(__FUNCTION__);

        $view->showNextStep = false;

        $form = new Piwik_Installation_FormDatabaseSetup();

        if ($form->validate()) {
            try {
                $dbInfos = $form->createDatabaseObject();
                $this->session->databaseCreated = true;

                Piwik::checkDatabaseVersion();
                $this->session->databaseVersionOk = true;

                $this->session->db_infos = $dbInfos;
                $this->redirectToNextStep(__FUNCTION__);
            } catch (Exception $e) {
                $view->errorMessage = Piwik_Common::sanitizeInputValue($e->getMessage());
            }
        }
        $view->addForm($form);

        echo $view->render();
    }

    /**
     * Installation Step 4: Database Check
     */
    function databaseCheck()
    {
        $this->checkPreviousStepIsValid(__FUNCTION__);
        $view = new Piwik_Installation_View(
            $this->pathView . 'databaseCheck.tpl',
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
            $dbInfos = $this->session->db_infos;
            $view->databaseName = $dbInfos['dbname'];
            $view->databaseCreated = true;
        } else {
            $error = true;
        }

        $this->createDbFromSessionInformation();
        $db = Zend_Registry::get('db');

        try {
            $db->checkClientVersion();
        } catch (Exception $e) {
            $view->clientVersionWarning = $e->getMessage();
            $error = true;
        }

        if (!Piwik::isDatabaseConnectionUTF8()) {
            $dbInfos = $this->session->db_infos;
            $dbInfos['charset'] = 'utf8';
            $this->session->db_infos = $dbInfos;
        }

        $view->showNextStep = true;
        $this->session->currentStepDone = __FUNCTION__;

        if ($error === false) {
            $this->redirectToNextStep(__FUNCTION__);
        }
        echo $view->render();
    }

    /**
     * Installation Step 5: Table Creation
     */
    function tablesCreation()
    {
        $this->checkPreviousStepIsValid(__FUNCTION__);

        $view = new Piwik_Installation_View(
            $this->pathView . 'tablesCreation.tpl',
            $this->getInstallationSteps(),
            __FUNCTION__
        );
        $this->skipThisStep(__FUNCTION__);
        $this->createDbFromSessionInformation();

        if (Piwik_Common::getRequestVar('deleteTables', 0, 'int') == 1) {
            Piwik::dropTables();
            $view->existingTablesDeleted = true;

            // when the user decides to drop the tables then we dont skip the next steps anymore
            // workaround ZF-1743
            $tmp = $this->session->skipThisStep;
            $tmp['firstWebsiteSetup'] = false;
            $tmp['displayJavascriptCode'] = false;
            $this->session->skipThisStep = $tmp;
        }

        $tablesInstalled = Piwik::getTablesInstalled();
        $tablesToInstall = Piwik::getTablesNames();
        $view->tablesInstalled = '';
        if (count($tablesInstalled) > 0) {
            // we have existing tables
            $view->tablesInstalled = implode(', ', $tablesInstalled);
            $view->someTablesInstalled = true;

            $minimumCountPiwikTables = 17;
            $baseTablesInstalled = preg_grep('/archive_numeric|archive_blob/', $tablesInstalled, PREG_GREP_INVERT);

            Piwik::createAccessObject();
            Piwik::setUserIsSuperUser();
            if (count($baseTablesInstalled) >= $minimumCountPiwikTables &&
                count(Piwik_SitesManager_API::getInstance()->getAllSitesId()) > 0 &&
                count(Piwik_UsersManager_API::getInstance()->getUsers()) > 0
            ) {
                $view->showReuseExistingTables = true;
                // when the user reuses the same tables we skip the website creation step
                // workaround ZF-1743
                $tmp = $this->session->skipThisStep;
                $tmp['firstWebsiteSetup'] = true;
                $tmp['displayJavascriptCode'] = true;
                $this->session->skipThisStep = $tmp;
            }
        } else {
            Piwik::createTables();
            Piwik::createAnonymousUser();

            $updater = new Piwik_Updater();
            $updater->recordComponentSuccessfullyUpdated('core', Piwik_Version::VERSION);
            $view->tablesCreated = true;
            $view->showNextStep = true;
        }

        $this->session->currentStepDone = __FUNCTION__;
        echo $view->render();
    }

    /**
     * Installation Step 6: General Set-up (superuser login/password/email and subscriptions)
     */
    function generalSetup()
    {
        $this->checkPreviousStepIsValid(__FUNCTION__);

        $view = new Piwik_Installation_View(
            $this->pathView . 'generalSetup.tpl',
            $this->getInstallationSteps(),
            __FUNCTION__
        );
        $this->skipThisStep(__FUNCTION__);

        $form = new Piwik_Installation_FormGeneralSetup();

        if ($form->validate()) {
            $superUserInfos = array(
                'login'    => $form->getSubmitValue('login'),
                'password' => md5($form->getSubmitValue('password')),
                'email'    => $form->getSubmitValue('email'),
                'salt'     => Piwik_Common::generateUniqId(),
            );

            $this->session->superuser_infos = $superUserInfos;

            $url = Piwik_Config::getInstance()->General['api_service_url'];
            $url .= '/1.0/subscribeNewsletter/';
            $params = array(
                'email'     => $form->getSubmitValue('email'),
                'security'  => $form->getSubmitValue('subscribe_newsletter_security'),
                'community' => $form->getSubmitValue('subscribe_newsletter_community'),
                'url'       => Piwik_Url::getCurrentUrlWithoutQueryString(),
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
                    Piwik_Http::sendHttpRequest($url, $timeout = 2);
                } catch (Exception $e) {
                    // e.g., disable_functions = fsockopen; allow_url_open = Off
                }
            }
            $this->redirectToNextStep(__FUNCTION__);
        }
        $view->addForm($form);

        echo $view->render();
    }

    /**
     * Installation Step 7: Configure first web-site
     */
    public function firstWebsiteSetup()
    {
        $this->checkPreviousStepIsValid(__FUNCTION__);

        $view = new Piwik_Installation_View(
            $this->pathView . 'firstWebsiteSetup.tpl',
            $this->getInstallationSteps(),
            __FUNCTION__
        );
        $this->skipThisStep(__FUNCTION__);

        $form = new Piwik_Installation_FormFirstWebsiteSetup();
        if (!isset($this->session->generalSetupSuccessMessage)) {
            $view->displayGeneralSetupSuccess = true;
            $this->session->generalSetupSuccessMessage = true;
        }

        $this->initObjectsToCallAPI();
        if ($form->validate()) {
            $name = urlencode($form->getSubmitValue('siteName'));
            $url = urlencode($form->getSubmitValue('url'));
            $ecommerce = (int)$form->getSubmitValue('ecommerce');

            $request = new Piwik_API_Request("
							method=SitesManager.addSite
							&siteName=$name
							&urls=$url
							&ecommerce=$ecommerce
							&format=original
						");

            try {
                $result = $request->process();
                $this->session->site_idSite = $result;
                $this->session->site_name = $name;
                $this->session->site_url = $url;

                $this->redirectToNextStep(__FUNCTION__);
            } catch (Exception $e) {
                $view->errorMessage = $e->getMessage();
            }

        }
        $view->addForm($form);
        echo $view->render();
    }

    /**
     * Installation Step 8: Display JavaScript tracking code
     */
    public function displayJavascriptCode()
    {
        $this->checkPreviousStepIsValid(__FUNCTION__);

        $view = new Piwik_Installation_View(
            $this->pathView . 'displayJavascriptCode.tpl',
            $this->getInstallationSteps(),
            __FUNCTION__
        );
        $this->skipThisStep(__FUNCTION__);

        if (!isset($this->session->firstWebsiteSetupSuccessMessage)) {
            $view->displayfirstWebsiteSetupSuccess = true;
            $this->session->firstWebsiteSetupSuccessMessage = true;
        }
        $siteName = $this->session->site_name;
        $siteName = Piwik_Common::sanitizeInputValue(urldecode($siteName));
        $idSite = $this->session->site_idSite;

        // Load the Tracking code and help text from the SitesManager
        $viewTrackingHelp = new Piwik_View('SitesManager/templates/DisplayJavascriptCode.tpl');
        $viewTrackingHelp->displaySiteName = $siteName;
        $viewTrackingHelp->jsTag = Piwik::getJavascriptCode($idSite, Piwik_Url::getCurrentUrlWithoutFileName());
        $viewTrackingHelp->idSite = $idSite;
        $viewTrackingHelp->piwikUrl = Piwik_Url::getCurrentUrlWithoutFileName();

        // Assign the html output to a smarty variable
        $view->trackingHelp = $viewTrackingHelp->render();
        $view->displaySiteName = $siteName;

        $view->showNextStep = true;

        $this->session->currentStepDone = __FUNCTION__;
        echo $view->render();
    }

    /**
     * Installation Step 9: Finished!
     */
    public function finished()
    {
        $this->checkPreviousStepIsValid(__FUNCTION__);

        $view = new Piwik_Installation_View(
            $this->pathView . 'finished.tpl',
            $this->getInstallationSteps(),
            __FUNCTION__
        );
        $this->skipThisStep(__FUNCTION__);

        if (!file_exists(Piwik_Config::getLocalConfigPath())) {
//			$this->addTrustedHosts();
            $this->writeConfigFileFromSession();
        }

        $view->showNextStep = false;

        $this->session->currentStepDone = __FUNCTION__;
        echo $view->render();

        $this->session->unsetAll();
    }

    /**
     * This controller action renders an admin tab that runs the installation
     * system check, so people can see if there are any issues w/ their running
     * Piwik installation.
     *
     * This admin tab is only viewable by the super user.
     */
    public function systemCheckPage()
    {
        Piwik::checkUserIsSuperUser();

        $view = Piwik_View::factory('systemCheckPage');
        $this->setBasicVariablesView($view);
        $view->menu = Piwik_GetAdminMenu();

        $view->duringInstall = false;

        $this->setupSystemCheckView($view);

        $infos = $view->infos;
        $infos['extra'] = self::performAdminPageOnlySystemCheck();
        $view->infos = $infos;

        echo $view->render();
    }

    /**
     * Instantiate access and log objects
     */
    protected function initObjectsToCallAPI()
    {
        // connect to the database using the DB infos currently in the session
        $this->createDbFromSessionInformation();

        Piwik::createAccessObject();
        Piwik::setUserIsSuperUser();
        Piwik::createLogObject();
    }

    /**
     * Create database connection from session-store
     */
    protected function createDbFromSessionInformation()
    {
        $dbInfos = $this->session->db_infos;
        Piwik_Config::getInstance()->database = $dbInfos;
        Piwik::createDatabaseObject($dbInfos);
    }

    /**
     * Write configuration file from session-store
     */
    protected function writeConfigFileFromSession()
    {
        if (!isset($this->session->superuser_infos)
            || !isset($this->session->db_infos)
        ) {
            return;
        }

        $config = Piwik_Config::getInstance();
        try {
            // expect exception since config.ini.php doesn't exist yet
            $config->init();
        } catch (Exception $e) {
            $config->superuser = $this->session->superuser_infos;
            $config->database = $this->session->db_infos;

            if (!empty($this->session->general_infos)) {
                $config->General = $this->session->general_infos;
            }

            $config->forceSave();
        }

        unset($this->session->superuser_infos);
        unset($this->session->db_infos);
        unset($this->session->general_infos);
    }

    /**
     * Save language selection in session-store
     */
    public function saveLanguage()
    {
        $language = Piwik_Common::getRequestVar('language');
        Piwik_LanguagesManager::setLanguageForSession($language);
        Piwik_Url::redirectToReferer();
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
        } else {
            if (file_exists(Piwik_Config::getLocalConfigPath())) {
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
            Piwik_Login_Controller::clearSession();
            $message = Piwik_Translate('Installation_ErrorInvalidState',
                array('<br /><b>',
                      '</b>',
                      '<a href=\'' . Piwik_Common::sanitizeInputValue(Piwik_Url::getCurrentUrlWithoutFileName()) . '\'>',
                      '</a>')
            );
            Piwik::exitWithErrorMessage($message);
        }
    }

    /**
     * Redirect to next step
     *
     * @param string Current step
     * @return none
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
     * @param string function name
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
        if (($host = $this->extractHost('http://' . Piwik_Url::getHost())) !== false) {
            $trustedHosts[] = $host;
        }

        // extract host from first web site
        if (($host = $this->extractHost(urldecode($this->session->site_url))) !== false) {
            $trustedHosts[] = $host;
        }

        $trustedHosts = array_unique($trustedHosts);
        if (count($trustedHosts)) {
            $this->session->general_infos['trusted_hosts'] = $trustedHosts;
        }
    }

    /**
     * Get system information
     */
    public static function getSystemInformation()
    {
        global $piwik_minimumPHPVersion;
        $minimumMemoryLimit = Piwik_Config::getInstance()->General['minimum_memory_limit'];

        $infos = array();

        $infos['general_infos'] = array();
        $infos['directories'] = Piwik::checkDirectoriesWritable();
        $infos['can_auto_update'] = Piwik::canAutoUpdate();

        if (Piwik_Common::isIIS()) {
            Piwik::createWebConfigFiles();
        } else {
            Piwik::createHtAccessFiles();
        }
        Piwik::createWebRootFiles();

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

        $infos['adapters'] = Piwik_Db_Adapter::getAdapters();

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

        $infos['openurl'] = Piwik_Http::getTransportMethod();

        $infos['gd_ok'] = Piwik::isGdExtensionEnabled();

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

        $raised = Piwik::raiseMemoryLimitIfNecessary();
        if (($memoryValue = Piwik::getMemoryLimitValue()) > 0) {
            $infos['memoryCurrent'] = $memoryValue . 'M';
            $infos['memory_ok'] = $memoryValue >= $minimumMemoryLimit;
        }

        $infos['isWindows'] = Piwik_Common::isWindows();

        $integrityInfo = Piwik::getFileIntegrityInformation();
        $infos['integrity'] = $integrityInfo[0];

        $infos['integrityErrorMessages'] = array();
        if (isset($integrityInfo[1])) {
            if ($infos['integrity'] == false) {
                $infos['integrityErrorMessages'][] = '<b>' . Piwik_Translate('General_FileIntegrityWarningExplanation') . '</b>';
            }
            $infos['integrityErrorMessages'] = array_merge($infos['integrityErrorMessages'], array_slice($integrityInfo, 1));
        }

        $infos['timezone'] = Piwik::isTimezoneSupportEnabled();

        $infos['tracker_status'] = Piwik_Common::getRequestVar('trackerStatus', 0, 'int');

        $infos['protocol'] = Piwik_ProxyHeaders::getProtocolInformation();
        if (!Piwik::isHttps() && $infos['protocol'] !== null) {
            $infos['general_infos']['assume_secure_protocol'] = '1';
        }
        if (count($headers = Piwik_ProxyHeaders::getProxyClientHeaders()) > 0) {
            $infos['general_infos']['proxy_client_headers'] = $headers;
        }
        if (count($headers = Piwik_ProxyHeaders::getProxyHostHeaders()) > 0) {
            $infos['general_infos']['proxy_host_headers'] = $headers;
        }

        // check if filesystem is NFS, if it is file based sessions won't work properly
        $infos['is_nfs'] = Piwik::checkIfFileSystemIsNFS();

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

        if (!$infos['can_auto_update']
            || !empty($infos['missing_desired_extensions'])
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
     * @param Piwik_View $view
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
     * @return array
     */
    public static function performAdminPageOnlySystemCheck()
    {
        $result = array();

        // check if LOAD DATA INFILE works
        $optionTable = Piwik_Common::prefixTable('option');
        $testOptionNames = array('test_system_check1', 'test_system_check2');

        $result['load_data_infile_available'] = false;
        try {
            $result['load_data_infile_available'] = Piwik::tableInsertBatch(
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
        Piwik_Exec("DELETE FROM `$optionTable` WHERE option_name IN ('" . implode("','", $testOptionNames) . "')");

        return $result;
    }
}
