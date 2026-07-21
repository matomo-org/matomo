<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Db\Adapter;

use Exception;
use PHPUnit\Framework\TestCase;
use Piwik\Db\Adapter\Mysqli;

class MysqliTest extends TestCase
{
    public function testCheckServerVersionThrowsWhenMysqlVersionTooLow(): void
    {
        $adapter = $this->createMockAdapter('5.7.44');

        $this->expectException(Exception::class);
        $adapter->checkServerVersion();
    }

    public function testCheckServerVersionAllowsSupportedMysqlVersion(): void
    {
        $adapter = $this->createMockAdapter('8.0.32');

        $adapter->checkServerVersion();
        $this->addToAssertionCount(1);
    }

    public function testCheckServerVersionThrowsWhenMariaDbVersionTooLow(): void
    {
        // 10.5.22 clears the MySQL minimum (8.0) but is below the MariaDB minimum (10.6);
        // this can only throw if the server is correctly detected as MariaDB.
        $adapter = $this->createMockAdapter('5.5.5-10.5.22-MariaDB-1:10.5.22+maria~ubu2004');

        $this->expectException(Exception::class);
        $adapter->checkServerVersion();
    }

    public function testCheckServerVersionAllowsSupportedMariaDbVersion(): void
    {
        $adapter = $this->createMockAdapter('10.6.19-MariaDB-1:10.6.19+maria~ubu2004');

        $adapter->checkServerVersion();
        $this->addToAssertionCount(1);
    }

    public function testCheckServerVersionAllowsSupportedMariaDbVersionWithLegacyPrefix(): void
    {
        $adapter = $this->createMockAdapter('5.5.5-10.6.19-MariaDB-1:10.6.19+maria~ubu2004');

        $adapter->checkServerVersion();
        $this->addToAssertionCount(1);
    }

    private function createMockAdapter(string $serverVersion): Mysqli
    {
        $adapter = $this->getMockBuilder(Mysqli::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getServerVersion'])
            ->getMock();

        $adapter->expects($this->once())
            ->method('getServerVersion')
            ->willReturn($serverVersion);

        return $adapter;
    }
}
