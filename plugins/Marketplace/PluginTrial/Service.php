<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\PluginTrial;

use Exception;
use Piwik\Config\GeneralConfig;
use Piwik\Container\StaticContainer;
use Piwik\Notification\Manager as NotificationManager;
use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Plugins\FeatureFlags\FeatureFlagManager;
use Piwik\Plugins\Marketplace\FeatureFlags\PluginRequests;
use Piwik\Request\AuthenticationToken;
use Piwik\Session;
use Piwik\Session\SessionNamespace;

final class Service
{
    private const SESSION_NAMESPACE = 'Marketplace.PluginTrial';

    // bounds how long a notification the session dropped, or one built for another site or language, can stay so
    private const MAX_SECONDS_BETWEEN_CHECKS = 600;

    // a check finding a different list replaces the notice, and a page can still display any of the last few
    private const MAX_REMEMBERED_NOTICES = 5;

    private ?string $pluginBeingApproved = null;

    public function __construct()
    {
    }

    /**
     * Creates a trial or install request (and sends a mail to all super users)
     *
     * @param string $requestType one of the RequestHistory::REQUEST_TYPE_* constants
     */
    public function request(
        string $pluginName,
        string $pluginDisplayName,
        string $requestType = RequestHistory::REQUEST_TYPE_TRIAL,
        ?string $message = null
    ): void {
        if (!$this->isEnabled()) {
            return;
        }

        $request = new Request($pluginName, new Storage($pluginName));
        $request->create($pluginDisplayName, $requestType, $message);
    }

    /**
     * Approves or declines all of a plugin's pending requests as the current super user
     *
     * @param string $decision one of the RequestHistory::DECISION_* constants
     * @param bool $requirePending false returns 0 when none of the plugin's requests is pending, instead of failing
     * @return int the number of requests decided
     * @throws Exception when none of the plugin's requests is pending
     */
    public function decide(string $pluginName, string $decision, ?string $note = null, bool $requirePending = true): int
    {
        Piwik::checkUserHasSuperUserAccess();

        $request = new Request($pluginName, new Storage($pluginName));
        $numRequests = $request->decide($decision, $note, $requirePending);
        $this->removeNotificationFromSession($pluginName);

        return $numRequests;
    }

    /**
     * Activates a requested plugin, and approves its pending requests as the current super user once it is active, so
     * requests stay pending when activating fails
     *
     * @return int|null the number of requesters emailed about the approval, or null when the requests had already been
     *                  decided on, eg by another super user or a repeated install request
     */
    public function activateAndApprove(string $pluginName): ?int
    {
        Piwik::checkUserHasSuperUserAccess();

        // activating approves the requests on its own, with no super user named, see cancelRequest()
        $this->pluginBeingApproved = $pluginName;
        try {
            Manager::getInstance()->activatePlugin($pluginName);
        } finally {
            $this->pluginBeingApproved = null;
        }

        $request = new Request($pluginName, new Storage($pluginName));
        $numDecided = $request->decide(RequestHistory::DECISION_APPROVED, null, false);
        $this->removeNotificationFromSession($pluginName);
        if ($numDecided === 0) {
            return null;
        }

        return $request->getNumNotified();
    }

    private function removeNotificationFromSession(string $pluginName): void
    {
        if (Session::isStarted()) {
            (new Notification($pluginName, new Storage($pluginName)))->removeFromSession();
            // rebuilt without the decided plugin on the next page view
            RequestsNotification::removeFromSession();
        }
    }


