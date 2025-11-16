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
use Piwik\Db\Schema;

class MysqliTest extends TestCase
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

    /**
     * This will 'mock' the Schema class to return a specific minimum version.
     * @param string $minimumVersion
     * @return Schema
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
