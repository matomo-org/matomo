<?php

namespace Piwik;

use Piwik\Access;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Db\Adapter;
use Piwik\DbHelper;
use Piwik\Plugins\SitesManager\API as APISitesManager;
use Piwik\Plugins\UsersManager\API as APIUsersManager;
use Piwik\Plugins\UsersManager\NewsletterSignup;
use Piwik\ProxyHeaders;
use Piwik\Updater;
use Piwik\Url;
use Piwik\Version;
use Zend_Db_Adapter_Exception;

//Warning: - Don't know how plugin are installed (not done by installer
//           actually). Some magics when I created installer.
//
// require : https://github.com/matomo-org/matomo/pull/15691

/*
 * Manage the Matomo server installation. Contain
 * all basic function to setup database, user, and
 * site to track.
 *
 * (?Could be a parent class to create custom
 * installation process?)
*/

class MatomoInstaller{
    /*
     * Perform the entire installation of Matomo.
     * Use user-defined settings.
     *
     * @settings is expected to be an array with following
     * variables.
     *
     *  - dbname
     *  - port (optionnal)
     *  - dbusername
     *  - dbpassword
     *  - dbhost (optionnal?)
     *  - tables_prefix (optionnal)
     *  - adapter (optionnal)
     *  - type (optionnal)
     *  - adminusername
     *  - adminpassword
     *  - email
     *  - subscribe_newsletter_piwikorg (optionnal)
     *  - subscribe_newsletter_professionalservices (optionnal)
     *  - name
     *  - url
     *  - ecommerce : (optionnal)
    */
    public static function headlessInstall($settings)
    {
        if (self::systemCheck())
        {
            //Raise Error because DigntosicService report in invalid.
            //What should I do in case of warning or error ?
        }
        
        self::createDatabaseObject($settings);
        self::tablesCreation();
        self::setupSuperUser($settings);
        self::firstWebsiteSetup($settings);
    }

    /*
     * Check if an headless installation is expected.
     * (Heritance should be related to it)
     */
    public static function isHeadlessInstall()
    {
        $installSettings = Config::getInstance()->MatomoInstall;
        return !empty($installSettings);
    }

    /*
     * Use settings from MatomoInstall section in
     * the config.ini.php file and perform installation.
     */
    public static function installFromConfig()
    {
        //Use settings defined in config.ini.php
        //in MatomoInstall section.
        $settings = Config::getInstance()->MatomoInstall;
        self::headlessInstall($settings);

        //Remove installation config when finished
        Config::getInstance()->MatomoInstall = array();
        Config::getInstance()->forceSave();

        //Redirect to matomo homepage
        Url::redirectToUrl('index.php');
    }

    /**
     * Installation Step 2: System Check
     *
     * Control if the system is ready for installation,
     * run Plugins\Diagnostics to get state.
     * 
     * @view View (optionnal), view to store DiagnosticReport
    */
    //S: Should I perform the system check ?
    //In : https://github.com/digitalist-se/extratools/blob/master/Lib/Install.php
    // no system check seem to be performed.
    public static function systemCheck($view=null)
    {
        //Why could delete conf be useful ?? Should I handle
        //already created config ? + delete conf on failure.
        // ! Having an error if I remove it.
        // Multiple entrence for installation, must know where plug
        // the install.
        self::deleteConfigFileIfNeeded();
        // Do not use dependency injection because this service requires a lot of sub-services across plugins
        /** @var DiagnosticService $diagnosticService */
        $diagnosticService = StaticContainer::get('Piwik\Plugins\Diagnostics\DiagnosticService');
        $diagnosticReport = $diagnosticService->runDiagnostics();

        //Store the diagnostic report in the view
        // !! Should remove Installation plugin mecanism
        //Must think about the way to handle error here
        if (!is_null($view))
        {
            $view->diagnosticReport = $diagnosticReport;
            $view->showNextStep = !$diagnosticReport->hasErrors();

            // On the system check page, if all is green, display Next link at the top
            $view->showNextStepAtTop = $view->showNextStep && !$diagnosticReport->hasWarnings();
        }
        //HEADLESS: DO STUFF WITH errors
        return (!$diagnosticReport->hasErrors());
    }

