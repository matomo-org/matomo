<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugin;

use Piwik\Config as PiwikConfig;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Development;
use Piwik\Menu\MenuAdmin;
use Piwik\Menu\MenuTop;
use Piwik\Notification;
use Piwik\Notification\Manager as NotificationManager;
use Piwik\Piwik;
use Piwik\Plugins\Marketplace\Marketplace;
use Piwik\Tracker\TrackerConfig;
use Piwik\Url;
use Piwik\Version;
use Piwik\View;
use Piwik\ProxyHttp;
use Piwik\SettingsPiwik;

/**
 * Base class of plugin controllers that provide administrative functionality.
 *
 * See {@link Controller} to learn more about Piwik controllers.
 *
 */
abstract class ControllerAdmin extends Controller
{
    private static function notifyWhenTrackingStatisticsDisabled()
    {
        $statsEnabled = PiwikConfig::getInstance()->Tracker['record_statistics'];
        if ($statsEnabled == "0") {
            $notification = new Notification(Piwik::translate('General_StatisticsAreNotRecorded'));
            $notification->context = Notification::CONTEXT_INFO;
            Notification\Manager::notify('ControllerAdmin_StatsAreNotRecorded', $notification);
        }
    }

    private static function notifyAnyInvalidLicense()
    {
        if (!Marketplace::isMarketplaceEnabled()) {
            return;
        }

        if (Piwik::isUserIsAnonymous()) {
            return;
        }

        if (!Piwik::isUserHasSomeAdminAccess()) {
            return;
        }

        if (Development::isEnabled()) {
            return;
        }

        $expired = StaticContainer::get('Piwik\Plugins\Marketplace\Plugins\InvalidLicenses');

        $messageLicenseMissing = $expired->getMessageNoLicense();
        if (!empty($messageLicenseMissing)) {
            $notification = new Notification($messageLicenseMissing);
            $notification->raw = true;
            $notification->context = Notification::CONTEXT_ERROR;
            $notification->title = Piwik::translate('Marketplace_LicenseMissing');
            Notification\Manager::notify('ControllerAdmin_LicenseMissingWarning', $notification);
        }

        $messageExceeded = $expired->getMessageExceededLicenses();
        if (!empty($messageExceeded)) {
            $notification = new Notification($messageExceeded);
            $notification->raw = true;
            $notification->context = Notification::CONTEXT_WARNING;
            $notification->title = Piwik::translate('Marketplace_LicenseExceeded');
            Notification\Manager::notify('ControllerAdmin_LicenseExceededWarning', $notification);
        }

        $messageExpired = $expired->getMessageExpiredLicenses();
        if (!empty($messageExpired)) {
            $notification = new Notification($messageExpired);
            $notification->raw = true;
            $notification->context = Notification::CONTEXT_WARNING;
            $notification->title = Piwik::translate('Marketplace_LicenseExpired');
            Notification\Manager::notify('ControllerAdmin_LicenseExpiredWarning', $notification);
        }
    }

    private static function notifyAnyInvalidPlugin()
    {
        if (!Piwik::hasUserSuperUserAccess()) {
            return;
        }

        $missingPlugins = \Piwik\Plugin\Manager::getInstance()->getMissingPlugins();

        if (empty($missingPlugins)) {
            return;
        }

        $pluginsLink = Url::getCurrentQueryStringWithParametersModified([
            'module' => 'CorePluginsAdmin', 'action' => 'plugins'
        ]);

        $invalidPluginsWarning = Piwik::translate('CoreAdminHome_InvalidPluginsWarning', [
                self::getPiwikVersion(),
                '<strong>' . implode('</strong>,&nbsp;<wbr><strong>', $missingPlugins) . '</strong>'])
            . "<br/>"
            . Piwik::translate('CoreAdminHome_InvalidPluginsYouCanUninstall', [
                '<a href="' . $pluginsLink . '"/>',
                '</a>'
            ]);

        $notification = new Notification($invalidPluginsWarning);
        $notification->raw = true;
        $notification->context = Notification::CONTEXT_WARNING;
        $notification->title = Piwik::translate('General_Warning');
        Notification\Manager::notify('ControllerAdmin_InvalidPluginsWarning', $notification);
    }

