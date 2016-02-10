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
use Piwik\Container\StaticContainer;
use Piwik\DataTable\Renderer\Json;
use Piwik\Menu\MenuTop;
use Piwik\Menu\MenuUser;
use Piwik\Nonce;
use Piwik\Piwik;
use Piwik\Plugin\ControllerAdmin;
use Piwik\Plugins\CorePluginsAdmin\UpdateCommunication;
use Piwik\Plugins\CustomVariables\CustomVariables;
use Piwik\Plugins\LanguagesManager\LanguagesManager;
use Piwik\Plugins\PrivacyManager\DoNotTrackHeaderChecker;
use Piwik\Plugins\SitesManager\API as APISitesManager;
use Piwik\Settings\Manager as SettingsManager;
use Piwik\Settings\SystemSetting;
use Piwik\Settings\UserSetting;
use Piwik\Site;
use Piwik\Translation\Translator;
use Piwik\UpdateCheck;
use Piwik\Url;
use Piwik\View;

class Controller extends ControllerAdmin
{
    const SET_PLUGIN_SETTINGS_NONCE = 'CoreAdminHome.setPluginSettings';

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

        $view->trustedHosts = Url::getTrustedHostsFromConfig();
        $logo = new CustomLogo();
        $view->branding              = array('use_custom_logo' => $logo->isEnabled());
        $view->fileUploadEnabled     = $logo->isFileUploadEnabled();
        $view->logosWriteable        = $logo->isCustomLogoWritable();
        $view->pathUserLogo          = CustomLogo::getPathUserLogo();
        $view->pathUserFavicon       = CustomLogo::getPathUserFavicon();
        $view->pathUserLogoSmall     = CustomLogo::getPathUserLogoSmall();
        $view->pathUserLogoSVG       = CustomLogo::getPathUserSvgLogo();
        $view->pathUserLogoDirectory = realpath(dirname($view->pathUserLogo) . '/');

