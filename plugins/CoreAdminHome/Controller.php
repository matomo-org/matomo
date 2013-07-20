<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package Piwik_CoreAdminHome
 */
use Piwik\Config;
use Piwik\Piwik;
use Piwik\Common;
use Piwik\Site;

/**
 *
 * @package Piwik_CoreAdminHome
 */
class Piwik_CoreAdminHome_Controller extends Piwik_Controller_Admin
{
    const LOGO_HEIGHT = 300;
    const LOGO_SMALL_HEIGHT = 100;

    public function index()
    {
        return $this->redirectToIndex('UsersManager', 'userSettings');
    }

    public function generalSettings()
    {
        Piwik::checkUserHasSomeAdminAccess();
        $view = new Piwik_View('@CoreAdminHome/generalSettings');

        if (Piwik::isUserIsSuperUser()) {
            $enableBrowserTriggerArchiving = Piwik_ArchiveProcessor_Rules::isBrowserTriggerEnabled();
            $todayArchiveTimeToLive = Piwik_ArchiveProcessor_Rules::getTodayArchiveTimeToLive();
            $showWarningCron = false;
            if (!$enableBrowserTriggerArchiving
                && $todayArchiveTimeToLive < 3600
            ) {
                $showWarningCron = true;
            }
            $view->showWarningCron = $showWarningCron;
            $view->todayArchiveTimeToLive = $todayArchiveTimeToLive;
            $view->enableBrowserTriggerArchiving = $enableBrowserTriggerArchiving;

            $this->displayWarningIfConfigFileNotWritable($view);

            $config = Config::getInstance();

            $debug = $config->Debug;
            $view->enableBetaReleaseCheck = $debug['allow_upgrades_to_beta'];

            $view->mail = $config->mail;

            $view->branding = $config->branding;

            $directoryWritable = is_writable(PIWIK_DOCUMENT_ROOT . '/misc/user/');
            $logoFilesWriteable = is_writeable(PIWIK_DOCUMENT_ROOT . '/misc/user/logo.png')
                && is_writeable(PIWIK_DOCUMENT_ROOT . '/misc/user/logo.svg')
                && is_writeable(PIWIK_DOCUMENT_ROOT . '/misc/user/logo-header.png');;
            $view->logosWriteable = ($logoFilesWriteable || $directoryWritable) && ini_get('file_uploads') == 1;

            $trustedHosts = array();
            if (isset($config->General['trusted_hosts'])) {
                $trustedHosts = $config->General['trusted_hosts'];
            }
            $view->trustedHosts = $trustedHosts;
        }

        $view->language = Piwik_LanguagesManager::getLanguageCodeForCurrentUser();
        $this->setBasicVariablesView($view);
        echo $view->render();
    }

    public function setGeneralSettings()
    {
        Piwik::checkUserIsSuperUser();
        $response = new Piwik_API_ResponseBuilder(Common::getRequestVar('format'));
        try {
            $this->checkTokenInUrl();
            $enableBrowserTriggerArchiving = Common::getRequestVar('enableBrowserTriggerArchiving');
            $todayArchiveTimeToLive = Common::getRequestVar('todayArchiveTimeToLive');

            Piwik_ArchiveProcessor_Rules::setBrowserTriggerArchiving((bool)$enableBrowserTriggerArchiving);
            Piwik_ArchiveProcessor_Rules::setTodayArchiveTimeToLive($todayArchiveTimeToLive);

            // Update email settings
            $mail = array();
            $mail['transport'] = (Common::getRequestVar('mailUseSmtp') == '1') ? 'smtp' : '';
            $mail['port'] = Common::getRequestVar('mailPort', '');
            $mail['host'] = Common::unsanitizeInputValue(Common::getRequestVar('mailHost', ''));
            $mail['type'] = Common::getRequestVar('mailType', '');
            $mail['username'] = Common::unsanitizeInputValue(Common::getRequestVar('mailUsername', ''));
            $mail['password'] = Common::unsanitizeInputValue(Common::getRequestVar('mailPassword', ''));
            $mail['encryption'] = Common::getRequestVar('mailEncryption', '');

            $config = Config::getInstance();
            $config->mail = $mail;

            // update branding settings
            $branding = $config->branding;
            $branding['use_custom_logo'] = Common::getRequestVar('useCustomLogo', '0');
            $config->branding = $branding;

            // update beta channel setting
            $debug = $config->Debug;
            $debug['allow_upgrades_to_beta'] = Common::getRequestVar('enableBetaReleaseCheck', '0', 'int');
            $config->Debug = $debug;
            // update trusted host settings
            $trustedHosts = Common::getRequestVar('trustedHosts', false, 'json');
            if ($trustedHosts !== false) {
                Piwik_Url::saveTrustedHostnameInConfig($trustedHosts);
            }

            $config->forceSave();

            $toReturn = $response->getResponse();
        } catch (Exception $e) {
            $toReturn = $response->getResponseException($e);
        }
        echo $toReturn;
    }

