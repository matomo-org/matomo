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
use Piwik\Db;
use Piwik\Plugins\Login\SystemSettings;

class BruteForceDetection {

    private $lockAttemptsMinutes;
    private $maxLogAttempts;

    private $table = 'brute_force_log';
    private $tablePrefixed = '';

    // we delete all entries older than 14 days
    private $time_frame_minutes_auto_delete = 20160;

    /**
     * @var SystemSettings
     */
    private $settings;

    public function __construct(SystemSettings $systemSettings)
    {
        $this->tablePrefixed = Common::prefixTable($this->table);
        $this->settings = $systemSettings;
        $this->lockAttemptsMinutes = $systemSettings->loginAttemptsTimeRange->getValue();
        $this->maxLogAttempts = $systemSettings->maxFailedLoginsPerMinutes->getValue();
    }

    public function isEnabled()
    {
        return $this->settings->enableBruteForceDetection->getValue();
    }

    public function addFailedLoginAttempt($ipAddress, $login)
    {
        if ($login === false || $login === '') {
            $login = null;
        }

        $db = Db::get();
        $db->query('INSERT INTO '.$this->tablePrefixed.' SET ip_address = ?, login = ?, attempted_at = NOW()', array($ipAddress, $login));
    }

    public function canLogin($ipAddress, $login)
    {
        // login is currently not used...

        if ($this->settings->isBlacklistedIp($ipAddress)) {
            return false;
        }

        if ($this->settings->isWhitelistedIp($ipAddress)) {
            return true;
        }

        $db = Db::get();

        $sql = 'SELECT count(*) as numLogins FROM '.$this->tablePrefixed.' WHERE ip_address = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL '.(int) $this->lockAttemptsMinutes.' MINUTE)';
        $numLogins = $db->fetchOne($sql, array($ipAddress));

        return empty($numLogins) || $numLogins <= $this->maxLogAttempts;
    }

    public function getCurrentlyBlockedIps()
    {
        $sql = 'SELECT ip_address
                FROM '.$this->tablePrefixed.' 
                WHERE attempted_at > DATE_SUB(NOW(), INTERVAL '.(int) $this->lockAttemptsMinutes.' MINUTE)
                GROUP BY ip_address HAVING count(*) > ' . $this->lockAttemptsMinutes;
        $rows = Db::get()->fetchAll($sql);
        $ips = array('123.1.2.2');
        foreach ($rows as $row) {
            $ips[] = $row['ip_address'];
        }
        return $ips;
    }

    public function unblockIp($ip)
    {
        Db::get()->query('DELETE FROM '.$this->tablePrefixed.' WHERE ip_address = ? and attempted_at < DATE_SUB(NOW(), INTERVAL '.((int) $this->lockAttemptsMinutes).' MINUTE)', array($ip));
    }

    public function cleanupOldEntries()
    {
        $minutes = max($this->time_frame_minutes_auto_delete, $this->lockAttemptsMinutes);
        $this->removeEntriesSinceMinutes($minutes);
    }

    private function removeEntriesSinceMinutes($minutes)
    {
        Db::get()->query('DELETE FROM '.$this->tablePrefixed.' WHERE attempted_at < DATE_SUB(NOW(), INTERVAL '.((int) $minutes).' MINUTE)');
    }

    /**
     * @internal tests only
     */
    public function deleteAll()
    {
        Db::get()->query('DELETE FROM ' . $this->tablePrefixed);
    }
}