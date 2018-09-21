<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Login\Securit;

use Piwik\Common;
use Piwik\Db;
use Piwik\Plugins\Login\SystemSettings;

class BruteForceDetection {

    private $lockAttemptsPerMinute;
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
        $this->lockAttemptsPerMinute = $systemSettings->loginAttemptsTimeRange->getValue();
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

        $sql = 'SELECT count(*) as numLogins FROM brute_force_block WHERE ip_address = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL '.(int) $this->lockAttemptsPerMinute.' MINUTE)';
        $numLogins = $db->fetchOne($sql, array($ipAddress));

        return empty($numLogins) || $numLogins <= $this->maxLogAttempts;
    }

    public function cleanupOldEntries()
    {
        Db::get()->query('DELETE from brute_force_block WHERE attempted_at < DATE_SUB(NOW(), INTERVAL '.((int) $this->time_frame_minutes_auto_delete).' MINUTE)');
    }

    /**
     * @internal tests only
     */
    public function deleteAll()
    {
        Db::get()->query('DELETE FROM ' . $this->tablePrefixed);
    }
}