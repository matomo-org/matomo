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

/**
 * Tells a requester, once, that their requests were approved or declined
 */
final class DecisionNotifications
{
    private const SEEN_OPTION_NAME = 'Marketplace.PluginRequestDecisionsSeen.%s';
    private const BASELINE_OPTION_NAME = 'Marketplace.PluginRequestDecisionsBaseline';
    public const APPROVED_ID_PREFIX = 'Marketplace_PluginRequestsApproved_';

    /**
     * Shows the decisions on the current user's requests made since they were last shown any. Transient notices are
     * kept in the session until a page displays them, so the caller must only call this while the session is writable.
     */
    public static function createIfNeeded(): void
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
                'Marketplace_PluginRequestDeclined_' . $decision['plugin_name'],
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
