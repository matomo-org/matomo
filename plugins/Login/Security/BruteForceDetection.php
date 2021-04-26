<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Login\Security;

use Piwik\Common;
use Piwik\Date;
use Piwik\Db;
use Piwik\Option;
use Piwik\Plugins\Login\Emails\SuspiciousLoginAttemptsInLastHourEmail;
use Piwik\Plugins\Login\SystemSettings;
use Piwik\Updater;
use Piwik\Version;

class BruteForceDetection {

    const OVERALL_LOGIN_LOCKOUT_THRESHOLD = 1000; // TODO: maybe make this come from DI config or INI config
    const NOTIFIED_USER_ABOUT_LOGIN_ATTEMPTS_OPTION_PREFIX = 'BruteForceDetection.suspiciousLoginCountNotified.';

    private $minutesTimeRange;
    private $maxLogAttempts;

    private $table = 'brute_force_log';
    private $tablePrefixed = '';

    /**
     * @var SystemSettings
     */
    private $settings;

    /**
     * @var Updater
     */
    private $updater;

    public function __construct(SystemSettings $systemSettings)
    {
        $this->tablePrefixed = Common::prefixTable($this->table);
        $this->settings = $systemSettings;
        $this->minutesTimeRange = $systemSettings->loginAttemptsTimeRange->getValue();
        $this->maxLogAttempts = $systemSettings->maxFailedLoginsPerMinutes->getValue();
        $this->updater = new Updater();
    }

    public function isEnabled()
    {
        $dbSchemaVersion = $this->updater->getCurrentComponentVersion('core');
        if ($dbSchemaVersion && version_compare($dbSchemaVersion, '3.8.0') == -1) {
            return false; // do not enable brute force detection before the tables exist
        }

        return $this->settings->enableBruteForceDetection->getValue();
    }

    public function addFailedAttempt($ipAddress)
    {
        $now = $this->getNow()->getDatetime();
        $db = Db::get();
        $db->query('INSERT INTO '.$this->tablePrefixed.' (ip_address, attempted_at) VALUES(?,?)', array($ipAddress, $now));
    }

    public function isAllowedToLogin($ipAddress)
    {
        if ($this->settings->isBlacklistedIp($ipAddress)) {
            return false;
        }

        if ($this->settings->isWhitelistedIp($ipAddress)) {
            return true;
        }

        $db = Db::get();

        $startTime = $this->getStartTimeRange();
        $sql = 'SELECT count(*) as numLogins FROM '.$this->tablePrefixed.' WHERE ip_address = ? AND attempted_at > ?';
        $numLogins = $db->fetchOne($sql, array($ipAddress, $startTime));

        return empty($numLogins) || $numLogins <= $this->maxLogAttempts;
    }

    public function getCurrentlyBlockedIps()
    {
        $sql = 'SELECT ip_address
                FROM ' . $this->tablePrefixed . ' 
                WHERE attempted_at > ?
                GROUP BY ip_address 
                HAVING count(*) > ' . (int) $this->maxLogAttempts;
        $rows = Db::get()->fetchAll($sql, array($this->getStartTimeRange()));

        $ips = array();
        foreach ($rows as $row) {
            if ($this->settings->isWhitelistedIp($row['ip_address'])) {
                continue;
            }
            $ips[] = $row['ip_address'];
        }

        return $ips;
    }

    public function unblockIp($ip)
    {
        // we only delete where attempted_at was recent and keep other IPs for history purposes
        Db::get()->query('DELETE FROM '.$this->tablePrefixed.' WHERE ip_address = ? and attempted_at > ?', array($ip, $this->getStartTimeRange()));
    }

    public function cleanupOldEntries()
    {
        // we delete all entries older than 7 days (or more if more attempts are logged)
        $minutesAutoDelete = 10080;

        $minutes = max($minutesAutoDelete, $this->minutesTimeRange);
        $deleteOlderDate = $this->getDateTimeSubMinutes($minutes);
        Db::get()->query('DELETE FROM '.$this->tablePrefixed.' WHERE attempted_at < ?', array($deleteOlderDate));
    }

    /**
     * @internal tests only
     */
    public function deleteAll()
    {
        return Db::query('DELETE FROM ' . $this->tablePrefixed);
    }

    /**
     * @internal tests only
     */
    public function getAll()
    {
        return Db::get()->fetchAll('SELECT * FROM ' . $this->tablePrefixed);
    }

    protected function getNow()
    {
        return Date::now();
    }

    private function getStartTimeRange()
    {
        return $this->getDateTimeSubMinutes($this->minutesTimeRange);
    }

    private function getDateTimeSubMinutes($minutes)
    {
        return $this->getNow()->subPeriod($minutes, 'minute')->getDatetime();
    }

    // TODO: new integration tests
    // TODO: need to make sure bruteforcedetection happens after any ip allowlists/blocklists have been used
    public function isUserLoginBlocked($login)
    {
        $count = $this->getTotalLoginAttemptsInLastHourForLogin($login);
        if (!$this->hasTooManyTriesOverallInlastHour($count)) {
            return false;
        }

        if (!$this->hasNotifiedUserAboutSuspiciousLogins($login)) {
            $this->sendSuspiciousLoginsEmailToUser($login, $count);
        }

        return true;
    }

    private function getTotalLoginAttemptsInLastHourForLogin($login)
    {
        $sql = "SELECT COUNT(*) FROM `{$this->tablePrefixed}` WHERE login = ? AND attempted_at > ?";
        $count = Db::fetchOne($sql, [$login, $this->getDateTimeSubMinutes(60)]);
        return $count;
    }

    private function hasTooManyTriesOverallInLastHour($count)
    {
        return $count > self::OVERALL_LOGIN_LOCKOUT_THRESHOLD;
    }

    private function hasNotifiedUserAboutSuspiciousLogins($login)
    {
        $optionName = $this->getSusNotifiedOptionName($login);
        $timeSent = Option::get($optionName);
        $timeSent = (int) @json_decode($timeSent, true);
        if ($timeSent <= 0) { // sanity check
            return false;
        }

        $timeSinceSent = Date::getNowTimestamp() - $timeSent;
        if ($timeSinceSent <= 0 // sanity check
            || $timeSinceSent > $this->getAmountOfTimeBetweenSusLoginNotifications()
        ) {
            return false;
        }

        return true;
    }

    private function sendSuspiciousLoginsEmailToUser($login, $countOverall)
    {
        $distinctIps = $this->getDistinctIpsAttemptingLoginsInLastHour($login);

        $email = new SuspiciousLoginAttemptsInLastHourEmail($login, $countOverall, $distinctIps);
        $email->send();

        $optionName = $this->getSusNotifiedOptionName($login);
        Option::set($optionName, Date::getNowTimestamp());
    }

    private function getAmountOfTimeBetweenSusLoginNotifications()
    {
        return 2 * 7 * 24 * 60 * 60; // 2 weeks
    }

    private function getSusNotifiedOptionName($login)
    {
        return self::NOTIFIED_USER_ABOUT_LOGIN_ATTEMPTS_OPTION_PREFIX . $login;
    }

    private function getDistinctIpsAttemptingLoginsInLastHour($login)
    {
        $sql = "SELECT COUNT(DISTINCT ip_address) FROM `{$this->tablePrefixed}` WHERE login = ? AND attempted_at > ?";
        $count = Db::fetchOne($sql, [$login, $this->getDateTimeSubMinutes(60)]);
        return $count;
    }
}
