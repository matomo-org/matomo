<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CorePluginsAdmin;

use Piwik\Common;
use Piwik\Session\SessionNamespace;

/**
 * Hands the outcome of a plugin install or update to the request that displays it. The request that
 * replaces the plugin files still has the old plugin code loaded, so it only records what it did and
 * redirects; the result page is rendered by a new request, which loads the new code.
 *
 * @phpstan-type InstallResult array{plugins: list<string>, marketplaceUrl?: string}
 */
class PluginInstallResults
{
    public const TOKEN_PARAMETER = 'resultToken';

    private const SESSION_NAMESPACE = 'CorePluginsAdmin.pluginInstallResults';

    // long enough to survive a reload of the result page, short enough not to pile up in the session
    private const TTL_IN_SECONDS = 600;

    /**
     * @param InstallResult $result
     * @return string the token to read the result back with
     */
    public static function store(array $result): string
    {
        $token = Common::getRandomString(32);

        $session = new SessionNamespace(self::SESSION_NAMESPACE);
        $session->$token = $result;
        $session->setExpirationSeconds(self::TTL_IN_SECONDS, $token);

        return $token;
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
}
