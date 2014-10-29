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
use Piwik\Date;
use Piwik\Menu\MenuAdmin;
use Piwik\Menu\MenuTop;
use Piwik\Menu\MenuUser;
use Piwik\Notification;
use Piwik\Notification\Manager as NotificationManager;
use Piwik\Piwik;
use Piwik\Url;
use Piwik\Version;
use Piwik\View;

/**
 * Base class of plugin controllers that provide administrative functionality.
 *
 * See {@link Controller} to learn more about Piwik controllers.
 *
 */
abstract class ControllerAdmin extends Controller
{
    private static $isEacceleratorUsed = false;

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

    /**
     * See https://github.com/piwik/piwik/issues/4439#comment:8 and https://github.com/eaccelerator/eaccelerator/issues/12
     *
     * Eaccelerator does not support closures and is known to be not comptabile with Piwik. Therefore we are disabling
     * it automatically. At this point it looks like Eaccelerator is no longer under development and the bug has not
     * been fixed within a year.
     */
    public static function disableEacceleratorIfEnabled()
    {
        $isEacceleratorUsed = ini_get('eaccelerator.enable');

        if (!empty($isEacceleratorUsed)) {
            self::$isEacceleratorUsed = true;

            @ini_set('eaccelerator.enable', 0);
        }
    }

    private static function notifyIfEAcceleratorIsUsed()
    {
        if (!self::$isEacceleratorUsed) {
            return;
        }
        $message = sprintf("You are using the PHP accelerator & optimizer eAccelerator which is known to be not compatible with Piwik.
            We have disabled eAccelerator, which might affect the performance of Piwik.
            Read the %srelated ticket%s for more information and how to fix this problem.",
            '<a target="_blank" href="https://github.com/piwik/piwik/issues/4439">', '</a>');

        $notification = new Notification($message);
        $notification->context = Notification::CONTEXT_WARNING;
        $notification->raw     = true;
        Notification\Manager::notify('ControllerAdmin_EacceleratorIsUsed', $notification);
    }

    private static function notifyWhenPhpVersionIsEOL()
    {
        $notifyPhpIsEOL = Piwik::hasUserSuperUserAccess() && self::isPhpVersion53();
        if (!$notifyPhpIsEOL) {
            return;
        }
        $dateDropSupport = Date::factory('2015-05-01')->getLocalized('%longMonth% %longYear%');
        $message = Piwik::translate('General_WarningPiwikWillStopSupportingPHPVersion', $dateDropSupport)
            . "\n "
            . Piwik::translate('General_WarningPhpVersionXIsTooOld', '5.3');

        $notification = new Notification($message);
        $notification->title = Piwik::translate('General_Warning');
        $notification->priority = Notification::PRIORITY_LOW;
        $notification->context = Notification::CONTEXT_WARNING;
        $notification->type = Notification::TYPE_TRANSIENT;
        $notification->flags = Notification::FLAG_NO_CLEAR;
        NotificationManager::notify('PHP53VersionCheck', $notification);
    }

    /**
     * Assigns view properties that would be useful to views that render admin pages.
     *
     * Assigns the following variables:
     *
     * - **statisticsNotRecorded** - Set to true if the `[Tracker] record_statistics` INI
     *                               config is `0`. If not `0`, this variable will not be defined.
     * - **topMenu** - The result of `MenuTop::getInstance()->getMenu()`.
     * - **currentAdminMenuName** - The currently selected admin menu name.
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

        $view->topMenu  = MenuTop::getInstance()->getMenu();
        $view->userMenu = MenuUser::getInstance()->getMenu();
        $view->currentAdminMenuName = MenuAdmin::getInstance()->getCurrentAdminMenuName();

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

}
