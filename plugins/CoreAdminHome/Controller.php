<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreAdminHome;

use Exception;
use Piwik\API\ResponseBuilder;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Common;
use Piwik\Config;
use Piwik\Menu\MenuTop;
use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Plugin\ControllerAdmin;
use Piwik\Plugins\Marketplace\Marketplace;
use Piwik\Plugins\CustomVariables\CustomVariables;
use Piwik\Plugins\LanguagesManager\LanguagesManager;
use Piwik\Plugins\PrivacyManager\DoNotTrackHeaderChecker;
use Piwik\Plugins\SitesManager\API as APISitesManager;
use Piwik\Site;
use Piwik\Translation\Translator;
use Piwik\Url;
use Piwik\View;
use Piwik\Widget\WidgetsList;
use Piwik\SettingsPiwik;

class Controller extends ControllerAdmin
{
    /**
     * @var Translator
     */
    private $translator;

    /** @var OptOutManager */
    private $optOutManager;

    public function __construct(Translator $translator, OptOutManager $optOutManager)
    {
        $this->translator = $translator;
        $this->optOutManager = $optOutManager;

        parent::__construct();
    }

    public function home()
    {
        $isInternetEnabled = SettingsPiwik::isInternetEnabled();
        
        $isMarketplaceEnabled = Marketplace::isMarketplaceEnabled();
        $isFeedbackEnabled = Plugin\Manager::getInstance()->isPluginLoaded('Feedback');
        $widgetsList = WidgetsList::get();

        $hasDonateForm = $widgetsList->isDefined('CoreHome', 'getDonateForm');
        $hasPiwikBlog = $widgetsList->isDefined('RssWidget', 'rssPiwik');
        $hasPremiumFeatures = $widgetsList->isDefined('Marketplace', 'getPremiumFeatures');
        $hasNewPlugins = $widgetsList->isDefined('Marketplace', 'getNewPlugins');
        $hasDiagnostics = $widgetsList->isDefined('Installation', 'getSystemCheck');

        return $this->renderTemplate('home', array(
            'isInternetEnabled' => $isInternetEnabled,
            'isMarketplaceEnabled' => $isMarketplaceEnabled,
            'hasPremiumFeatures' => $hasPremiumFeatures,
            'hasNewPlugins' => $hasNewPlugins,
            'isFeedbackEnabled' => $isFeedbackEnabled,
            'hasDonateForm' => $hasDonateForm,
            'hasPiwikBlog' => $hasPiwikBlog,
            'hasDiagnostics' => $hasDiagnostics,
        ));
    }

    public function index()
    {
        $this->redirectToIndex('UsersManager', 'userSettings');
        return;
    }

    public function generalSettings()
    {
        Piwik::checkUserHasSuperUserAccess();

        $view = new View('@CoreAdminHome/generalSettings');
        $this->handleGeneralSettingsAdmin($view);

        $view->trustedHosts = array_values(Url::getTrustedHostsFromConfig());
        $logo = new CustomLogo();
        $view->branding              = array('use_custom_logo' => $logo->isEnabled());
        $view->fileUploadEnabled     = $logo->isFileUploadEnabled();
        $view->logosWriteable        = $logo->isCustomLogoWritable();
        $view->hasUserLogo           = CustomLogo::hasUserLogo();
        $view->pathUserLogo          = CustomLogo::getPathUserLogo();
        $view->hasUserFavicon        = CustomLogo::hasUserFavicon();
        $view->pathUserFavicon       = CustomLogo::getPathUserFavicon();
        $view->pathUserLogoSmall     = CustomLogo::getPathUserLogoSmall();
        $view->pathUserLogoSVG       = CustomLogo::getPathUserSvgLogo();
        $view->pathUserLogoDirectory = realpath(dirname($view->pathUserLogo) . '/');
        $view->mailTypes = array(
            '' => '',
            'Plain' => 'Plain',
            'Login' => 'Login',
            'Crammd5' => 'Crammd5',
        );
        $view->mailEncryptions = array(
            '' => '',
            'ssl' => 'SSL',
            'tls' => 'TLS'
        );

        $view->language = LanguagesManager::getLanguageCodeForCurrentUser();
        $this->setBasicVariablesView($view);
        return $view->render();
    }

