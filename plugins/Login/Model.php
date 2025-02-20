<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Login;

use Piwik\Common;
use Piwik\Date;
use Piwik\Db;
use Piwik\Option;
use Piwik\Plugins\Login\Security\BruteForceDetection;

class Model
{
    public const NOTIFIED_USER_ABOUT_LOGIN_ATTEMPTS_OPTION_PREFIX = 'BruteForceDetection.suspiciousLoginCountNotified.';

    public const LAST_LOGIN_COUNTRY_OPTION_PREFIX = 'LoginFromDifferentCountry.lastCountry.';

    /**
     * @var
     */
    private $tablePrefixed;

    public function __construct()
    {
        $this->tablePrefixed = Common::prefixTable(BruteForceDetection::TABLE_NAME);
    }

    public function getTotalLoginAttemptsInLastHourForLogin(string $login): int
    {
        $sql = "SELECT COUNT(*) FROM `{$this->tablePrefixed}` WHERE login = ? AND attempted_at > ?";
        $count = Db::fetchOne($sql, [$login, $this->getDateTimeSubMinutes(60)]);
        return (int) $count;
    }

    public function hasNotifiedUserAboutSuspiciousLogins(string $login): bool
    {
        $optionName = $this->getSuspiciousLoginsNotifiedOptionName($login);
        $timeSent = Option::get($optionName);
        $timeSent = (int) @json_decode($timeSent, true);
        if ($timeSent <= 0) { // sanity check
            return false;
        }

        $timeSinceSent = Date::getNowTimestamp() - $timeSent;
        if (
            $timeSinceSent <= 0 // sanity check
            || $timeSinceSent > $this->getAmountOfTimeBetweenSuspiciousLoginNotifications()
        ) {
            return false;
        }

        return true;
    }

    public function getDistinctIpsAttemptingLoginsInLastHour(string $login): int
    {
        $sql = "SELECT COUNT(DISTINCT ip_address) FROM `{$this->tablePrefixed}` WHERE login = ? AND attempted_at > ?";
        $count = Db::fetchOne($sql, [$login, $this->getDateTimeSubMinutes(60)]);
        return (int) $count;
    }

    private function getDateTimeSubMinutes($minutes): string
    {
        return Date::now()->subPeriod($minutes, 'minute')->getDatetime();
    }

    private function getAmountOfTimeBetweenSuspiciousLoginNotifications(): int
    {
        return 2 * 7 * 24 * 60 * 60; // 2 weeks
    }

    private function getSuspiciousLoginsNotifiedOptionName(string $login): string
    {
        return self::NOTIFIED_USER_ABOUT_LOGIN_ATTEMPTS_OPTION_PREFIX . $login;
    }

    private function getLastLoginCountryOptionName(string $login): string
    {
        return self::LAST_LOGIN_COUNTRY_OPTION_PREFIX . $login;
    }

    public function markSuspiciousLoginsNotifiedEmailSent(string $login): void
    {
        $optionName = $this->getSuspiciousLoginsNotifiedOptionName($login);
        Option::set($optionName, Date::getNowTimestamp());
    }

    public function setLastLoginCountry(string $login, string $countryCode): void
    {
        $optionName = $this->getLastLoginCountryOptionName($login);
        Option::set($optionName, $countryCode);
    }

    public function getLastLoginCountry($login): ?string
    {
        $optionName = $this->getLastLoginCountryOptionName($login);
        $optionValue = Option::get($optionName);
        // convert false to null so that we don't return mixed types
        return (false !== $optionValue) ? $optionValue : null;
    }
}
