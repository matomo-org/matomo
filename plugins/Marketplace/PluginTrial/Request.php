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
use Piwik\Log\LoggerInterface;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Plugins\FeatureFlags\FeatureFlagManager;
use Piwik\Plugins\Marketplace\FeatureFlags\PluginRequests;
use Piwik\Plugins\Marketplace\Emails\PluginRequestDecidedEmail;
use Piwik\Plugins\Marketplace\Emails\RequestTrialNotificationEmail;
use Piwik\Plugins\Marketplace\UserSettings;
use Piwik\Settings\Storage\UserScopedSettingsAccessManager;

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
     * Creates a trial or install request, and sends a mail to all super users unless the plugin already had one pending
     *
     * @param string $requestType one of the RequestHistory::REQUEST_TYPE_* constants
     */
    public function create(
        string $pluginDisplayName = '',
        string $requestType = RequestHistory::REQUEST_TYPE_TRIAL,
        ?string $message = null
    ): void {
        if ($this->wasRequested()) {
            return; // already requested
        }

        $result = $this->storage->setRequested($pluginDisplayName, $requestType, $message);

        // a concurrent request by the same user got there first
        if ($result === Storage::REQUEST_ALREADY_RECORDED) {
            return;
        }

        if ($result === Storage::REQUEST_PENDING) {
            // sent before the event is posted, so a failing observer cannot stop it; safeSend() logs a failed send rather than throwing
            $this->sendEmailToSuperUsers();
        }

        if ($requestType === RequestHistory::REQUEST_TYPE_INSTALL) {
            /**
             * Triggered after the current user has asked for a free plugin to be installed, so an audit trail can
             * record it.
             *
             * **Example**
             *
             *     Piwik::addAction('Marketplace.pluginInstallRequested', function ($pluginName, $pluginDisplayName) {
             *         $this->logActivity(Piwik::getCurrentUserLogin(), 'requested to install ' . $pluginName);
             *     });
             *
             * @param string $pluginName The name of the requested plugin.
             * @param string $pluginDisplayName The plugin's display name, or an empty string when none was given.
             */
            Piwik::postEvent('Marketplace.pluginInstallRequested', [$this->pluginName, $pluginDisplayName]);
            return;
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
        if (!$this->storage->hasPendingRequests()) {
            return; // not requested
        }

        $decided = $this->storage->setFulfilled();
        if ($decided !== null) {
            $this->announceDecision($decided, RequestHistory::DECISION_APPROVED, null);
        }
    }

    /**
     * Approves or declines all of the plugin's pending requests as the current super user
     *
     * @param string $decision one of the RequestHistory::DECISION_* constants
     * @param bool $requirePending false returns 0 when another super user has already decided on them, instead of failing
     * @return int the number of requests decided
     * @throws Exception when another super user has already decided on them
     */
    public function decide(string $decision, ?string $note = null, bool $requirePending = true): int
    {
        $decided = $this->storage->setDecided($decision, $note, $requirePending);
        if ($decided === null) {
            return 0;
        }

        $this->announceDecision($decided, $decision, $note);

        return $decided->numRequests;
    }

    /**
     * Run once the decision is committed, so neither a failing email nor a failing observer undoes it
     */
    private function announceDecision(Decision $decided, string $decision, ?string $note): void
    {
        // sent before the event is posted, so a failing observer cannot stop it. The decision is saved by now, so a
        // failure to email it is logged rather than stopping the event, the answer to the decision or an activation
        try {
            $this->sendDecisionEmails($decided, $decision, $note);
        } catch (\Throwable $e) {
            StaticContainer::get(LoggerInterface::class)->error(
                'Could not email the requesters of {plugin} about the decision: {exception}',
                ['plugin' => $this->pluginName, 'exception' => $e]
            );
        }

        $numRequests = $decided->numRequests;

        /**
         * Triggered after a plugin's pending requests were approved or declined, so an audit trail can record it. A
         * plugin activated by any means approves its pending requests, with no super user logged in when activated
         * from the command line.
         *
         * **Example**
         *
         *     Piwik::addAction('Marketplace.pluginRequestDecided', function ($pluginName, $decision, $numRequests) {
         *         $this->logActivity(Piwik::getCurrentUserLogin(), $decision . ' ' . $numRequests . ' requests for ' . $pluginName);
         *     });
         *
         * @param string $pluginName The name of the requested plugin.
         * @param string $decision `approved` or `declined`.
         * @param int $numRequests The number of requests the decision ended.
         */
        Piwik::postEvent('Marketplace.pluginRequestDecided', [$this->pluginName, $decision, $numRequests]);
    }


    /**
     * Returns if the current user has already requested the plugin
     */
    public function wasRequested(): bool
    {
        return $this->storage->wasRequestedByCurrentUser();
    }

    /**
     * Sends each requester who still has an account, and has not turned these emails off, one email about the decision
     */
    private function sendDecisionEmails(Decision $decided, string $decision, ?string $note): void
    {
        // activating a plugin approves its requests whether or not the flag is on, which only then tells the requesters
        if (!StaticContainer::get(FeatureFlagManager::class)->isFeatureActive(PluginRequests::class)) {
            return;
        }

        $requesters = StaticContainer::get(RequestHistory::class)->getRequesterEmails($decided);
        if (empty($requesters)) {
            return;
        }

        $settings = StaticContainer::get(UserScopedSettingsAccessManager::class)
            ->getValuesForAllUsers('Marketplace', [UserSettings::NOTIFY_ON_PLUGIN_REQUEST_DECISION]);

        foreach ($requesters as $login => $emailAddress) {
            if (!($settings[$login][UserSettings::NOTIFY_ON_PLUGIN_REQUEST_DECISION] ?? true)) {
                continue;
            }

            $email = StaticContainer::getContainer()->make(
                PluginRequestDecidedEmail::class,
                [
                    'login' => (string) $login,
                    'emailAddress' => $emailAddress,
                    'pluginName' => $this->pluginName,
                    'pluginDisplayName' => $this->storage->getDisplayName(),
                    'decision' => $decision,
                    'note' => $note,
                ]
            );

            $email->safeSend();
        }
    }

    /**
     * Send notification email to all super users who have not turned these emails off
     */
    private function sendEmailToSuperUsers(): void
    {
        $superUsers = Piwik::getAllSuperUserAccessEmailAddresses();
        $settings = StaticContainer::get(UserScopedSettingsAccessManager::class)
            ->getValuesForAllUsers('Marketplace', [UserSettings::NOTIFY_ON_PLUGIN_REQUEST]);

        foreach ($superUsers as $login => $email) {
            if (!($settings[$login][UserSettings::NOTIFY_ON_PLUGIN_REQUEST] ?? true)) {
                continue;
            }

            // like safeSend(), a failure for one recipient is logged so it cannot stop the others or the event
            try {
                // null when the user was deleted since the lookup above
                $unsubscribeToken = StaticContainer::get(UnsubscribeToken::class)->generate((string) $login);
                if ($unsubscribeToken === null) {
                    continue;
                }

                $email = StaticContainer::getContainer()->make(
                    RequestTrialNotificationEmail::class,
                    [
                        'emailAddress' => $email,
                        'login' => $login,
                        'unsubscribeToken' => $unsubscribeToken,
                        'pluginName' => $this->pluginName,
                        'pluginDisplayName' => $this->storage->getDisplayName(),
                        'requestType' => $this->storage->getRequestType(),
                    ]
                );
            } catch (Exception $e) {
                StaticContainer::get(LoggerInterface::class)->warning(
                    'Could not prepare the plugin trial request email for {login}: {exception}',
                    ['login' => $login, 'exception' => $e]
                );
                continue;
            }

            $email->safeSend();
        }
    }
}
