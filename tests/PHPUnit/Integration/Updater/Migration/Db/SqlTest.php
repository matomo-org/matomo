<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Updater\Migration\Db;

use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Updater\Migration\Db\Sql;

/**
 * @group Core
 * @group Updater
 * @group Migration
 * @group SqlTest
 */
class SqlTest extends IntegrationTestCase
{
    private $testQuery = 'ALTER TABLE foobar ADD COLUMN barbaz VARCHAR(1)';

    public function testToStringShouldAppendSemicolonIfNeeded()
    {
        $sql = $this->sql($this->testQuery);

        $this->assertSame($this->testQuery . ';', '' . $sql);
    }

    public function testToStringShouldNotAppendSemicolonIfNotNeeded()
    {
        $sql = $this->sql($this->testQuery . ';');

        $this->assertSame($this->testQuery . ';', '' . $sql);
    }

    public function testToStringShouldNotAppendSemicolonIfNoQueryGiven()
    {
        $sql = $this->sql('');

        $this->assertSame('', '' . $sql);
    }

    public function testExecShouldNotFailWhenNoQueryGiven()
    {
        $sql = $this->sql('');

        $this->assertNull($sql->exec());
    }

    public function testConstructorShouldConvertErrorCodeToArrayIfNeeded()
    {
        $sql = $this->sql($this->testQuery, 1091);
        $this->assertSame(array(1091), $sql->getErrorCodesToIgnore());
    }

    public function testConstructorShouldNotConvertErrorCodeToArrayIfNotNeeded()
    {
        $sql = $this->sql($this->testQuery, array(1091, 1061));
        $this->assertSame(array(1091, 1061), $sql->getErrorCodesToIgnore());
    }

    public function testAddErrorCodeToIgnoreAddsOneErrorCode()
    {
        $sql = $this->sql($this->testQuery, array(1091, 1061));
        $sql->addErrorCodeToIgnore(1049);
        $this->assertSame(array(1091, 1061, 1049), $sql->getErrorCodesToIgnore());
    }

    private function sql($query, $errorCode = false)
    {
        return new Sql($query, $errorCode);
    }
}
