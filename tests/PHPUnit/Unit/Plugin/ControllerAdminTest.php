<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Core\Plugin;

use PHPUnit\Framework\TestCase;
use Piwik\Plugin\ControllerAdmin;
use ReflectionMethod;

/**
 * @group Core
 */
class ControllerAdminTest extends TestCase
{
    /**
     * @dataProvider getPhpVersions
     */
    public function testIsUsingPhpVersionCompatibleWithNextPiwik(string $phpVersion, bool $expected): void
    {
        $this->assertSame(
            $expected,
            $this->invokeControllerAdminMethod('isUsingPhpVersionCompatibleWithNextPiwik', [$phpVersion])
        );
    }

    public function getPhpVersions(): iterable
    {
        yield 'PHP below Matomo 6 requirement' => ['8.0.30', false];
        yield 'PHP matching Matomo 6 requirement' => ['8.1.0', true];
        yield 'PHP above Matomo 6 requirement' => ['8.2.0', true];
    }

    /**
     * @dataProvider getDatabaseVersions
     */
    public function testIsUsingDatabaseVersionCompatibleWithNextPiwik(
        string $databaseType,
        string $databaseVersion,
        bool $expected
    ): void {
        $this->assertSame(
            $expected,
            $this->invokeControllerAdminMethod(
                'isUsingDatabaseVersionCompatibleWithNextPiwik',
                [$databaseType, $databaseVersion]
            )
        );
    }

    public function getDatabaseVersions(): iterable
    {
        yield 'MySQL below Matomo 6 requirement' => ['MySQL', '5.7.44', false];
        yield 'MySQL matching Matomo 6 requirement' => ['MySQL', '8.0.0', true];
        yield 'MySQL above Matomo 6 requirement' => ['MySQL', '8.4.0', true];
        yield 'MariaDB below Matomo 6 requirement' => ['MariaDB', '10.5.22-MariaDB', false];
        yield 'MariaDB matching Matomo 6 requirement' => ['MariaDB', '10.6.0-MariaDB', true];
        yield 'MariaDB above Matomo 6 requirement' => ['MariaDB', '11.4.0-MariaDB', true];
        yield 'MariaDB detected through MySQL schema version' => ['MySQL', '10.5.22-MariaDB', false];
        yield 'MariaDB detected with compatibility prefix' => ['MySQL', '5.5.5-10.6.12-MariaDB', true];
        yield 'Unknown database type is treated as compatible' => ['Tidb', '7.5.0', true];
    }

    /**
     * @dataProvider getDatabaseRequirements
     */
    public function testGetNextRequiredMinimumDatabaseVersion(
        string $databaseType,
        string $databaseVersion,
        ?string $expected
    ): void {
        $this->assertSame(
            $expected,
            $this->invokeControllerAdminMethod(
                'getNextRequiredMinimumDatabaseVersion',
                [$databaseType, $databaseVersion]
            )
        );
    }

    public function getDatabaseRequirements(): iterable
    {
        yield 'MySQL' => ['MySQL', '5.7.44', '8.0'];
        yield 'MariaDB' => ['MariaDB', '10.5.22-MariaDB', '10.6'];
        yield 'MariaDB detected through version' => ['MySQL', '10.5.22-MariaDB', '10.6'];
        yield 'Unknown database type' => ['Tidb', '7.5.0', null];
    }

    private function invokeControllerAdminMethod(string $methodName, array $arguments)
    {
        $method = new ReflectionMethod(ControllerAdmin::class, $methodName);
        $method->setAccessible(true);

        return $method->invokeArgs(null, $arguments);
    }
}