    /**
     * Installation Step 3: Database Set-up
     * @throws Exception|Zend_Db_Adapter_Exception
     */
    public static function createDatabaseObject($settings)
    {
        if (empty($settings['dbname']))
        {
            throw new Exception("No database name");
        }

        //Set some default value for database setup.
        if (is_null($settings['type']))
            $settings['type'] = Config::getInstance()->database['type'];

        //!! MUST ADD Adapter::getRecommendedApater
        if (is_null($settings['adapter']))
            $settings['adapter'] = Adapter::getRecommendedApater();

        if (is_null($settings['port']))
        {
            $port = Adapter::getDefaultPortForAdapter($settings['adapter']);
            $settings['port'] = $port;
        }

        //DEFAULT TABLES_PREFIX could be define in global.ini.php
        //(Could table be without prefix ?)
        if (is_null($settings['tables_prefix']))
            $settings['tables_prefix'] = "matomo_";

        //Prepare db settings.
        $dbInfos = array(
            'host'          => $settings['dbhost'],
            'username'      => $settings['dbusername'],
            'password'      => $settings['dbpassword'],
            'dbname'        => $settings['dbname'],
            'tables_prefix' => $settings['tables_prefix'],
            'adapter'       => $settings['adapter'],
            'port'          => $settings['port'],
            'schema'        => Config::getInstance()->database['schema'],
            'type'          => $settings['type'],
            'enable_ssl'    => false
        );

        if (($portIndex = strpos($dbInfos['host'], '/')) !== false) {
            // unix_socket=/path/sock.n
            $dbInfos['port'] = substr($dbInfos['host'], $portIndex);
            $dbInfos['host'] = '';
        } else if (($portIndex = strpos($dbInfos['host'], ':')) !== false) {
            // host:port
            $dbInfos['port'] = substr($dbInfos['host'], $portIndex + 1);
            $dbInfos['host'] = substr($dbInfos['host'], 0, $portIndex);
        }

        try {
            @Db::createDatabaseObject($dbInfos);
        } catch (Zend_Db_Adapter_Exception $e) {
            $db = Adapter::factory($adapter, $dbInfos, $connect = false);

            // database not found, we try to create  it
            if ($db->isErrNo($e, '1049')) {
                $dbInfosConnectOnly = $dbInfos;
                $dbInfosConnectOnly['dbname'] = null;
                @Db::createDatabaseObject($dbInfosConnectOnly);
                @DbHelper::createDatabase($dbInfos['dbname']);

                /* // select the newly created database */
                @Db::createDatabaseObject($dbInfos);
            } else {
                throw $e;
            }
        }

        @DbHelper::checkDatabaseVersion();

        @Db::get()->checkClientVersion();
        self::createConfigFile($dbInfos);
        return $dbInfos;
    }
    
    /**
     * Installation Step 4: Table Creation
     */
    public static function tablesCreation()
    {
        //Could have already created tables ?
        DbHelper::createTables();
        DbHelper::createAnonymousUser();
        DbHelper::recordInstallVersion();

        self::updateComponents();

        Updater::recordComponentSuccessfullyUpdated('core', Version::VERSION);
    }

    /**
     * Installation Step 5: General Set-up (superuser login/password/email and subscriptions)
     */
    public static function setupSuperUser($settings)
    {
        // Can throw exception

        //Skip super user creation if one already exist
        $superUserAlreadyExists = Access::doAsSuperUser(function () {
            return count(APIUsersManager::getInstance()->getUsersHavingSuperUserAccess()) > 0;
        });

        if ($superUserAlreadyExists) {
            return ;
        }

        self::createSuperUser($settings['adminusername'],
                              $settings['adminpassword'],
                              $settings['email']);


        //Subscribe matomo newsletters
        NewsletterSignup::signupForNewsletter(
            $settings['adminusername'],
            $settings['email'],
            $settings['subscribe_newsletter_piwikorg'],
            $settings['subscribe_newsletter_professionalservices']
        );
    }

