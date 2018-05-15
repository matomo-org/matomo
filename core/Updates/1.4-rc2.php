<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Common;
use Piwik\Updater;
use Piwik\Updates;
use Piwik\Updater\Migration\Factory as MigrationFactory;

/**
 */
class Updates_1_4_rc2 extends Updates
{
    /**
     * @var MigrationFactory
     */
    private $migration;

    public function __construct(MigrationFactory $factory)
    {
        $this->migration = $factory;
    }

    public function getMigrations(Updater $updater)
    {
        $errorCodeDbNotExists = Updater\Migration\Db::ERROR_CODE_TABLE_NOT_EXISTS;

        return array(
            $this->migration->db->sql("SET sql_mode=''"),
            // this converts the 32-bit UNSIGNED INT column to a 16 byte VARBINARY;
            // _but_ MySQL does string conversion! (e.g., integer 1 is converted to 49 -- the ASCII code for "1")
            $this->migration->db->sql('ALTER TABLE ' . Common::prefixTable('log_visit') . ' MODIFY location_ip VARBINARY(16) NOT NULL'),
            $this->migration->db->sql('ALTER TABLE ' . Common::prefixTable('logger_api_call') . ' MODIFY caller_ip VARBINARY(16) NOT NULL', $errorCodeDbNotExists),

            // fortunately, 2^32 is 10 digits long and fits in the VARBINARY(16) without truncation;
            // to fix this, we cast to an integer, convert to hex, pad out leading zeros, and unhex it
            $this->migration->db->sql('UPDATE ' . Common::prefixTable('log_visit') . "
                SET location_ip = UNHEX(LPAD(HEX(CONVERT(location_ip, UNSIGNED)), 8, '0'))"),
            $this->migration->db->sql('UPDATE ' . Common::prefixTable('logger_api_call') . "
                SET caller_ip = UNHEX(LPAD(HEX(CONVERT(caller_ip, UNSIGNED)), 8, '0'))", $errorCodeDbNotExists),
        );
    }

    public function doUpdate(Updater $updater)
    {
        $updater->executeMigrations(__FILE__, $this->getMigrations($updater));
    }
}