    /**
     * Calls {@link setBasicVariablesView()} and {@link setBasicVariablesAdminView()}
     * using the supplied view.
     *
     * @param View $view
     * @param string $viewType If 'admin', the admin variables are set as well as basic ones.
     */
    protected function setBasicVariablesViewAs($view, $viewType = 'admin')
    {
        $this->setBasicVariablesNoneAdminView($view);
        if ($viewType === 'admin') {
            self::setBasicVariablesAdminView($view);
        }
    }

    private static function notifyIfURLIsNotSecure()
    {
        $isURLSecure = ProxyHttp::isHttps();
        if ($isURLSecure) {
            return;
        }

        if (!Piwik::hasUserSuperUserAccess()) {
            return;
        }

        if (Url::isLocalHost(Url::getCurrentHost())) {
            return;
        }

        if (Development::isEnabled()) {
            return;
        }

        $message = Piwik::translate('General_CurrentlyUsingUnsecureHttp');

        $message .= " ";

        $message .= Piwik::translate(
            'General_ReadThisToLearnMore',
            ['<a rel="noreferrer noopener" target="_blank" href="https://matomo.org/faq/how-to/faq_91/">', '</a>']
        );

        $notification = new Notification($message);
        $notification->context = Notification::CONTEXT_WARNING;
        $notification->raw     = true;
        Notification\Manager::notify('ControllerAdmin_HttpIsUsed', $notification);
    }

    private static function notifyIfDevelopmentModeOnButNotInstalledThroughGit()
    {
        if (!Piwik::hasUserSuperUserAccess()) {
            return;
        }

        if (!Development::isEnabled()) {
            return;
        }

        if (SettingsPiwik::isGitDeployment()) {
            return;
        }

        $message = Piwik::translate('General_WarningDevelopmentModeOnButNotGitInstalled');

        $notification = new Notification($message);
        $notification->context = Notification::CONTEXT_WARNING;
        $notification->raw = true;
        $notification->flags = Notification::FLAG_CLEAR;
        Notification\Manager::notify('ControllerAdmin_DevelopmentModeOn', $notification);
    }

    /**
     * @ignore
     */
    public static function displayWarningIfConfigFileNotWritable()
    {
        $isConfigFileWritable = PiwikConfig::getInstance()->isFileWritable();

        if (!$isConfigFileWritable) {
            $exception = PiwikConfig::getInstance()->getConfigNotWritableException();
            $message = $exception->getMessage();

            $notification = new Notification($message);
            $notification->raw     = true;
            $notification->context = Notification::CONTEXT_WARNING;
            Notification\Manager::notify('ControllerAdmin_ConfigNotWriteable', $notification);
        }
    }


    private static function notifyIfEAcceleratorIsUsed()
    {
        $isEacceleratorUsed = ini_get('eaccelerator.enable');
        if (empty($isEacceleratorUsed)) {
            return;
        }
        $message = sprintf(
            "You are using the PHP accelerator & optimizer eAccelerator which is known to be not compatible with Matomo.
            We have disabled eAccelerator, which might affect the performance of Matomo.
            Read the %srelated ticket%s for more information and how to fix this problem.",
            '<a rel="noreferrer noopener" target="_blank" href="https://github.com/matomo-org/matomo/issues/4439">',
            '</a>'
        );

        $notification = new Notification($message);
        $notification->context = Notification::CONTEXT_WARNING;
        $notification->raw     = true;
        Notification\Manager::notify('ControllerAdmin_EacceleratorIsUsed', $notification);
    }

    /**
     * PHP Version required by the next major Matomo version
     * @return string
     */
    private static function getNextRequiredMinimumPHP()
    {
        return '7.2';
    }

