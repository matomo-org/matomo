<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\PluginTrial;

use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Notification as MatomoNotification;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\Marketplace\Api\Client;
use Piwik\Plugins\Marketplace\SiteAwareLinks;
use Piwik\Plugins\UsersManager\Model as UsersModel;

/**
 * Tells a requester that their requests were approved or declined, until they dismiss the notice
 */
final class DecisionNotifications
{
    private const SEEN_OPTION_NAME = 'Marketplace.PluginRequestDecisionsSeen.%s';
    private const DISMISSED_OPTION_NAME = 'Marketplace.PluginRequestDecisionsDismissed.%s';
    private const BASELINE_OPTION_NAME = 'Marketplace.PluginRequestDecisionsBaseline';
    public const APPROVED_ID_PREFIX = 'Marketplace_PluginRequestsApproved_';
    public const DECLINED_ID_PREFIX = 'Marketplace_PluginRequestDeclined_';

    /**
     * Shows the current user's decisions they have not dismissed. The notices are persistent, so a page that does not
     * display them, or one that clears the transient notices, does not lose them.
     */
    public static function createIfNeeded(): void
    {
        $decisions = self::getUndismissedDecisions(Piwik::getCurrentUserLogin());

        $notices = [];
        $displayNames = self::getDisplayNames(array_values(array_unique(array_column($decisions, 'plugin_name'))));
        $approved = [];
        $lastApprovedId = 0;

        foreach ($decisions as $decision) {
            $displayName = '<b>' . htmlentities($displayNames[$decision['plugin_name']]) . '</b>';

            if ($decision['decision'] === RequestHistory::DECISION_APPROVED) {
                $approved[$decision['plugin_name']] = $displayName;
                $lastApprovedId = $decision['iddecision'];
                continue;
            }

            $notices[self::DECLINED_ID_PREFIX . $decision['iddecision']] = self::createNotification(
                Piwik::translate('Marketplace_PluginRequestDeclinedNotification', [
                    $displayName,
                    sprintf('<a href="%s">', (new SiteAwareLinks())->getActionUrl('manageRequests')),
                    '</a>',
                ]),
                MatomoNotification::CONTEXT_INFO
            );
        }

        if (!empty($approved)) {
            // carries the newest approval, so dismissing it dismisses only the approvals it listed
            $notices[self::APPROVED_ID_PREFIX . $lastApprovedId] = self::createNotification(
                count($approved) === 1
                    ? Piwik::translate('Marketplace_PluginRequestApprovedNotification', [reset($approved)])
                    : Piwik::translate('Marketplace_PluginRequestsApprovedNotification', [
                        RequestsNotification::joinNames(array_values($approved)),
                    ]),
                MatomoNotification::CONTEXT_SUCCESS
            );
        }

        // one dismissed in another session, or an approval notice that a newer approval replaced
        foreach (self::getQueuedIds() as $id) {
            if (!isset($notices[$id])) {
                MatomoNotification\Manager::cancel($id);
            }
        }

        foreach ($notices as $id => $notification) {
            MatomoNotification\Manager::notify($id, $notification);
        }
    }

    /**
     * Marks the decisions a notice of the current user listed as dismissed
     *
     * @return bool false when the ID is not a decision notice's
     */
    public static function dismiss(string $notificationId): bool
    {
        if (str_starts_with($notificationId, self::APPROVED_ID_PREFIX)) {
            $isApproval = true;
            $idDecision = (int) substr($notificationId, strlen(self::APPROVED_ID_PREFIX));
        } elseif (str_starts_with($notificationId, self::DECLINED_ID_PREFIX)) {
            $isApproval = false;
            $idDecision = (int) substr($notificationId, strlen(self::DECLINED_ID_PREFIX));
        } else {
            return false;
        }

        $login = Piwik::getCurrentUserLogin();
        // two notices closed in quick succession arrive as parallel requests, and each would overwrite the other's dismissal
        $lockName = self::getLockName($login);
        if (!Db::getDbLock($lockName)) {
            return true; // the notice comes back on the next page, to be dismissed again
        }
        try {
            self::markDismissed($login, $isApproval, $idDecision);
        } finally {
            Db::releaseDbLock($lockName);
        }

        return true;
    }

    private static function getLockName(string $login): string
    {
        return 'MarketplaceRequestDecisions.' . md5($login);
    }

    /**
     * @phpstan-impure the user may be deleted by another request between two calls
     */
    private static function userExists(string $login): bool
    {
        return (new UsersModel())->userExists($login);
    }

