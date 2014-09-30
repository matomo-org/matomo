<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CorePluginsAdmin;

use Piwik\Config;
use Piwik\Mail;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\UsersManager\API as UsersManagerApi;
use Piwik\SettingsPiwik;

/**
 * Class to check and notify users via email if there are plugin updates available.
 */
class UpdateCommunication
{
    private $enabledOptionName = 'enableUpdateCommunicationPlugins';

    /**
     * Checks whether plugin update notification is enabled or not. If the marketplace is disabled or if update
     * communication is disabled in general, it will return false as well.
     *
     * @return bool
     */
    public function isEnabled()
    {
        if (!$this->canBeEnabled()) {
            return false;
        }

        $isEnabled = Option::get($this->enabledOptionName);

        return !empty($isEnabled);
    }

    /**
     * Checks whether a plugin update notification can be enabled or not. It cannot be enabled if for instance the
     * Marketplace is disabled or if update notifications are disabled in general.
     *
     * @return bool
     */
    public function canBeEnabled()
    {
        $isEnabled = Config::getInstance()->General['enable_update_communication'];

        return CorePluginsAdmin::isMarketplaceEnabled() && !empty($isEnabled);
    }

    /**
     * Enable plugin update notifications.
     */
    public function enable()
    {
        Option::set($this->enabledOptionName, 1);
    }

    /**
     * Disable plugin update notifications.
     */
    public function disable()
    {
        Option::set($this->enabledOptionName, 0);
    }

    /**
     * Sends an email to all super users if there is an update available for any plugins from the Marketplace.
     * For each update we send an email only once.
     *
     * @return bool
     */
    public function sendNotificationIfUpdatesAvailable()
    {
        $pluginsHavingUpdate = $this->getPluginsHavingUpdate();

        if (empty($pluginsHavingUpdate)) {
            return;
        }

        $pluginsToBeNotified = array();

        foreach ($pluginsHavingUpdate as $plugin) {
            if ($this->hasNotificationAlreadyReceived($plugin)) {
                continue;
            }

            $this->setHasLatestUpdateNotificationReceived($plugin);

            $pluginsToBeNotified[] = $plugin;
        }

        if (!empty($pluginsToBeNotified)) {
            $this->sendNotifications($pluginsToBeNotified);
        }
    }

    protected function sendNotifications($pluginsToBeNotified)
    {
        $hasThemeUpdate  = false;
        $hasPluginUpdate = false;

        foreach ($pluginsToBeNotified as $plugin) {
            $hasThemeUpdate  = $hasThemeUpdate || $plugin['isTheme'];
            $hasPluginUpdate = $hasPluginUpdate || !$plugin['isTheme'];
        }

        $subject = Piwik::translate('CoreUpdater_NotificationSubjectAvailablePluginUpdate');
        $message = $this->buildNotificationMessage($pluginsToBeNotified, $hasThemeUpdate, $hasPluginUpdate);

        $this->sendEmailNotification($subject, $message);
    }

    /**
     * Send an email notification to all super users.
     *
     * @param $subject
     * @param $message
     */
    protected function sendEmailNotification($subject, $message)
    {
        $superUsers = UsersManagerApi::getInstance()->getUsersHavingSuperUserAccess();

        foreach ($superUsers as $superUser) {
            $mail = new Mail();
            $mail->setDefaultFromPiwik();
            $mail->addTo($superUser['email']);
            $mail->setSubject($subject);
            $mail->setBodyText($message);
            $mail->send();
        }
    }

    protected function setHasLatestUpdateNotificationReceived($plugin)
    {
        $latestVersion = $this->getLatestVersion($plugin);

        Option::set($this->getNotificationSentOptionName($plugin), $latestVersion);
    }

    protected function getLatestVersionSent($plugin)
    {
        return Option::get($this->getNotificationSentOptionName($plugin));
    }

    protected function getLatestVersion($plugin)
    {
        return $plugin['latestVersion'];
    }

    protected function hasNotificationAlreadyReceived($plugin)
    {
        $latestVersion   = $this->getLatestVersion($plugin);
        $lastVersionSent = $this->getLatestVersionSent($plugin);

        if (!empty($lastVersionSent)
            && ($latestVersion == $lastVersionSent
                || version_compare($latestVersion, $lastVersionSent) == -1)) {
            return true;
        }

        return false;
    }

    protected function getNotificationSentOptionName($plugin)
    {
        return 'last_update_communication_sent_plugin_' . $plugin['name'];
    }

    protected function getPluginsHavingUpdate()
    {
        $marketplace         = new Marketplace();
        $pluginsHavingUpdate = $marketplace->getPluginsHavingUpdate($themesOnly = false);
        $themesHavingUpdate  = $marketplace->getPluginsHavingUpdate($themesOnly = true);

        $plugins = array_merge($pluginsHavingUpdate, $themesHavingUpdate);

        return $plugins;
    }

    protected function buildNotificationMessage($pluginsToBeNotified, $hasThemeUpdate, $hasPluginUpdate)
    {
        $message  = Piwik::translate('ScheduledReports_EmailHello');
        $message .= "\n\n";
        $message .= Piwik::translate('CoreUpdater_ThereIsNewPluginVersionAvailableForUpdate');
        $message .= "\n\n";

        foreach ($pluginsToBeNotified as $plugin) {
            $message .= sprintf(' * %s %s', $plugin['name'], $plugin['latestVersion']);
            $message .= "\n";
        }

        $message .= "\n";

        $host = SettingsPiwik::getPiwikUrl();

        if ($hasThemeUpdate) {
            $message .= Piwik::translate('CoreUpdater_NotificationClickToUpdateThemes') . "\n";
            $message .= $host . 'index.php?module=CorePluginsAdmin&action=themes';
        }

        if ($hasPluginUpdate) {
            if ($hasThemeUpdate) {
                $message .= "\n\n";
            }
            $message .= Piwik::translate('CoreUpdater_NotificationClickToUpdatePlugins') . "\n";
            $message .= $host . 'index.php?module=CorePluginsAdmin&action=plugins';
        }

        $message .= "\n\n";
        $message .= Piwik::translate('Installation_HappyAnalysing');

        return $message;
    }
}
