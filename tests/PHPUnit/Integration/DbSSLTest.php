<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Db;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Config;

/**
 * @group Core
 */
class DbSSLTest extends IntegrationTestCase
{
    public function testMysqlSSLConnection() {
        $dbConfig = Config::getInstance()->database;
        if(isset($dbConfig['enable_ssl']) AND $dbConfig['enable_ssl'] == true) {
            Db::createDatabaseObject($dbConfig);
            $cipher = Db::fetchRow("show status like 'Ssl_cipher'");
            $this->assertNotEmpty($cipher['Value']);
        } else {
            $this->isTrue(true);
        }
    }
}