    public function setMailSettings()
    {
        Piwik::checkUserHasSuperUserAccess();

        if (!self::isGeneralSettingsAdminEnabled()) {
            // General settings + Beta channel + SMTP settings is disabled
            return '';
        }

        $response = new ResponseBuilder('json2');
        try {
            $this->checkTokenInUrl();

            // Update email settings
            $mail = array();
            $mail['transport'] = (Common::getRequestVar('mailUseSmtp') == '1') ? 'smtp' : '';
            $mail['port'] = Common::getRequestVar('mailPort', '');
            $mail['host'] = Common::unsanitizeInputValue(Common::getRequestVar('mailHost', ''));
            $mail['type'] = Common::getRequestVar('mailType', '');
            $mail['username'] = Common::unsanitizeInputValue(Common::getRequestVar('mailUsername', ''));
            $mail['password'] = Common::unsanitizeInputValue(Common::getRequestVar('mailPassword', ''));

            if (!array_key_exists('mailPassword', $_POST)) {
                // use old password if it wasn't set in request
                $mail['password'] = Config::getInstance()->mail['password'];
            }

            $mail['encryption'] = Common::getRequestVar('mailEncryption', '');

            Config::getInstance()->mail = $mail;

            Config::getInstance()->forceSave();

            $toReturn = $response->getResponse();
        } catch (Exception $e) {
            $toReturn = $response->getResponseException($e);
        }

        return $toReturn;
    }

    /**
     * Renders and echo's an admin page that lets users generate custom JavaScript
     * tracking code and custom image tracker links.
     */
    public function trackingCodeGenerator()
    {
        Piwik::checkUserHasSomeViewAccess();
        
        $view = new View('@CoreAdminHome/trackingCodeGenerator');
        $this->setBasicVariablesView($view);
        $view->topMenu  = MenuTop::getInstance()->getMenu();

        $viewableIdSites = APISitesManager::getInstance()->getSitesIdWithAtLeastViewAccess();

        $defaultIdSite = reset($viewableIdSites);
        $view->idSite = Common::getRequestVar('idSite', $defaultIdSite, 'int');

        $view->defaultReportSiteName = Site::getNameFor($view->idSite);
        $view->defaultSiteRevenue = Site::getCurrencySymbolFor($view->idSite);
        $view->maxCustomVariables = CustomVariables::getNumUsableCustomVariables();

        $view->defaultSite = array('id' => $view->idSite, 'name' => $view->defaultReportSiteName);

        $allUrls = APISitesManager::getInstance()->getSiteUrlsFromId($view->idSite);
        if (isset($allUrls[1])) {
            $aliasUrl = $allUrls[1];
        } else {
            $aliasUrl = 'x.domain.com';
        }
        $view->defaultReportSiteAlias = $aliasUrl;

        $mainUrl = Site::getMainUrlFor($view->idSite);
        $view->defaultReportSiteDomain = @parse_url($mainUrl, PHP_URL_HOST);

        $dntChecker = new DoNotTrackHeaderChecker();
        $view->serverSideDoNotTrackEnabled = $dntChecker->isActive();

        return $view->render();
    }

    /**
     * Shows the "Track Visits" checkbox.
     */
    public function optOut()
    {
        return $this->optOutManager->getOptOutView()->render();
    }

    public function uploadCustomLogo()
    {
        Piwik::checkUserHasSuperUserAccess();
        $this->checkTokenInUrl();

        $logo = new CustomLogo();
        $successLogo    = $logo->copyUploadedLogoToFilesystem();
        $successFavicon = $logo->copyUploadedFaviconToFilesystem();

        if ($successLogo || $successFavicon) {
            return '1';
        }
        return '0';
    }

    public static function isGeneralSettingsAdminEnabled()
    {
        return (bool) Config::getInstance()->General['enable_general_settings_admin'];
    }

    private function handleGeneralSettingsAdmin($view)
    {
        // Whether to display or not the general settings (cron, beta, smtp)
        $view->isGeneralSettingsAdminEnabled = self::isGeneralSettingsAdminEnabled();
        if ($view->isGeneralSettingsAdminEnabled) {
            $this->displayWarningIfConfigFileNotWritable();
        }

        $enableBrowserTriggerArchiving = Rules::isBrowserTriggerEnabled();
        $todayArchiveTimeToLive = Rules::getTodayArchiveTimeToLive();
        $showWarningCron = false;
        if (!$enableBrowserTriggerArchiving
            && $todayArchiveTimeToLive < 3600
        ) {
            $showWarningCron = true;
        }
        $view->showWarningCron = $showWarningCron;
        $view->todayArchiveTimeToLive = $todayArchiveTimeToLive;
        $view->todayArchiveTimeToLiveDefault = Rules::getTodayArchiveTimeToLiveDefault();
        $view->enableBrowserTriggerArchiving = $enableBrowserTriggerArchiving;

        $view->mail = Config::getInstance()->mail;
    }

}
