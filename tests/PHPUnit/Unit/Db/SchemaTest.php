<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\Db;

use PHPUnit\Framework\TestCase;
use Piwik\Db\Schema;

class SchemaTest extends TestCase
{
    /**
     * @dataProvider getComparableVersions
     */
    public function testGetComparableVersion(string $rawVersion, string $expected): void
    {
        $this->assertSame($expected, Schema::getComparableVersion($rawVersion));
    }

    public function getComparableVersions(): iterable
    {
        yield 'plain mysql' => ['8.0.32', '8.0.32'];
        yield 'mysql with distro suffix' => ['8.0.36-0ubuntu0.22.04.1', '8.0.36'];
        yield 'percona' => ['8.0.36-28', '8.0.36'];
        yield 'mariadb modern string' => ['10.6.19-MariaDB-1:10.6.19+maria~ubu2004', '10.6.19'];
        yield 'mariadb legacy compatibility prefix' => ['5.5.5-10.6.19-MariaDB-1:10.6.19', '10.6.19'];
        yield 'tidb' => ['8.0.11-TiDB-v7.5.0', '8.0.11'];
        yield 'no digits returns input unchanged' => ['unknown', 'unknown'];
        yield 'empty string returns input unchanged' => ['', ''];
    }

    /**
     * @dataProvider getServerTypes
     */
    public function testGetServerTypeFromVersion(string $rawVersion, string $expected): void
    {
        $this->assertSame($expected, Schema::getServerTypeFromVersion($rawVersion));
    }

    public function getServerTypes(): iterable
    {
        yield 'mysql' => ['8.0.32', 'MySQL'];
        yield 'mariadb' => ['10.6.19-MariaDB', 'MariaDB'];
        yield 'mariadb legacy compatibility prefix' => ['5.5.5-10.6.19-MariaDB', 'MariaDB'];
        yield 'tidb' => ['8.0.11-TiDB-v7.5.0', 'TiDb'];
        yield 'unrecognised string defaults to mysql' => ['', 'MySQL'];
    }

    /**
     * @dataProvider getServerMinimums
     */
    public function testGetMinimumSupportedVersionForServer(string $rawVersion, string $expected): void
    {
        $this->assertSame($expected, Schema::getMinimumSupportedVersionForServer($rawVersion));
    }

    public function getServerMinimums(): iterable
    {
        yield 'mysql uses the mysql minimum' => ['8.0.32', '8.0'];
        yield 'mariadb uses the mariadb minimum' => ['10.6.19-MariaDB', '10.6'];
        yield 'mariadb with legacy prefix uses the mariadb minimum' => ['5.5.5-10.6.19-MariaDB', '10.6'];
        yield 'tidb inherits the mysql minimum' => ['8.0.11-TiDB-v7.5.0', '8.0'];
    }
}
