<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Marketplace\PluginTrial;

use Piwik\Common;
use Piwik\Db;
use Piwik\SettingsPiwik;

/**
 * Signs the login in a trial request email's unsubscribe link, so the link works without logging in
 * but cannot be altered to unsubscribe another user. The user's registration date is signed too, so a
 * link stops working if the user is deleted and the same login is created again later.
 */
class UnsubscribeToken
{
    private const PURPOSE = 'Marketplace.unsubscribeTrialRequestEmails';

    /**
     * @return string|null null when no user has this login
     */
    public function generate(string $login): ?string
    {
        if ($login === '') {
            return null;
        }

        $registered = $this->getRegisteredTimestamp($login);
        if ($registered === false) {
            return null;
        }

        return hash_hmac(
            'sha256',
            self::PURPOSE . '|' . $login . '|' . $registered,
            (string) SettingsPiwik::getSalt()
        );
    }

    public function isValid(string $login, string $token): bool
    {
        $expected = $this->generate($login);

        return $expected !== null && hash_equals($expected, $token);
    }

    /**
     * date_registered is a TIMESTAMP, which MySQL returns in the connection's time zone, so it is read as a
     * Unix timestamp to keep tokens valid across a database time zone change.
     *
     * @return string|false false when no user has this login, '' when the user has no registration date
     */
    private function getRegisteredTimestamp(string $login)
    {
        // matches UsersManager\Model::getUser(): prefer an exact login match over a case-insensitive one
        $rows = Db::fetchAll(
            'SELECT login, UNIX_TIMESTAMP(date_registered) AS registered FROM ' . Common::prefixTable('user') . ' WHERE login = ?',
            [$login]
        );
        if (empty($rows)) {
            return false;
        }

        foreach ($rows as $row) {
            if ($row['login'] === $login) {
                return (string) $row['registered'];
            }
        }

        return (string) $rows[0]['registered'];
    }
}