    /**
     * Returns if the current user has already requested a plugin
     */
    public function wasRequested(string $pluginName): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $request = new Request($pluginName, new Storage($pluginName));
        return $request->wasRequested();
    }

    /**
     * Returns if any user's request for a plugin is pending, so super users were already notified of it
     */
    public function isPending(string $pluginName): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        return (new Storage($pluginName))->wasRequested();
    }

    /**
     * Returns if any of a plugin's requests is still to be decided, including one recorded without making the plugin
     * pending, see Storage::hasPendingRequests()
     */
    public function hasPendingRequests(string $pluginName): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        return (new Storage($pluginName))->hasPendingRequests();
    }

    /**
     * Returns if any user has ever requested the plugin, whether the request is still pending or was decided
     */
    public function hasRequests(string $pluginName): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        return (new Storage($pluginName))->wasRequested()
            || StaticContainer::get(RequestHistory::class)->getRequests($pluginName) !== [];
    }

    /**
     * Creates the notifications of plugin requests for super users, and while requests are active, of decisions for
     * the users who requested
     *
     * @throws Exception
     */
    public function createNotificationsIfNeeded(): void
    {
        // a timed out session keeps its data, so the next person on that browser, even anonymous, inherits its notices
        $session = self::isSessionEnabled() ? new SessionNamespace(self::SESSION_NAMESPACE) : null;
        if ($session !== null && $session->notifiedLogin !== Piwik::getCurrentUserLogin()) {
            self::removeNoticesFromSession();
            DecisionNotifications::removeFromSession();
            $session->notifiedLists = [];
            $session->notifiedLogin = Piwik::getCurrentUserLogin();
            $session->checkedLogin = null;
        }

        $requestsActive = $this->areRequestsActive();
        if ($session !== null && !$requestsActive) {
            // persistent, so one shown while requests were active would otherwise stay
            DecisionNotifications::removeFromSession();
        }
        if ($session !== null && (!$this->isEnabled() || !Piwik::hasUserSuperUserAccess())) {
            // also persistent, so they would outlive the user's super user access or the feature being turned off
            self::removeNoticesFromSession();
        }

        if (!$this->isEnabled() || Piwik::isUserIsAnonymous()) {
            return;
        }

        $isSuperUser = Piwik::hasUserSuperUserAccess();
        if (!$isSuperUser && !$requestsActive) {
            return; // requesters are only told of decisions while requests are active
        }

        // this runs on every request, so it is skipped while no request has changed since this session last checked
        $changeMarker = Storage::getChangeMarker();
        if (
            $session !== null
            && $session->checkedMarker === $changeMarker
            && $session->checkedLogin === Piwik::getCurrentUserLogin()
            // turning requests on or off, or a promotion, changes which notices are due without changing a request
            && $session->checkedRequestsActive === $requestsActive
            && $session->checkedIsSuperUser === $isSuperUser
            && $session->checkedAt > time() - self::MAX_SECONDS_BETWEEN_CHECKS
            // activating a plugin outside the plugin manager, eg in the config, changes no request
            && !array_filter((array) $session->checkedPlugins, [Manager::getInstance(), 'isPluginActivated'])
        ) {
            return;
        }

        // looked up at most once, and only if a check needs them
        $superUsers = null;
        $getSuperUsers = function () use (&$superUsers): array {
            return $superUsers ??= Storage::getSuperUserLogins();
        };
        $allChecked = true;
        $checkedPlugins = [];

        if ($isSuperUser) {
            $requested = [];

            foreach (Storage::getAllInStorage() as $pluginName => $storage) {
                if (Manager::getInstance()->isPluginActivated($pluginName)) {
                    try {
                        $this->cancelRequest($pluginName);
                    } catch (\Throwable $e) {
                        // retried on the next page view, and must not hide the other plugins' notifications
                        $allChecked = false;
                    }
                    continue;
                }

                $checkedPlugins[] = $pluginName;

                $trialRequest = new Notification($pluginName, $storage);
                if (!$requestsActive) {
                    $trialRequest->createNotificationIfNeeded($getSuperUsers);
                    continue;
                }

                // one shown before requests were active
                $trialRequest->removeFromSession();
                if ($storage->wasRequested() && !$storage->isNotificationDismissed($getSuperUsers)) {
                    $requested[$pluginName] = $storage->getDisplayName();
                }
            }

            if (!$requestsActive) {
                // its link leads to a page that is off now
                RequestsNotification::removeFromSession();
            } else {
                $noticeId = RequestsNotification::notify($requested);
                if ($session !== null && $noticeId !== null) {
                    // dismissing a notice dismisses only the plugins it listed, not one requested since
                    $lists = is_array($session->notifiedLists) ? $session->notifiedLists : [];
                    unset($lists[$noticeId]);
                    $lists[$noticeId] = array_keys($requested);
                    $session->notifiedLists = array_slice($lists, -self::MAX_REMEMBERED_NOTICES, null, true);
                }
            }
        }

        // without a session no page displays the notices
        if ($requestsActive && $session !== null) {
            // an approval waits for its plugin to be activated, which changes no request either
            $checkedPlugins = array_merge($checkedPlugins, DecisionNotifications::createIfNeeded());
        }

        // a check that needed the current super users can change with them, and demoting one changes no request
        if ($session !== null && $allChecked && $superUsers === null) {
            $session->checkedMarker = $changeMarker;
            $session->checkedLogin = Piwik::getCurrentUserLogin();
            $session->checkedRequestsActive = $requestsActive;
            $session->checkedIsSuperUser = $isSuperUser;
            $session->checkedPlugins = $checkedPlugins;
            $session->checkedAt = time();
        }
    }

    /**
     * Dismisses a plugin trial notification for the current (super) user if the provided notification id matches a
     * plugin trial request.
     *
     * @throws Exception
     */
    public function dismissNotification(string $notificationId): void
    {
        if (!$this->isEnabled()) {
            return;
        }

        // the event that calls this fires before CoreHome checks the token, so a forged request must not store anything
        $tokenRequest = StaticContainer::get(AuthenticationToken::class)->getAuthToken();
        $tokenUser = Piwik::getCurrentUserTokenAuth();
        if ((!empty($tokenRequest) || !empty($tokenUser)) && $tokenRequest !== $tokenUser) {
            return;
        }

        if (!Piwik::isUserIsAnonymous() && DecisionNotifications::dismiss($notificationId)) {
            return;
        }

        if (!Piwik::hasUserSuperUserAccess()) {
            return; // only super users can see and dismiss the other notifications
        }

        if (RequestsNotification::isOwnId($notificationId)) {
            $this->dismissRequestsNotification($notificationId);
            return;
        }

        if (!str_starts_with($notificationId, 'Marketplace_PluginTrialRequest_')) {
            return; // Ignore other notifications
        }

        $notificationParts = explode('_', $notificationId, 4);

        if (count($notificationParts) !== 4) {
            return; // unable to parse notification id
        }

        $userLogin = $notificationParts[2];
        $pluginName = $notificationParts[3];

        if ($userLogin !== md5(Piwik::getCurrentUserLogin())) {
            return; // Don't allow to unset notifications for other users
        }

        $pluginTrial = new Notification($pluginName, new Storage($pluginName));
        $pluginTrial->setNotificationDismissed();
    }

    private static function removeNoticesFromSession(): void
    {
        $prefixes = [
            'Marketplace_PluginTrialRequest_',
            'Marketplace_PluginRequests_',
        ];
        foreach (array_keys(NotificationManager::getAllNotificationsToDisplay()) as $id) {
            foreach ($prefixes as $prefix) {
                if (str_starts_with((string) $id, $prefix)) {
                    NotificationManager::cancel($id);
                    break;
                }
            }
        }
    }

    private function dismissRequestsNotification(string $notificationId): void
    {
        $session = self::isSessionEnabled() ? new SessionNamespace(self::SESSION_NAMESPACE) : null;
        if ($session === null) {
            // without a session the notice was built by the request that displayed it, from what is pending now
            $pluginNames = Storage::getPluginsInStorage();
        } else {
            // a check since the page was displayed, eg on an AJAX request, can have replaced the notice
            $lists = is_array($session->notifiedLists) ? $session->notifiedLists : [];
            $pluginNames = $lists[$notificationId] ?? [];
            unset($lists[$notificationId]);
            $session->notifiedLists = $lists;
        }

        foreach ($pluginNames as $pluginName) {
            $storage = new Storage($pluginName);
            if ($storage->wasRequested() && !$storage->isNotificationDismissed()) {
                $storage->setNotificationDismissed();
            }
        }

        RequestsNotification::removeFromSession();
    }

    /**
     * Cancels a plugin trial request
     *
     * @throws Exception
     */
    public function cancelRequest(string $pluginName): void
    {
        if ($pluginName === $this->pluginBeingApproved) {
            return;
        }

        $request = new Request($pluginName, new Storage($pluginName));
        $request->cancel();

        if (Piwik::hasUserSuperUserAccess() && Session::isStarted()) {
            $notification = new Notification($pluginName, new Storage($pluginName));
            $notification->removeFromSession();
            RequestsNotification::removeFromSession();
        }
    }

    /**
     * Without a session the notifications are not kept between requests, so every request has to check again
     */
    private static function isSessionEnabled(): bool
    {
        return Session::isWritable() && Session::isReadable();
    }

    public function isEnabled(): bool
    {
        return -1 !== GeneralConfig::getIntegerConfigValue('plugin_trial_request_expiration_in_days', 0);
    }

    /**
     * Returns if users can request free plugins and follow their requests: the PluginRequests feature is on, and
     * requests are not turned off in the config
     */
    public function areRequestsActive(): bool
    {
        return $this->isEnabled()
            && StaticContainer::get(FeatureFlagManager::class)->isFeatureActive(PluginRequests::class);
    }
}
