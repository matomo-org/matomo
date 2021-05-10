<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Login;

use Piwik\Common;
use Piwik\Date;
use Piwik\Db;
use Piwik\Option;
use Piwik\Plugins\Login\Security\BruteForceDetection;

class Model
{
    const NOTIFIED_USER_ABOUT_LOGIN_ATTEMPTS_OPTION_PREFIX = 'BruteForceDetection.suspiciousLoginCountNotified.';

    /**
     * @var
     */
    private $tablePrefixed;

    public function __construct()
    {
        $this->tablePrefixed = Common::prefixTable(BruteForceDetection::TABLE_NAME);
    }

    public function getTotalLoginAttemptsInLastHourForLogin($login)
    {
        $sql = "SELECT COUNT(*) FROM `{$this->tablePrefixed}` WHERE login = ? AND attempted_at > ?";
        $count = Db::fetchOne($sql, [$login, $this->getDateTimeSubMinutes(60)]);
        return $count;
    }

    public function hasNotifiedUserAboutSuspiciousLogins($login)
    {
        $optionName = $this->getSuspiciousLoginsNotifiedOptionName($login);
        $timeSent = Option::get($optionName);
        $timeSent = (int) @json_decode($timeSent, true);
        if ($timeSent <= 0) { // sanity check
            return false;
        }

        $timeSinceSent = Date::getNowTimestamp() - $timeSent;
        if ($timeSinceSent <= 0 // sanity check
            || $timeSinceSent > $this->getAmountOfTimeBetweenSuspiciousLoginNotifications()
        ) {
            return false;
        }

        return true;
    }

    public function getDistinctIpsAttemptingLoginsInLastHour($login)
    {
        $sql = "SELECT COUNT(DISTINCT ip_address) FROM `{$this->tablePrefixed}` WHERE login = ? AND attempted_at > ?";
        $count = Db::fetchOne($sql, [$login, $this->getDateTimeSubMinutes(60)]);
        return $count;
    }

    private function getDateTimeSubMinutes($minutes)
    {
        return Date::now()->subPeriod($minutes, 'minute')->getDatetime();
    }

    private function getAmountOfTimeBetweenSuspiciousLoginNotifications()
    {
        return 2 * 7 * 24 * 60 * 60; // 2 weeks
    }

    private function getSuspiciousLoginsNotifiedOptionName($login)
    {
        return self::NOTIFIED_USER_ABOUT_LOGIN_ATTEMPTS_OPTION_PREFIX . $login;
    }

    public function markSuspiciousLoginsNotifiedEmailSent($login)
    {
        $optionName = $this->getSuspiciousLoginsNotifiedOptionName($login);
        Option::set($optionName, Date::getNowTimestamp());
    }
}