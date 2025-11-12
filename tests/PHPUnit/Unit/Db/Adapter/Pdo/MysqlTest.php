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
        Schema::setSingletonInstance($this->createSchemaDouble('8.0.0'));
        $adapter = $this->createAdapterDouble('5.7.0');

        $this->expectException(Exception::class);
        $adapter->checkServerVersion();
    }

    public function testCheckServerVersionAllowsSupportedVersion(): void
    {
        Schema::setSingletonInstance($this->createSchemaDouble('5.7.0'));
        $adapter = $this->createAdapterDouble('8.0.32');

        $adapter->checkServerVersion();
        $this->addToAssertionCount(1);
    }

    /**
     * This will 'mock' the Schema class to return a specific minimum version.
     * @param string $minimumVersion
     * @return Schema
     */
    private function createSchemaDouble(string $minimumVersion): Schema
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

    private function createAdapterDouble(string $serverVersion): Mysql
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
