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
use Piwik\Db\Adapter\Pdo\Mysql;
use Piwik\Db\Schema;

class MysqlTest extends TestCase
{
    protected function tearDown(): void
    {
        Schema::unsetInstance();
        parent::tearDown();
    }

    public function testCheckServerVersionThrowsWhenServerVersionTooLow(): void
    {
        Schema::setSingletonInstance($this->createMockSchema('8.0.0'));
        $adapter = $this->createMockAdapter('5.7.0');

        $this->expectException(Exception::class);
        $adapter->checkServerVersion();
    }

    public function testCheckServerVersionAllowsSupportedVersion(): void
    {
        Schema::setSingletonInstance($this->createMockSchema('5.7.0'));
        $adapter = $this->createMockAdapter('8.0.32');

        $adapter->checkServerVersion();
        $this->addToAssertionCount(1);
    }

    public function testCheckServerVersionThrowsWhenMariaDbVersionTooLow(): void
    {
        // MySQL schema configured while running MariaDB: the MariaDB minimum must still apply
        Schema::setSingletonInstance($this->createMockSchema('8.0.0'));
        $adapter = $this->createMockAdapter('5.5.5-10.5.22-MariaDB-1:10.5.22+maria~ubu2004');

        $this->expectException(Exception::class);
        $adapter->checkServerVersion();
    }

    public function testCheckServerVersionAllowsSupportedMariaDbVersion(): void
    {
        Schema::setSingletonInstance($this->createMockSchema('8.0.0'));
        $adapter = $this->createMockAdapter('10.6.19-MariaDB-1:10.6.19+maria~ubu2004');

        $adapter->checkServerVersion();
        $this->addToAssertionCount(1);
    }

    public function testCheckServerVersionAllowsSupportedMariaDbVersionWithLegacyPrefix(): void
    {
        Schema::setSingletonInstance($this->createMockSchema('8.0.0'));
        $adapter = $this->createMockAdapter('5.5.5-10.6.19-MariaDB-1:10.6.19+maria~ubu2004');

        $adapter->checkServerVersion();
        $this->addToAssertionCount(1);
    }

    /**
     * This will 'mock' the Schema class to return a specific minimum version.
     */
    private function createMockSchema(string $minimumVersion): Schema
    {
        return new class ($minimumVersion) extends Schema {
            private $minimumVersion;

            public function __construct(string $minimumVersion)
            {
                parent::__construct();
                $this->minimumVersion = $minimumVersion;
            }

            public function getMinimumSupportedVersion(): string
            {
                return $this->minimumVersion;
            }
        };
    }

    private function createMockAdapter(string $serverVersion): Mysql
    {
        $adapter = $this->getMockBuilder(Mysql::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getServerVersion'])
            ->getMock();

        $adapter->expects($this->once())
            ->method('getServerVersion')
            ->willReturn($serverVersion);

        return $adapter;
    }
}
