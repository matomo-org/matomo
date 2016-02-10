<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugin;

use Piwik\Config as PiwikConfig;
use Piwik\Config;
use Piwik\Development;
use Piwik\Menu\MenuAdmin;
use Piwik\Menu\MenuTop;
use Piwik\Menu\MenuUser;
use Piwik\Notification;
use Piwik\Notification\Manager as NotificationManager;
use Piwik\Piwik;
use Piwik\Tracker\TrackerConfig;
use Piwik\Url;
use Piwik\Version;
use Piwik\View;
use Piwik\ProxyHttp;

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

    private static function notifyAnyInvalidPlugin()
    {
        $missingPlugins = \Piwik\Plugin\Manager::getInstance()->getMissingPlugins();

        if (empty($missingPlugins)) {
            return;
        }

        if (!Piwik::hasUserSuperUserAccess()) {
            return;
        }

        $pluginsLink = Url::getCurrentQueryStringWithParametersModified(array(
            'module' => 'CorePluginsAdmin', 'action' => 'plugins'
        ));

        $invalidPluginsWarning = Piwik::translate('CoreAdminHome_InvalidPluginsWarning', array(
                self::getPiwikVersion(),
                '<strong>' . implode('</strong>,&nbsp;<strong>', $missingPlugins) . '</strong>'))
            . "<br/>"
            . Piwik::translate('CoreAdminHome_InvalidPluginsYouCanUninstall', array(
                '<a href="' . $pluginsLink . '"/>',
                '</a>'
        ));

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
     * @api
     */
    protected function setBasicVariablesView($view)
    {
        parent::setBasicVariablesView($view);

        self::setBasicVariablesAdminView($view);
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

        if(Url::isLocalHost(Url::getCurrentHost())) {
            return;
        }


        $message = Piwik::translate('General_CurrentlyUsingUnsecureHttp');

        $message .= " ";

        $message .= Piwik::translate('General_ReadThisToLearnMore',
            array('<a rel="noreferrer" target="_blank" href="https://piwik.org/faq/how-to/faq_91/">', '</a>')
          );

        $notification = new Notification($message);
        $notification->context = Notification::CONTEXT_WARNING;
        $notification->raw     = true;
        Notification\Manager::notify('ControllerAdmin_HttpIsUsed', $notification);
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
        $message = sprintf("You are using the PHP accelerator & optimizer eAccelerator which is known to be not compatible with Piwik.
            We have disabled eAccelerator, which might affect the performance of Piwik.
            Read the %srelated ticket%s for more information and how to fix this problem.",
            '<a rel="noreferrer" target="_blank" href="https://github.com/piwik/piwik/issues/4439">', '</a>');

        $notification = new Notification($message);
        $notification->context = Notification::CONTEXT_WARNING;
        $notification->raw     = true;
        Notification\Manager::notify('ControllerAdmin_EacceleratorIsUsed', $notification);
    }

    private static function notifyWhenPhpVersionIsEOL()
    {
        $deprecatedMajorPhpVersion = null;
        if(self::isPhpVersion53()) {
            $deprecatedMajorPhpVersion = '5.3';
        } elseif(self::isPhpVersion54()) {
            $deprecatedMajorPhpVersion = '5.4';
        }

        $notifyPhpIsEOL = Piwik::hasUserSuperUserAccess() && $deprecatedMajorPhpVersion;
        if (!$notifyPhpIsEOL) {
            return;
        }

        $nextRequiredMinimumPHP = '5.5';

        $message = Piwik::translate('General_WarningPiwikWillStopSupportingPHPVersion', array($deprecatedMajorPhpVersion, $nextRequiredMinimumPHP))
            . "\n "
            . Piwik::translate('General_WarningPhpVersionXIsTooOld', $deprecatedMajorPhpVersion);

        $notification = new Notification($message);
        $notification->title = Piwik::translate('General_Warning');
        $notification->priority = Notification::PRIORITY_LOW;
        $notification->context = Notification::CONTEXT_WARNING;
        $notification->type = Notification::TYPE_TRANSIENT;
        $notification->flags = Notification::FLAG_NO_CLEAR;
        NotificationManager::notify('DeprecatedPHPVersionCheck', $notification);
    }

    private static function notifyWhenDebugOnDemandIsEnabled($trackerSetting)
    {
        if (!Development::isEnabled()
            && Piwik::hasUserSuperUserAccess() &&
            TrackerConfig::getConfigValue($trackerSetting)) {

            $message = Piwik::translate('General_WarningDebugOnDemandEnabled');
            $message = sprintf($message, '"' . $trackerSetting . '"', '"[Tracker] ' .  $trackerSetting . '"', '"0"',
                                               '"config/config.ini.php"');
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

        $view->topMenu  = MenuTop::getInstance()->getMenu();
        $view->userMenu = MenuUser::getInstance()->getMenu();

        $view->isDataPurgeSettingsEnabled = self::isDataPurgeSettingsEnabled();
        $enableFrames = PiwikConfig::getInstance()->General['enable_framed_settings'];
        $view->enableFrames = $enableFrames;

        if (!$enableFrames) {
            $view->setXFrameOptions('sameorigin');
        }

        $view->isSuperUser = Piwik::hasUserSuperUserAccess();

        self::notifyAnyInvalidPlugin();

        self::checkPhpVersion($view);

        self::notifyWhenPhpVersionIsEOL();
        self::notifyWhenDebugOnDemandIsEnabled('debug');
        self::notifyWhenDebugOnDemandIsEnabled('debug_on_demand');

        $adminMenu = MenuAdmin::getInstance()->getMenu();
        $view->adminMenu = $adminMenu;

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
        return "Piwik " . Version::VERSION;
    }

    /**
     * Check if the current PHP version is >= 5.3. If not, a warning is displayed
     * to the user.
     */
    private static function checkPhpVersion($view)
    {
        $view->phpVersion = PHP_VERSION;
        $view->phpIsNewEnough = version_compare($view->phpVersion, '5.3.0', '>=');
    }

    private static function isPhpVersion53()
    {
        return strpos(PHP_VERSION, '5.3') === 0;
    }

    private static function isPhpVersion54()
    {
        return strpos(PHP_VERSION, '5.4') === 0;
    }
}