    private static function isUsingPhpVersionCompatibleWithNextPiwik()
    {
        return version_compare(PHP_VERSION, self::getNextRequiredMinimumPHP(), '>=');
    }

    private static function notifyWhenPhpVersionIsNotCompatibleWithNextMajorPiwik()
    {
        if (self::isUsingPhpVersionCompatibleWithNextPiwik()) {
            return;
        }

        $youMustUpgradePHP = Piwik::translate('General_YouMustUpgradePhpVersionToReceiveLatestPiwik');
        $message =  Piwik::translate('General_PiwikCannotBeUpgradedBecausePhpIsTooOld')
            .     ' '
            .  sprintf(Piwik::translate('General_PleaseUpgradeYourPhpVersionSoYourPiwikDataStaysSecure'), self::getNextRequiredMinimumPHP())
        ;

        $notification = new Notification($message);
        $notification->title = $youMustUpgradePHP;
        $notification->priority = Notification::PRIORITY_LOW;
        $notification->context = Notification::CONTEXT_WARNING;
        $notification->type = Notification::TYPE_TRANSIENT;
        $notification->flags = Notification::FLAG_NO_CLEAR;
        NotificationManager::notify('PHPVersionTooOldForNewestPiwikCheck', $notification);
    }

    private static function notifyWhenPhpVersionIsEOL()
    {
        if (defined('PIWIK_TEST_MODE')) { // to avoid changing every admin UI test
            return;
        }

        $notifyPhpIsEOL = Piwik::hasUserSuperUserAccess() && self::isPhpVersionEOL();
        if (!$notifyPhpIsEOL) {
            return;
        }

        $deprecatedMajorPhpVersion = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;

        $message = '';

        if (version_compare(PHP_VERSION, self::getNextRequiredMinimumPHP(), '<')) {
            $message = Piwik::translate(
                'General_WarningPiwikWillStopSupportingPHPVersion',
                [$deprecatedMajorPhpVersion, self::getNextRequiredMinimumPHP()]
            ) . '<br/>';
        }

        $message .= Piwik::translate('General_WarningPhpVersionXIsTooOld', $deprecatedMajorPhpVersion);

        $notification = new Notification($message);
        $notification->raw = true;
        $notification->title = Piwik::translate('General_Warning');
        $notification->priority = Notification::PRIORITY_LOW;
        $notification->context = Notification::CONTEXT_WARNING;
        $notification->type = Notification::TYPE_TRANSIENT;
        $notification->flags = Notification::FLAG_NO_CLEAR;
        NotificationManager::notify('PHPVersionCheck', $notification);
    }

    private static function notifyWhenDebugOnDemandIsEnabled($trackerSetting)
    {
        if (
            !Development::isEnabled()
            && Piwik::hasUserSuperUserAccess()
            && TrackerConfig::getConfigValue($trackerSetting)
        ) {
            $message = Piwik::translate('General_WarningDebugOnDemandEnabled');
            $message = sprintf(
                $message,
                '"' . $trackerSetting . '"',
                '"[Tracker] ' .  $trackerSetting . '"',
                '"0"',
                '"config/config.ini.php"'
            );
            $notification = new Notification($message);
            $notification->title = Piwik::translate('General_Warning');
            $notification->priority = Notification::PRIORITY_LOW;
            $notification->context = Notification::CONTEXT_WARNING;
            $notification->type = Notification::TYPE_TRANSIENT;
            $notification->flags = Notification::FLAG_NO_CLEAR;
            NotificationManager::notify('Tracker' . $trackerSetting, $notification);
        }
    }

