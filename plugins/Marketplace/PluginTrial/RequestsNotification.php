<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\PluginTrial;

use Piwik\Notification as MatomoNotification;
use Piwik\Piwik;
use Piwik\Plugins\Marketplace\SiteAwareLinks;

/**
 * The one notice that tells a super user about every plugin with pending requests they have not dismissed
 */
final class RequestsNotification
{
    private const ID_PREFIX = 'Marketplace_PluginRequests_';

    /**
     * @var string[]
     */
    private static $postedIds = [];

    public static function isOwnId(string $notificationId): bool
    {
        return str_starts_with($notificationId, self::getIdPrefix());
    }

    /**
     * Replaces the current user's notice, or removes it when no plugin is given
     *
     * @param array<string, string> $displayNames keyed by plugin name
     * @return string|null the notice's ID, which differs per list of plugins so a dismissal tells which list was shown
     */
    public static function notify(array $displayNames): ?string
    {
        self::removeFromSession();

        if (empty($displayNames)) {
            return null;
        }

        $names = array_map(function (string $displayName): string {
            return '<b>' . htmlentities($displayName) . '</b>';
        }, array_values($displayNames));

        $message = count($names) === 1
            ? Piwik::translate('Marketplace_PluginRequestedNotification', [$names[0]])
            : Piwik::translate('Marketplace_PluginsRequestedNotification', [count($names), self::joinNames($names)]);
        // kept in the session between checks, so it carries only the site of the request that happened to build it
        $message .= sprintf(
            ' <a href="%s">%s</a>',
            (new SiteAwareLinks())->getActionUrl('manageRequests'),
            Piwik::translate('Marketplace_ReviewRequests')
        );

        $notification = new MatomoNotification($message);
        $notification->raw = true;
        $notification->context = MatomoNotification::CONTEXT_INFO;
        $notification->type = MatomoNotification::TYPE_PERSISTENT;
        $id = self::getIdPrefix() . md5(implode(',', array_keys($displayNames)));
        MatomoNotification\Manager::notify($id, $notification);
        self::$postedIds[] = $id;

        return $id;
    }

    public static function removeFromSession(): void
    {
        // without a session the manager lists none, though it still holds those posted in this request
        $ids = array_merge(self::$postedIds, array_keys(MatomoNotification\Manager::getAllNotificationsToDisplay()));
        foreach (array_unique($ids) as $id) {
            if (self::isOwnId((string) $id)) {
                MatomoNotification\Manager::cancel($id);
            }
        }
        self::$postedIds = [];
    }

    private static function getIdPrefix(): string
    {
        return self::ID_PREFIX . md5(Piwik::getCurrentUserLogin()) . '_';
    }

    /**
     * @param string[] $names
     */
    public static function joinNames(array $names): string
    {
        $last = array_pop($names);

        return empty($names) ? $last : implode(', ', $names) . ' ' . Piwik::translate('General_And') . ' ' . $last;
    }
}
