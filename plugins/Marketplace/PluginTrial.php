<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace;

use Exception;
use Piwik\Config\GeneralConfig;
use Piwik\Container\StaticContainer;
use Piwik\Notification;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Plugins\Marketplace\Emails\RequestTrialNotificationEmail;

class PluginTrial
{
    private const OPTION_NAME = 'Marketplace.PluginTrialRequest.%s';
    private $pluginName;
    private $optionName;
    private $storage = null;

    public function __construct(string $pluginName)
    {
        if (!Manager::getInstance()->isValidPluginName($pluginName)) {
            throw new Exception('Invalid plugin name given ' . $pluginName);
        }

        $this->pluginName = $pluginName;
        $this->optionName = sprintf(self::OPTION_NAME, $pluginName);
        $this->loadStorage();
    }

    public function request(): void
    {
        if (!self::isEnabled()) {
            return;
        }

        if ($this->wasRequested()) {
            return; // already requested
        }

        $this->storage = [
            'requestTime' => time(),
            'dismissed' => [],
            'requestedBy' => Piwik::getCurrentUserLogin(),
        ];
        $this->saveStorage();

        $this->sendEmailToSuperUsers();
    }


    /**
     * Returns if a plugin was already requested
     *
     * @return bool
     */
    public function wasRequested(): bool
    {
        if (!self::isEnabled()) {
            return false;
        }

        if (empty($this->storage)) {
            return false;
        }

        $expirationTime = (int) GeneralConfig::getConfigValue('plugin_trial_request_expiration_in_days');

        if ($this->storage['requestTime'] < (time() - $expirationTime * 24 * 3600)) {
            $this->clearStorage(); // remove outdated request
            return false;
        }

        return true;
    }

    public function setNotificationDismissed()
    {
        if (!self::isEnabled()) {
            return;
        }

        $this->storage['dismissed'][] = Piwik::getCurrentUserLogin();
        $this->saveStorage();
    }

    public static function createNotificationsIfNeeded(): void
    {
        if (!self::isEnabled()) {
            return;
        }

        $trialRequests = Option::getLike(sprintf(self::OPTION_NAME, '%'));

        foreach ($trialRequests as $trialRequest => $data) {
            $pluginName = str_replace(sprintf(self::OPTION_NAME, ''), '', $trialRequest);

            $trialRequest = new self($pluginName);
            $trialRequest->createNotificationIfNeeded();
        }
    }

    public static function dismissNotificationIfNeeded($notificationId): void
    {
        if (!self::isEnabled()) {
            return;
        }

        if (!Piwik::hasUserSuperUserAccess()) {
            return; // only super users can see and dismiss those notifications
        }

        if (strpos($notificationId, 'Marketplace_PluginTrialRequest_') !== 0) {
            return; // Ignore other notifications
        }

        $notificationParts = explode('_', $notificationId, 4);

        if (count($notificationParts) !== 4) {
            return; // unable to parse notification id
        }

        $pluginName = $notificationParts[2];
        $userLogin = $notificationParts[3];

        if ($userLogin !== Piwik::getCurrentUserLogin()) {
            return; // Don't allow to unset notifications for other users
        }

        $pluginTrial = new self($pluginName);
        $pluginTrial->setNotificationDismissed();
    }

    public static function isEnabled()
    {
        return -1 !== (int) GeneralConfig::getConfigValue('plugin_trial_request_expiration_in_days');
    }

    public function createNotificationIfNeeded(): void
    {
        if (!self::isEnabled()) {
            return;
        }

        if (!$this->wasRequested()) {
            return;
        }

        if (!Piwik::hasUserSuperUserAccess()) {
            return; // Notification only displayed to super users
        }

        if (!empty($this->storage['dismissed']) && in_array(Piwik::getCurrentUserLogin(), $this->storage['dismissed'])) {
            return; // current user already dismissed the notification
        }

        $notification = new Notification(Piwik::translate('Marketplace_PluginTrialRequested'));
        $notification->raw = true;
        $notification->context = Notification::CONTEXT_INFO;
        $notification->type = Notification::TYPE_PERSISTENT;
        Notification\Manager::notify($this->getNotificationId(), $notification);
    }

    private function getNotificationId()
    {
        return sprintf('Marketplace_PluginTrialRequest_%s_%s', $this->pluginName, Piwik::getCurrentUserLogin());
    }

    private function loadStorage(): void
    {
        $this->storage = json_decode(Option::get($this->optionName) ?: '', true);
    }

    private function saveStorage(): void
    {
        Option::set($this->optionName, json_encode($this->storage));
    }

    private function clearStorage(): void
    {
        Option::delete($this->optionName);
    }

    /**
     * Send notification email to all super users
     *
     * @return void
     */
    private function sendEmailToSuperUsers(): void
    {
        $superUsers = Piwik::getAllSuperUserAccessEmailAddresses();

        foreach ($superUsers as $login => $email) {
            $email = StaticContainer::getContainer()->make(
                RequestTrialNotificationEmail::class,
                [
                    'emailAddress' => $email,
                    'login' => $login,
                    'pluginName' => $this->pluginName,
                ]
            );

            $email->safeSend();
        }
    }
}
