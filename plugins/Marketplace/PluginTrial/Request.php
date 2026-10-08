<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\PluginTrial;

use Exception;
use Piwik\Container\StaticContainer;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Plugins\Marketplace\Emails\RequestTrialNotificationEmail;

class Request
{
    private string $pluginName;

    private Storage $storage;

    public function __construct(string $pluginName, Storage $storage)
    {
        if (!Manager::getInstance()->isValidPluginName($pluginName)) {
            throw new Exception('Invalid plugin name given ' . $pluginName);
        }

        $this->pluginName = $pluginName;
        $this->storage = $storage;
    }

    /**
     * Creates a trial request, and sends a mail to all super users unless the plugin already had one pending
     */
    public function create(string $pluginDisplayName = ''): void
    {
        if ($this->wasRequested()) {
            return; // already requested
        }

        $result = $this->storage->setRequested($pluginDisplayName);

        // a concurrent request by the same user got there first
        if ($result === Storage::REQUEST_ALREADY_RECORDED) {
            return;
        }

        if ($result === Storage::REQUEST_PENDING) {
            // sent before the event is posted, so a failing observer cannot stop it; safeSend() logs a failed send rather than throwing
            $this->sendEmailToSuperUsers();
        }

        /**
         * Triggered after the current user has requested a trial of a plugin, so an audit trail can record it.
         *
         * **Example**
         *
         *     Piwik::addAction('Marketplace.pluginTrialRequested', function ($pluginName, $pluginDisplayName) {
         *         $this->logActivity(Piwik::getCurrentUserLogin(), 'requested a trial of ' . $pluginName);
         *     });
         *
         * @param string $pluginName The name of the requested plugin.
         * @param string $pluginDisplayName The plugin's display name, or an empty string when none was given.
         */
        Piwik::postEvent('Marketplace.pluginTrialRequested', [$this->pluginName, $pluginDisplayName]);
    }

    /**
     * Ends a pending trial request because the plugin was installed or activated
     */
    public function cancel(): void
    {
        if (!$this->storage->wasRequested()) {
            return; // not requested
        }

        $this->storage->setFulfilled();
    }


    /**
     * Returns if the current user has already requested the plugin
     */
    public function wasRequested(): bool
    {
        return $this->storage->wasRequestedByCurrentUser();
    }

    /**
     * Send notification email to all super users
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
                    'pluginDisplayName' => $this->storage->getDisplayName(),
                ]
            );

            $email->safeSend();
        }
    }
}
