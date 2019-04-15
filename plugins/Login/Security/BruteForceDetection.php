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
use Piwik\Plugins\Login\SystemSettings;
use Piwik\Updater;
use Piwik\Version;

class BruteForceDetection {

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
}
