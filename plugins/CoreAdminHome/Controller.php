<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreAdminHome;

use Exception;
use Piwik\API\ResponseBuilder;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Common;
use Piwik\Config;
use Piwik\Mail;
use Piwik\Menu\MenuTop;
use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Plugin\ControllerAdmin;
use Piwik\Changes\UserChanges;
use Piwik\Plugins\CorePluginsAdmin\CorePluginsAdmin;
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
use Piwik\Plugins\UsersManager\Model as UsersModel;

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

        if ($isInternetEnabled && $isMarketplaceEnabled) {
            $this->securityPolicy->addPolicy('img-src', '*.matomo.org');
        }

        $hasDonateForm = $widgetsList->isDefined('CoreHome', 'getDonateForm');
        $hasPiwikBlog = $widgetsList->isDefined('RssWidget', 'rssPiwik');
        $hasPremiumFeatures = $widgetsList->isDefined('Marketplace', 'getPremiumFeatures');
        $hasNewPlugins = $widgetsList->isDefined('Marketplace', 'getNewPlugins');
        $hasDiagnostics = $widgetsList->isDefined('Installation', 'getSystemCheck');
        $hasTrackingFailures = $widgetsList->isDefined('CoreAdminHome', 'getTrackingFailures');
        $hasQuickLinks = $widgetsList->isDefined('CoreHome', 'quickLinks');
        $hasSystemSummary = $widgetsList->isDefined('CoreHome', 'getSystemSummary');

        return $this->renderTemplate('home', array(
            'isInternetEnabled' => $isInternetEnabled,
            'isMarketplaceEnabled' => $isMarketplaceEnabled,
            'hasPremiumFeatures' => $hasPremiumFeatures,
            'hasNewPlugins' => $hasNewPlugins,
            'isFeedbackEnabled' => $isFeedbackEnabled,
            'hasDonateForm' => $hasDonateForm,
            'hasPiwikBlog' => $hasPiwikBlog,
            'hasDiagnostics' => $hasDiagnostics,
            'hasTrackingFailures' => $hasTrackingFailures,
            'hasQuickLinks' => $hasQuickLinks,
            'hasSystemSummary' => $hasSystemSummary,
        ));
    }

    public function index()
    {
        $this->redirectToIndex('UsersManager', 'userSettings');
        return;
    }

    public function trackingFailures()
    {
        Piwik::checkUserHasSomeAdminAccess();

        return $this->renderTemplate('trackingFailures');
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
        $view->customLogoEnabled     = $logo->isCustomLogoFeatureEnabled();
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
            'Cram-md5' => 'Cram-md5',
        );
        $view->mailEncryptions = array(
            '' => 'auto',
            'ssl' => 'SSL',
            'tls' => 'TLS',
            'none' => 'none',
        );
        $mail = new Mail();
        $view->mailHost = $mail->getMailHost();

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

        $response = new ResponseBuilder('json');
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

            if (!array_key_exists('mailPassword', $_POST) && Config::getInstance()->mail['host'] === $mail['host']) {
                // use old password if it wasn't set in request (and the host wasn't changed)
                $mail['password'] = Config::getInstance()->mail['password'];
            }

            $mail['encryption'] = Common::getRequestVar('mailEncryption', '');

            Config::getInstance()->mail = $mail;

            $general = Config::getInstance()->General;
            $fromName = Common::getRequestVar('mailFromName', '');
            $general['noreply_email_name'] = Common::unsanitizeInputValue($fromName);

            $mailFrom = Common::getRequestVar('mailFromAddress', '');
            if (empty($mailFrom)) {
                $mailFrom = 'noreply@{DOMAIN}';
            } else {
                $mailFrom = Common::unsanitizeInputValue($mailFrom);
            }
            if (!Piwik::isValidEmailString($mailFrom) && !Common::stringEndsWith($mailFrom, '@{DOMAIN}')) {
                throw new Exception(Piwik::translate('CoreAdminHome_ErrorEmailFromAddressNotValid'));
            }
            $general['noreply_email_address'] = $mailFrom;
            Config::getInstance()->General = $general;

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
        $view->idSite = $this->idSite ?: $defaultIdSite;

        if ($view->idSite) {
            try {
                $view->siteName = Site::getNameFor($view->idSite);
                $view->siteNameDecoded = Common::unsanitizeInputValue($view->siteName);
            } catch (Exception $e) {
                // ignore if site no longer exists
            }
        }

        $view->defaultReportSiteName = Site::getNameFor($view->idSite);
        $view->defaultSiteRevenue = Site::getCurrencySymbolFor($view->idSite);
        $view->maxCustomVariables = 0;

        if (Plugin\Manager::getInstance()->isPluginActivated('CustomVariables')) {
            $view->maxCustomVariables = CustomVariables::getNumUsableCustomVariables();
        }

        $view->defaultSite = array('id' => $view->idSite, 'name' => $view->defaultReportSiteName);
        $view->defaultSiteDecoded = [
            'id' => $view->idSite,
            'name' => Common::unsanitizeInputValue($view->defaultReportSiteName),
        ];

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
     * Shows the "Track Visits" checkbox - iFrame (deprecated)
     */
    public function optOut()
    {
        return $this->optOutManager->getOptOutViewIframe()->render();
    }

    /**
     * Shows the Javascript opt out
     *
     * @return string
     * @throws Exception
     */
    public function optOutJS(): string
    {
        Common::sendHeader('Content-Type: application/javascript; charset=utf-8');
        Common::sendHeader('Cache-Control: no-store');
        return $this->optOutManager->getOptOutJS();
    }

    public function uploadCustomLogo()
    {
        Piwik::checkUserHasSuperUserAccess();
        $this->checkTokenInUrl();

        $logo = new CustomLogo();

        if (! $logo->isCustomLogoFeatureEnabled()) {
            return '0';
        }

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
        $view->isMultiServerEnvironment = SettingsPiwik::isMultiServerEnvironment();
        $view->isPluginsAdminEnabled = CorePluginsAdmin::isPluginsAdminEnabled();
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
        $view->showSegmentArchiveTriggerInfo = Rules::isBrowserArchivingAvailableForSegments();

        $mail = Config::getInstance()->mail;
        $mail['noreply_email_address'] = Config::getInstance()->General['noreply_email_address'];
        $mail['noreply_email_name'] = Config::getInstance()->General['noreply_email_name'];
        $mail['password'] = !empty($mail['password']) ? '*****' : '';
        $view->mail = $mail;
    }

    /**
     * Show the what is new changes list
     */
    public function whatIsNew()
    {
        Piwik::checkUserHasSomeViewAccess();
        Piwik::checkUserIsNotAnonymous();

        $model = new UsersModel();
        $user = $model->getUser(Piwik::getCurrentUserLogin());
        if (is_array($user)) {
            $userChanges = new UserChanges($user);
            $changes = $userChanges->getChanges();
            return $this->renderTemplate('whatIsNew', ['changes' => $changes]);
        } else {
            throw new \Exception('Unable to getUser() when attempting to show whatIsNew');
        }
    }

}
