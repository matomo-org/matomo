<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\PluginTrial;

use Piwik\Container\StaticContainer;
use Piwik\Notification as MatomoNotification;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\Marketplace\Api\Client;
use Piwik\Plugins\Marketplace\SiteAwareLinks;
use Piwik\Session\SessionNamespace;

/**
 * Tells a requester, once, that their requests were approved or declined
 */
final class DecisionNotifications
{
    private const SEEN_OPTION_NAME = 'Marketplace.PluginRequestDecisionsSeen.%s';
    private const BASELINE_OPTION_NAME = 'Marketplace.PluginRequestDecisionsBaseline';
    public const APPROVED_ID_PREFIX = 'Marketplace_PluginRequestsApproved_';
    public const DECLINED_ID_PREFIX = 'Marketplace_PluginRequestDeclined_';

    /**
     * Shows the decisions on the current user's requests made since they were last shown any. Transient notices are
     * kept in the session until a page displays them, so the caller must only call this while the session is writable.
     */
    public static function createIfNeeded(?SessionNamespace $session): void
    {
        $login = Piwik::getCurrentUserLogin();
        $optionName = sprintf(self::SEEN_OPTION_NAME, $login);

        $seen = Option::get($optionName);
        if ($seen === false) {
            $seen = self::getBaseline();
            if ($seen === null) {
                return;
            }
        }

        $decisions = StaticContainer::get(RequestHistory::class)->getDecisionsOfLogin($login, (int) $seen);
        if (empty($decisions)) {
            return;
        }

        // while an earlier notice is undisplayed, removing it has to bring back its decisions too
        if ($session !== null && ($session->decisionsLogin !== $login || self::getQueuedIds() === [])) {
            $session->decisionsLogin = $login;
            $session->decisionsSeenBefore = (string) $seen;
        }

        $displayNames = self::getDisplayNames(array_column($decisions, 'plugin_name'));
        $approved = [];
        $lastApprovedId = 0;

        foreach ($decisions as $decision) {
            $displayName = '<b>' . htmlentities($displayNames[$decision['plugin_name']]) . '</b>';

            if ($decision['decision'] === RequestHistory::DECISION_APPROVED) {
                $approved[$decision['plugin_name']] = $displayName;
                $lastApprovedId = $decision['iddecision'];
                continue;
            }

            self::notify(
                self::DECLINED_ID_PREFIX . $decision['plugin_name'],
                Piwik::translate('Marketplace_PluginRequestDeclinedNotification', [
                    $displayName,
                    sprintf('<a href="%s">', (new SiteAwareLinks())->getActionUrl('manageRequests')),
                    '</a>',
                ]),
                MatomoNotification::CONTEXT_INFO
            );
        }

        if (!empty($approved)) {
            self::notify(
                // a check on an AJAX request can come before the page that shows an earlier check's notice
                self::APPROVED_ID_PREFIX . $lastApprovedId,
                count($approved) === 1
                    ? Piwik::translate('Marketplace_PluginRequestApprovedNotification', [reset($approved)])
                    : Piwik::translate('Marketplace_PluginRequestsApprovedNotification', [
                        RequestsNotification::joinNames(array_values($approved)),
                    ]),
                MatomoNotification::CONTEXT_SUCCESS
            );
        }

        Option::set($optionName, (string) max(array_column($decisions, 'iddecision')));
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

    /**
     * Removes the notices no page has displayed yet, and marks their decisions as not shown, so their requester is
     * told on their next page view
     */
    public static function removeFromSession(SessionNamespace $session): void
    {
        $queuedIds = self::getQueuedIds();
        foreach ($queuedIds as $id) {
            MatomoNotification\Manager::cancel($id);
        }

        $login = $session->decisionsLogin;
        $seenBefore = $session->decisionsSeenBefore;
        $session->decisionsLogin = null;
        $session->decisionsSeenBefore = null;
        if ($queuedIds === [] || !is_string($login) || !is_string($seenBefore)) {
            return;
        }

        $optionName = sprintf(self::SEEN_OPTION_NAME, $login);
        // a deleted user's option is gone, and reads as 0, so it is not recreated
        if ((int) Option::get($optionName) > (int) $seenBefore) {
            Option::set($optionName, $seenBefore);
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
        Option::delete(sprintf(self::SEEN_OPTION_NAME, $login));
    }

    private static function notify(string $id, string $message, string $context): void
    {
        $notification = new MatomoNotification($message);
        $notification->raw = true;
        $notification->context = $context;
        $notification->type = MatomoNotification::TYPE_TRANSIENT;
        MatomoNotification\Manager::notify($id, $notification);
    }

    /**
     * Only from the cached Marketplace lists, as this runs on a page view
     *
     * @param string[] $pluginNames
     * @return array<string, string> keyed by plugin name
     */
    private static function getDisplayNames(array $pluginNames): array
    {
        $pluginInfos = StaticContainer::get(Client::class)->getInfoOfPlugins($pluginNames, false);

        $displayNames = [];
        foreach ($pluginNames as $pluginName) {
            $displayNames[$pluginName] = ($pluginInfos[$pluginName]['displayName'] ?? '') ?: $pluginName;
        }

        return $displayNames;
    }
}
