<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreUpdater;

use Piwik\Config;
use Piwik\Mail;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\UsersManager\API as UsersManagerApi;
use Piwik\SettingsPiwik;
use Piwik\UpdateCheck;
use Piwik\Version;
use Piwik\View;

/**
 * Class to check and notify users via email if there is a core update available.
 */
class UpdateCommunication
{

    /**
     * Checks whether update communication in general is enabled or not.
     *
     * @return bool
     */
    public function isEnabled()
    {
        $isEnabled = (bool) Config::getInstance()->General['enable_update_communication'];

        if($isEnabled === true && SettingsPiwik::isInternetEnabled() === true){
            return true;
        }
        
        return false;
    }

    /**
     * Sends a notification email to all super users if there is a core update available but only if we haven't notfied
     * them about a specific new version yet.
     *
     * @return bool
     */
    public function sendNotificationIfUpdateAvailable()
    {
        if (!$this->isNewVersionAvailable()) {
            return;
        }

        if ($this->hasNotificationAlreadyReceived()) {
            return;
        }

        $this->setHasLatestUpdateNotificationReceived();
        $this->sendNotifications();
    }

    protected function sendNotifications()
    {
        $latestVersion = $this->getLatestVersion();

        $host = SettingsPiwik::getPiwikUrl();

        $subject  = Piwik::translate('CoreUpdater_NotificationSubjectAvailableCoreUpdate', $latestVersion);

        $view = new View('@CoreUpdater/_updateCommunicationEmail.twig');
        $view->latestVersion = $latestVersion;
        $view->host = $host;

        $version = new Version();
        $view->isStableVersion = $version->isStableVersion($latestVersion);
        $view->linkToChangeLog = $this->getLinkToChangeLog($latestVersion);

        $this->sendEmailNotification($subject, $view);
    }

    private function getLinkToChangeLog($version)
    {
        $version = str_replace('.', '-', $version);

        $link = sprintf('https://matomo.org/changelog/matomo-%s/', $version);

        return $link;
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
            $mail->setWrappedHtmlBody($message);
            $mail->send();
        }
    }

    protected function isNewVersionAvailable()
    {
        UpdateCheck::check();

        $hasUpdate = UpdateCheck::isNewestVersionAvailable();

        if (!$hasUpdate) {
            return false;
        }

        $latestVersion = self::getLatestVersion();
        $version = new Version();
        if (!$version->isVersionNumber($latestVersion)) {
            return false;
        }

        return $hasUpdate;
    }

    protected function hasNotificationAlreadyReceived()
    {
        $latestVersion   = $this->getLatestVersion();
        $lastVersionSent = $this->getLatestVersionSent();

        if (!empty($lastVersionSent)
            && ($latestVersion == $lastVersionSent
                || version_compare($latestVersion, $lastVersionSent) == -1)) {
            return true;
        }

        return false;
    }

    private function getLatestVersion()
    {
        $version = UpdateCheck::getLatestVersion();

        if (!empty($version)) {
            $version = trim($version);
        }

        return $version;
    }

    private function getLatestVersionSent()
    {
        return Option::get($this->getNotificationSentOptionName());
    }

    private function setHasLatestUpdateNotificationReceived()
    {
        $latestVersion = $this->getLatestVersion();

        Option::set($this->getNotificationSentOptionName(), $latestVersion);
    }

    private function getNotificationSentOptionName()
    {
        return 'last_update_communication_sent_core';
    }
}