        $view->language = LanguagesManager::getLanguageCodeForCurrentUser();
        $this->setBasicVariablesView($view);
        return $view->render();
    }

    public function adminPluginSettings()
    {
        Piwik::checkUserHasSuperUserAccess();

        $settings = $this->getPluginSettings();

        $vars = array(
            'nonce'                      => Nonce::getNonce(static::SET_PLUGIN_SETTINGS_NONCE),
            'pluginsSettings'            => $this->getSettingsByType($settings, 'admin'),
            'firstSuperUserSettingNames' => $this->getFirstSuperUserSettingNames($settings),
            'mode' => 'admin'
        );

        return $this->renderTemplate('pluginSettings', $vars);
    }

    /**
     * @param \Piwik\Plugin\Settings[] $pluginsSettings
     * @return array   array([pluginName] => [])
     */
    private function getSettingsByType($pluginsSettings, $mode)
    {
        $byType = array();

        foreach ($pluginsSettings as $pluginName => $pluginSettings) {
            $settings = array();

            foreach ($pluginSettings->getSettingsForCurrentUser() as $setting) {
                if ('admin' === $mode && $setting instanceof SystemSetting) {
                    $settings[] = $setting;
                } elseif ('user' === $mode && $setting instanceof UserSetting) {
                    $settings[] = $setting;
                }
            }

            if (!empty($settings)) {
                $byType[$pluginName] = array(
                    'introduction' => $pluginSettings->getIntroduction(),
                    'settings' => $settings
                );
            }
        }

        return $byType;
    }

    public function userPluginSettings()
    {
        Piwik::checkUserIsNotAnonymous();

        $settings = $this->getPluginSettings();

        $vars = array(
            'nonce'                      => Nonce::getNonce(static::SET_PLUGIN_SETTINGS_NONCE),
            'pluginsSettings'            => $this->getSettingsByType($settings, 'user'),
            'firstSuperUserSettingNames' => $this->getFirstSuperUserSettingNames($settings),
            'mode' => 'user'
        );

        return $this->renderTemplate('pluginSettings', $vars);
    }

    private function getPluginSettings()
    {
        $pluginsSettings = SettingsManager::getPluginSettingsForCurrentUser();

        ksort($pluginsSettings);

        return $pluginsSettings;
    }

    /**
     * @param \Piwik\Plugin\Settings[] $pluginsSettings
     * @return array   array([pluginName] => [])
     */
    private function getFirstSuperUserSettingNames($pluginsSettings)
    {
        $names = array();
        foreach ($pluginsSettings as $pluginName => $pluginSettings) {

            foreach ($pluginSettings->getSettingsForCurrentUser() as $setting) {
                if ($setting instanceof \Piwik\Settings\SystemSetting) {
                    $names[$pluginName] = $setting->getName();
                    break;
                }
            }
        }

        return $names;
    }

    public function setPluginSettings()
    {
        Piwik::checkUserIsNotAnonymous();
        Json::sendHeaderJSON();

        $nonce = Common::getRequestVar('nonce', null, 'string');

        if (!Nonce::verifyNonce(static::SET_PLUGIN_SETTINGS_NONCE, $nonce)) {
            return json_encode(array(
                'result' => 'error',
                'message' => $this->translator->translate('General_ExceptionNonceMismatch')
            ));
        }

        $pluginsSettings = SettingsManager::getPluginSettingsForCurrentUser();

        try {

            foreach ($pluginsSettings as $pluginName => $pluginSetting) {
                foreach ($pluginSetting->getSettingsForCurrentUser() as $setting) {

                    $value = $this->findSettingValueFromRequest($pluginName, $setting->getKey());

                    if (!is_null($value)) {
                        $setting->setValue($value);
                    }
                }
            }

        } catch (Exception $e) {
            $message = $e->getMessage();

            if (!empty($setting)) {
                $message = $setting->title . ': ' . $message;
            }

            $message = html_entity_decode($message, ENT_QUOTES, 'UTF-8');
            return json_encode(array('result' => 'error', 'message' => $message));
        }

        try {
            foreach ($pluginsSettings as $pluginSetting) {
                $pluginSetting->save();
            }
        } catch (Exception $e) {
            return json_encode(array(
                'result' => 'error',
                'message' => $this->translator->translate('CoreAdminHome_PluginSettingsSaveFailed'))
            );
        }

        Nonce::discardNonce(static::SET_PLUGIN_SETTINGS_NONCE);
        return json_encode(array('result' => 'success'));
    }

    private function findSettingValueFromRequest($pluginName, $settingKey)
    {
        $changedPluginSettings = Common::getRequestVar('settings', null, 'array');

        if (!array_key_exists($pluginName, $changedPluginSettings)) {
            return;
        }

        $settings = $changedPluginSettings[$pluginName];

        foreach ($settings as $setting) {
            if ($setting['name'] == $settingKey) {
                $value = $setting['value'];

                if (is_string($value)) {
                    return Common::unsanitizeInputValue($value);
                }

                return $value;
            }
        }
    }

    public function setGeneralSettings()
    {
        Piwik::checkUserHasSuperUserAccess();
        $response = new ResponseBuilder(Common::getRequestVar('format'));
        try {
            $this->checkTokenInUrl();

            $this->saveGeneralSettings();

            $customLogo = new CustomLogo();
            if (Common::getRequestVar('useCustomLogo', '0')) {
                $customLogo->enable();
            } else {
                $customLogo->disable();
            }

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
        $view->userMenu = MenuUser::getInstance()->getMenu();

        $viewableIdSites = APISitesManager::getInstance()->getSitesIdWithAtLeastViewAccess();

        $defaultIdSite = reset($viewableIdSites);
        $view->idSite = Common::getRequestVar('idSite', $defaultIdSite, 'int');

        $view->defaultReportSiteName = Site::getNameFor($view->idSite);
        $view->defaultSiteRevenue = Site::getCurrencySymbolFor($view->idSite);
        $view->maxCustomVariables = CustomVariables::getNumUsableCustomVariables();

        $allUrls = APISitesManager::getInstance()->getSiteUrlsFromId($view->idSite);
        if (isset($allUrls[1])) {
            $aliasUrl = $allUrls[1];
        } else {
            $aliasUrl = 'x.domain.com';
        }
        $view->defaultReportSiteAlias = $aliasUrl;

        $mainUrl = Site::getMainUrlFor($view->idSite);
        $view->defaultReportSiteDomain = @parse_url($mainUrl, PHP_URL_HOST);

        // get currencies for each viewable site
        $view->currencySymbols = APISitesManager::getInstance()->getCurrencySymbols();

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

    private function makeReleaseChannels()
    {
        return StaticContainer::get('Piwik\Plugin\ReleaseChannels');
    }

    private function saveGeneralSettings()
    {
        if (!self::isGeneralSettingsAdminEnabled()) {
            // General settings + Beta channel + SMTP settings is disabled
            return;
        }

        // General Setting
        $enableBrowserTriggerArchiving = Common::getRequestVar('enableBrowserTriggerArchiving');
        $todayArchiveTimeToLive = Common::getRequestVar('todayArchiveTimeToLive');
        Rules::setBrowserTriggerArchiving((bool)$enableBrowserTriggerArchiving);
        Rules::setTodayArchiveTimeToLive($todayArchiveTimeToLive);

        $releaseChannels = $this->makeReleaseChannels();

        // update beta channel setting
        $releaseChannel = Common::getRequestVar('releaseChannel', '', 'string');
        if (!$releaseChannels->isValidReleaseChannelId($releaseChannel)) {
            $releaseChannel = '';
        }
        $releaseChannels->setActiveReleaseChannelId($releaseChannel);

        // Update email settings
        $mail = array();
        $mail['transport'] = (Common::getRequestVar('mailUseSmtp') == '1') ? 'smtp' : '';
        $mail['port'] = Common::getRequestVar('mailPort', '');
        $mail['host'] = Common::unsanitizeInputValue(Common::getRequestVar('mailHost', ''));
        $mail['type'] = Common::getRequestVar('mailType', '');
        $mail['username'] = Common::unsanitizeInputValue(Common::getRequestVar('mailUsername', ''));
        $mail['password'] = Common::unsanitizeInputValue(Common::getRequestVar('mailPassword', ''));
        $mail['encryption'] = Common::getRequestVar('mailEncryption', '');

        Config::getInstance()->mail = $mail;

        // update trusted host settings
        $trustedHosts = Common::getRequestVar('trustedHosts', false, 'json');
        if ($trustedHosts !== false) {
            Url::saveTrustedHostnameInConfig($trustedHosts);
        }

        Config::getInstance()->forceSave();

        $pluginUpdateCommunication = new UpdateCommunication();
        if (Common::getRequestVar('enablePluginUpdateCommunication', '0', 'int')) {
            $pluginUpdateCommunication->enable();
        } else {
            $pluginUpdateCommunication->disable();
        }
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

        $releaseChannels = $this->makeReleaseChannels();
        $activeChannelId = $releaseChannels->getActiveReleaseChannel()->getId();
        $allChannels = array();
        foreach ($releaseChannels->getAllReleaseChannels() as $channel) {
            $allChannels[] = array(
                'id'   => $channel->getId(),
                'name' => $channel->getName(),
                'description' => $channel->getDescription(),
                'active' => $channel->getId() === $activeChannelId
            );
        }

        $view->releaseChannels = $allChannels;
        $view->mail = Config::getInstance()->mail;

        $pluginUpdateCommunication = new UpdateCommunication();
        $view->canUpdateCommunication              = $pluginUpdateCommunication->canBeEnabled();
        $view->enableSendPluginUpdateCommunication = $pluginUpdateCommunication->isEnabled();
    }

}
