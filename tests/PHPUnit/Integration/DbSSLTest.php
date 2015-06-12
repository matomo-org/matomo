<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\Common;
use Piwik\Db;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use PDO;

/**
 * @group Core
 */
class DbSSLTest extends IntegrationTestCase
{
    public function testMysqliSslSupport() {
        // Once there is this function it seams ok
        $this->assertTrue(function_exists("mysqli_ssl_set"));
    }

    /**
     * @dataProvider pdoConstantValues
     */
    public function testPdoMysqlSslDefined($name, $value) {
        $value = 0; //not used
        $this->assertTrue(is_numeric($name));
    }
    
    /**
     * @dataProvider pdoConstantValues
     * @depends testPdoMysqlSslDefined
     */
    public function testPdoMysqlSslValues($name, $value) {
        // Tests if PDO ssl const did not change and if they even exists
        $this->assertEquals($name, $value);
    }

    public function pdoConstantValues() {
        return array(
            array(PDO::MYSQL_ATTR_SSL_CA, 1012),
            array(PDO::MYSQL_ATTR_SSL_CAPATH, 1013),
            array(PDO::MYSQL_ATTR_SSL_CERT, 1011),
            array(PDO::MYSQL_ATTR_SSL_CIPHER, 1014),
            array(PDO::MYSQL_ATTR_SSL_KEY, 1010)
        );
    }
    
    /**
     * Tests whatever mysql supports SSL
     */
    public function testMysqlSslSupport() {
        $have_ssl = Db::fetchRow("SHOW VARIABLES LIKE 'have_ssl'");
        $values = array(
            'YES',
            'DISABLED'
        );

        $this->assertTrue(in_array($have_ssl['Value'], $values));
    }
}