    /**
     * Assigns view properties that would be useful to views that render admin pages.
     *
     * Assigns the following variables:
     *
     * - **statisticsNotRecorded** - Set to true if the `[Tracker] record_statistics` INI
     *                               config is `0`. If not `0`, this variable will not be defined.
     * - **topMenu** - The result of `MenuTop::getInstance()->getMenu()`.
     * - **enableFrames** - The value of the `[General] enable_framed_pages` INI config option. If
     *                    true, {@link Piwik\View::setXFrameOptions()} is called on the view.
     * - **isSuperUser** - Whether the current user is a superuser or not.
     * - **usingOldGeoIPPlugin** - Whether this Piwik install is currently using the old GeoIP
     *                             plugin or not.
     * - **invalidPluginsWarning** - Set if some of the plugins to load (determined by INI configuration)
     *                               are invalid or missing.
     * - **phpVersion** - The current PHP version.
     * - **phpIsNewEnough** - Whether the current PHP version is new enough to run Piwik.
     * - **adminMenu** - The result of `MenuAdmin::getInstance()->getMenu()`.
     *
     * @param View $view
     * @api
     */
    public static function setBasicVariablesAdminView(View $view)
    {
        self::notifyWhenTrackingStatisticsDisabled();
        self::notifyIfEAcceleratorIsUsed();
        self::notifyIfURLIsNotSecure();
        self::notifyIfDevelopmentModeOnButNotInstalledThroughGit();

        $view->topMenu = MenuTop::getInstance()->getMenu();

        $view->isDataPurgeSettingsEnabled = self::isDataPurgeSettingsEnabled();
        $enableFrames = PiwikConfig::getInstance()->General['enable_framed_settings'];
        $view->enableFrames = $enableFrames;

        if (!$enableFrames) {
            $view->setXFrameOptions('sameorigin');
        }

        $view->isSuperUser = Piwik::hasUserSuperUserAccess();

        self::notifyAnyInvalidLicense();
        self::notifyAnyInvalidPlugin();
        self::notifyWhenPhpVersionIsEOL();
        self::notifyWhenPhpVersionIsNotCompatibleWithNextMajorPiwik();
        self::notifyWhenDebugOnDemandIsEnabled('debug');
        self::notifyWhenDebugOnDemandIsEnabled('debug_on_demand');

        /**
         * Posted when rendering an admin page and notifications about any warnings or errors should be triggered.
         * You can use it for example when you have a plugin that needs to be configured in order to work and the
         * plugin has not been configured yet. It can be also used to cancel / remove other notifications by calling
         * eg `Notification\Manager::cancel($notificationId)`.
         *
         * **Example**
         *
         *     public function onTriggerAdminNotifications(Piwik\Widget\WidgetsList $list)
         *     {
         *         if ($pluginFooIsNotConfigured) {
         *              $notification = new Notification('The plugin foo has not been configured yet');
         *              $notification->context = Notification::CONTEXT_WARNING;
         *              Notification\Manager::notify('fooNotConfigured', $notification);
         *         }
         *     }
         *
         */
        Piwik::postEvent('Controller.triggerAdminNotifications');

        $view->adminMenu = MenuAdmin::getInstance()->getMenu();

        $notifications = $view->notifications;

        if (empty($notifications)) {
            $view->notifications = NotificationManager::getAllNotificationsToDisplay();
            NotificationManager::cancelAllNonPersistent();
        }
    }

    public static function isDataPurgeSettingsEnabled()
    {
        return (bool) Config::getInstance()->General['enable_delete_old_data_settings_admin'];
    }

    protected static function getPiwikVersion()
    {
        return "Matomo " . Version::VERSION;
    }

    private static function isPhpVersionEOL()
    {
        $phpEOL = '7.3';

        // End of security update for certain PHP versions as of https://www.php.net/supported-versions.php
        if (Date::today()->isLater(Date::factory('2022-11-28'))) {
            $phpEOL = '7.4';
        }
        if (Date::today()->isLater(Date::factory('2023-11-26'))) {
            $phpEOL = '8.0';
        }
        if (Date::today()->isLater(Date::factory('2024-11-25'))) {
            $phpEOL = '8.1';
        }

        return version_compare(PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION, $phpEOL, '<=');
    }
}