    /**
     * Renders and echo's an admin page that lets users generate custom JavaScript
     * tracking code and custom image tracker links.
     */
    public function trackingCodeGenerator()
    {
        $view = new Piwik_View('@CoreAdminHome/trackingCodeGenerator');
        $this->setBasicVariablesView($view);
        $view->topMenu = Piwik_GetTopMenu();

        $viewableIdSites = Piwik_SitesManager_API::getInstance()->getSitesIdWithAtLeastViewAccess();

        $defaultIdSite = reset($viewableIdSites);
        $view->idSite = Common::getRequestVar('idSite', $defaultIdSite, 'int');

        $view->defaultReportSiteName = Site::getNameFor($view->idSite);
        $view->defaultSiteRevenue = Piwik::getCurrency($view->idSite);

        $allUrls = Piwik_SitesManager_API::getInstance()->getSiteUrlsFromId($view->idSite);
        if (isset($allUrls[1])) {
            $aliasUrl = $allUrls[1];
        } else {
            $aliasUrl = 'x.domain.com';
        }
        $view->defaultReportSiteAlias = $aliasUrl;

        $mainUrl = Site::getMainUrlFor($view->idSite);
        $view->defaultReportSiteDomain = @parse_url($mainUrl, PHP_URL_HOST);

        // get currencies for each viewable site
        $view->currencySymbols = Piwik_SitesManager_API::getInstance()->getCurrencySymbols();

        $view->serverSideDoNotTrackEnabled = Piwik_PrivacyManager_Controller::isDntSupported();

        echo $view->render();
    }

    /**
     * Shows the "Track Visits" checkbox.
     */
    public function optOut()
    {
        $trackVisits = !Piwik_Tracker_IgnoreCookie::isIgnoreCookieFound();

        $nonce = Common::getRequestVar('nonce', false);
        $language = Common::getRequestVar('language', '');
        if ($nonce !== false && Piwik_Nonce::verifyNonce('Piwik_OptOut', $nonce)) {
            Piwik_Nonce::discardNonce('Piwik_OptOut');
            Piwik_Tracker_IgnoreCookie::setIgnoreCookie();
            $trackVisits = !$trackVisits;
        }

        $view = new Piwik_View('@CoreAdminHome/optOut');
        $view->trackVisits = $trackVisits;
        $view->nonce = Piwik_Nonce::getNonce('Piwik_OptOut', 3600);
        $view->language = Piwik_LanguagesManager_API::getInstance()->isLanguageAvailable($language)
            ? $language
            : Piwik_LanguagesManager::getLanguageCodeForCurrentUser();
        echo $view->render();
    }

    public function uploadCustomLogo()
    {
        Piwik::checkUserIsSuperUser();
        if (empty($_FILES['customLogo'])
            || !empty($_FILES['customLogo']['error'])
        ) {
            echo '0';
            return;
        }

        $file = $_FILES['customLogo']['tmp_name'];
        if (!file_exists($file)) {
            echo '0';
            return;
        }

        list($width, $height) = getimagesize($file);
        switch ($_FILES['customLogo']['type']) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($file);
                break;
            case 'image/png':
                $image = imagecreatefrompng($file);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($file);
                break;
            default:
                echo '0';
                return;
        }

        $widthExpected = round($width * self::LOGO_HEIGHT / $height);
        $smallWidthExpected = round($width * self::LOGO_SMALL_HEIGHT / $height);

        $logo = imagecreatetruecolor($widthExpected, self::LOGO_HEIGHT);
        $logoSmall = imagecreatetruecolor($smallWidthExpected, self::LOGO_SMALL_HEIGHT);
        imagecopyresized($logo, $image, 0, 0, 0, 0, $widthExpected, self::LOGO_HEIGHT, $width, $height);
        imagecopyresized($logoSmall, $image, 0, 0, 0, 0, $smallWidthExpected, self::LOGO_SMALL_HEIGHT, $width, $height);

        imagepng($logo, PIWIK_DOCUMENT_ROOT . '/misc/user/logo.png', 3);
        imagepng($logoSmall, PIWIK_DOCUMENT_ROOT . '/misc/user/logo-header.png', 3);
        echo '1';
        return;
    }
}