    private static function markDismissed(string $login, bool $isApproval, int $idDecision): void
    {
        if (!self::userExists($login)) {
            return; // deleted while this request was on its way, so nothing may be stored for them again
        }

        // this request may have read them before another one changed them
        Option::clearCachedOption(sprintf(self::SEEN_OPTION_NAME, $login));
        Option::clearCachedOption(sprintf(self::DISMISSED_OPTION_NAME, $login));

        $seen = self::getSeen($login);
        if ($seen === null) {
            return;
        }

        // only the current user's own decisions are read, so no other user's notice can be dismissed
        $decisions = StaticContainer::get(RequestHistory::class)->getDecisionsOfLogin($login, $seen);
        $dismissed = self::getDismissed($login);

        foreach ($decisions as $decision) {
            $id = (int) $decision['iddecision'];
            $isListed = $isApproval
                ? $decision['decision'] === RequestHistory::DECISION_APPROVED && $id <= $idDecision
                : $id === $idDecision;
            if ($isListed) {
                $dismissed[] = $id;
            }
        }

        // the cursor passes the dismissed decisions in a row, so only those after one still shown are kept in the list
        foreach ($decisions as $decision) {
            if (!in_array((int) $decision['iddecision'], $dismissed, true)) {
                break;
            }
            $seen = (int) $decision['iddecision'];
        }
        $dismissed = array_unique(array_filter($dismissed, function (int $id) use ($seen): bool {
            return $id > $seen;
        }));

        Option::set(sprintf(self::SEEN_OPTION_NAME, $login), (string) $seen);
        if ($dismissed === []) {
            Option::delete(sprintf(self::DISMISSED_OPTION_NAME, $login));
        } else {
            Option::set(sprintf(self::DISMISSED_OPTION_NAME, $login), implode(',', $dismissed));
        }

        // a deletion that gave up waiting for the lock may have run since the check above
        if (!self::userExists($login)) {
            Option::delete(sprintf(self::SEEN_OPTION_NAME, $login));
            Option::delete(sprintf(self::DISMISSED_OPTION_NAME, $login));
        }
    }

    /**
     * @return array<int, array{plugin_name: string, iddecision: int|string, decision: string}>
     */
    private static function getUndismissedDecisions(string $login): array
    {
        $seen = self::getSeen($login);
        if ($seen === null) {
            return [];
        }

        $dismissed = self::getDismissed($login);
        $decisions = StaticContainer::get(RequestHistory::class)->getDecisionsOfLogin($login, $seen);

        return array_filter($decisions, function (array $decision) use ($dismissed): bool {
            return !in_array((int) $decision['iddecision'], $dismissed, true);
        });
    }

    /**
     * The newest decision that the user dismissed along with every earlier one, or null before decisions can be read
     */
    private static function getSeen(string $login): ?int
    {
        $seen = Option::get(sprintf(self::SEEN_OPTION_NAME, $login));

        return $seen === false ? self::getBaseline() : (int) $seen;
    }

    /**
     * @return int[] the decisions dismissed after one the user has not dismissed yet
     */
    private static function getDismissed(string $login): array
    {
        $dismissed = Option::get(sprintf(self::DISMISSED_OPTION_NAME, $login));

        return empty($dismissed) ? [] : array_map('intval', explode(',', $dismissed));
    }

    /**
     * The newest decision when decisions were first looked for, so a requester is not told of the decisions recorded
     * before then, such as the approvals the 6.1.0-b1 update recorded for every request made before it
     */
    private static function getBaseline(): ?int
    {
        $baseline = Option::get(self::BASELINE_OPTION_NAME);
        if ($baseline !== false) {
            return (int) $baseline;
        }

        $baseline = StaticContainer::get(RequestHistory::class)->getLastDecisionId();
        if ($baseline !== null) {
            Option::set(self::BASELINE_OPTION_NAME, (string) $baseline);
        }

        return $baseline;
    }

    public static function removeFromSession(): void
    {
        foreach (self::getQueuedIds() as $id) {
            MatomoNotification\Manager::cancel($id);
        }
    }

    /**
     * @return string[]
     */
    private static function getQueuedIds(): array
    {
        return array_values(array_filter(
            array_map('strval', array_keys(MatomoNotification\Manager::getAllNotificationsToDisplay())),
            function (string $id): bool {
                return str_starts_with($id, self::DECLINED_ID_PREFIX) || str_starts_with($id, self::APPROVED_ID_PREFIX);
            }
        ));
    }

    public static function forgetLogin(string $login): void
    {
        // waits for a dismissal in progress, which would otherwise store its options again after these deletes
        $lockName = self::getLockName($login);
        $isLocked = Db::getDbLock($lockName);
        try {
            Option::delete(sprintf(self::SEEN_OPTION_NAME, $login));
            Option::delete(sprintf(self::DISMISSED_OPTION_NAME, $login));
        } finally {
            if ($isLocked) {
                Db::releaseDbLock($lockName);
            }
        }
    }

    private static function createNotification(string $message, string $context): MatomoNotification
    {
        $notification = new MatomoNotification($message);
        $notification->raw = true;
        $notification->context = $context;
        $notification->type = MatomoNotification::TYPE_PERSISTENT;

        return $notification;
    }

    /**
     * Only from the cached Marketplace lists, as this runs on a page view
     *
     * @param string[] $pluginNames
     * @return array<string, string> keyed by plugin name
     */
    private static function getDisplayNames(array $pluginNames): array
    {
        if ($pluginNames === []) {
            return [];
        }

        $pluginInfos = StaticContainer::get(Client::class)->getInfoOfPlugins($pluginNames, false);

        $displayNames = [];
        foreach ($pluginNames as $pluginName) {
            $displayNames[$pluginName] = ($pluginInfos[$pluginName]['displayName'] ?? '') ?: $pluginName;
        }

        return $displayNames;
    }
}