    /**
     * Installation Step 6: Configure first web-site
     */
    public static function firstWebsiteSetup($settings)
    {
        $name = $settings['name'];
        $url = $settings['url'];
        $ecommerce = $settings['ecommerce'];

        $result = Access::doAsSuperUser(function () use ($name, $url, $ecommerce) {
            return APISitesManager::getInstance()->addSite($name, $url, $ecommerce);
        });

        $params = array(
            'site_idSite'   => $result,
            'site_name'     => urlencode($name)
        );
        self::addTrustedHosts($settings['url']);
        return $params;
    }

    //Unable to remove it, getting error otherwise.
    //It was probably coming from the location where I called the Installer
    //WARNING: remove the config.ini.php file on installation failure
    protected static function deleteConfigFileIfNeeded()
    {
        $config = Config::getInstance();
        if ($config->existsLocalConfig()) {
            $config->deleteLocalConfig();
        }
    }

    protected static function getParam($name)
    {
        return Common::getRequestVar($name, false, 'string');
    }

    /*
     * Create a new user and grant him Super User acess.
    */
    public static function createSuperUser($login, $password, $email)
    {
        Access::doAsSuperUser(function () use ($login, $password, $email) {
            $api = APIUsersManager::getInstance();
            $api->addUser($login, $password, $email);

            APIUsersManager::$SET_SUPERUSER_ACCESS_REQUIRE_PASSWORD_CONFIRMATION = false;
            $api->setSuperUserAccess($login, true);
        });
    }

    /**
     * Write configuration file from session-store
     */
    private static function createConfigFile($dbInfos)
    {
        $config = Config::getInstance();

        // make sure DB sessions are used if the filesystem is NFS
        if (count($headers = ProxyHeaders::getProxyClientHeaders()) > 0) {
            $config->General['proxy_client_headers'] = $headers;
        }
        if (count($headers = ProxyHeaders::getProxyHostHeaders()) > 0) {
            $config->General['proxy_host_headers'] = $headers;
        }

        if (Common::getRequestVar('clientProtocol', 'http', 'string') == 'https') {
            $protocol = 'https';
        } else {
            $protocol = ProxyHeaders::getProtocolInformation();
        }

        if (!empty($protocol)
            && !\Piwik\ProxyHttp::isHttps()) {
            $config->General['assume_secure_protocol'] = '1';
        }

        $config->General['salt'] = Common::generateUniqId();

        $config->database = $dbInfos;
        if (!DbHelper::isDatabaseConnectionUTF8()) {
            $config->database['charset'] = 'utf8';
        }

        $config->forceSave();
    }

    /**
     * @return array|bool
     */
    private static function updateComponents()
    {
        Access::getInstance();

        return Access::doAsSuperUser(function () {
            $updater = new Updater();
            $componentsWithUpdateFile = $updater->getComponentUpdates();

            if (empty($componentsWithUpdateFile)) {
                return false;
            }
            $result = $updater->updateComponents($componentsWithUpdateFile);
            return $result;
        });
    }

    /*
     * Add trusted host to general settings.
    */
    private static function addTrustedHosts($siteUrl)
    {
        $trustedHosts = array();

        // extract host from the request header
        if (($host = self::extractHost('http://' . Url::getHost())) !== false) {
            $trustedHosts[] = $host;
        }

        // extract host from first web site
        if (($host = self::extractHost(urldecode($siteUrl))) !== false) {
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
     * Extract host from URL
     *
     * @param string $url URL
     *
     * @return string|false
     */
    private static function extractHost($url)
    {
        $urlParts = parse_url($url);
        if (isset($urlParts['host']) && strlen($host = $urlParts['host'])) {
            return $host;
        }
        return false;
    }
}
