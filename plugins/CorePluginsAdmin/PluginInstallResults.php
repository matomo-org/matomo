<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CorePluginsAdmin;

use Piwik\Common;
use Piwik\Notification;
use Piwik\Plugin;
use Piwik\Session\SessionNamespace;

/**
 * Hands the outcome of a plugin install or update to the request that displays it. The request that
 * replaces the plugin files still has the old plugin code loaded, so it only records what it did and
 * redirects; the result page is rendered by a new request, which loads the new code.
 *
 * When an updated plugin ships a migration, that request is sent to the database upgrade screen, which
 * continues to the dashboard instead of the result page. So a summary is also posted as a persistent
 * notification, which the next summary replaces and the result page withdraws when it is shown.
 *
 * @phpstan-type InstallResult array{plugins: list<string>, marketplaceUrl?: string}
 */
class PluginInstallResults
{
    public const TOKEN_PARAMETER = 'resultToken';

    private const SESSION_NAMESPACE = 'CorePluginsAdmin.pluginInstallResults';

    private const SUMMARY_NOTIFICATION_ID = 'CorePluginsAdmin_InstallResult';

    // long enough to survive a reload of the result page, short enough not to pile up in the session
    private const TTL_IN_SECONDS = 600;

    /**
     * @param InstallResult $result
     * @return string the token to read the result back with
     */
    public static function store(array $result, string $summary): string
    {
        $token = Common::getRandomString(32);

        $session = new SessionNamespace(self::SESSION_NAMESPACE);
        $session->$token = $result;
        $session->setExpirationSeconds(self::TTL_IN_SECONDS, $token);

        $notification = new Notification($summary);
        $notification->context = Notification::CONTEXT_SUCCESS;
        $notification->type = Notification::TYPE_PERSISTENT;
        Notification\Manager::notify(self::SUMMARY_NOTIFICATION_ID, $notification);

        return $token;
    }

    /**
     * Withdraws the summary posted by store(), for a result page that shows the result itself.
     */
    public static function cancelSummary(): void
    {
        Notification\Manager::cancel(self::SUMMARY_NOTIFICATION_ID);
    }

    /**
     * @return InstallResult|null null when the token is unknown, belongs to another session or has expired
     */
    public static function get(string $token): ?array
    {
        if (!ctype_alnum($token)) {
            return null;
        }

        $session = new SessionNamespace(self::SESSION_NAMESPACE);
        if (!isset($session->$token)) {
            return null;
        }

        /** @var InstallResult|null $result written by store() only */
        $result = $session->$token;

        return is_array($result) ? $result : null;
    }

    /**
     * @param string[] $pluginNames
     * @return list<array{name: string, latestVersion: string, isTheme: bool}> one entry per plugin still on disk
     */
    public static function getInstalledPluginInfos(array $pluginNames): array
    {
        $pluginManager = Plugin\Manager::getInstance();

        $pluginInfos = [];
        foreach ($pluginNames as $pluginName) {
            if (!$pluginManager->isPluginInFilesystem($pluginName)) {
                continue;
            }

            // read from disk rather than the Marketplace, so the page shows the version actually installed
            $metadata = (new Plugin\MetadataLoader($pluginName))->load();
            $pluginInfos[] = [
                'name' => $pluginName,
                'latestVersion' => $metadata['version'],
                'isTheme' => !empty($metadata['theme']),
            ];
        }

        return $pluginInfos;
    }
}
