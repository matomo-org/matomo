<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreConsole\tests\Unit\ClickhouseBench;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Piwik\Plugins\CoreConsole\ClickhouseBench\Engine;

/**
 * @group CoreConsole
 * @group ClickhouseBench
 * @group Plugins
 */
class EngineTest extends TestCase
{
    public function testAnEmptyListMeansBothLegs(): void
    {
        self::assertSame(
            ['mysql', 'clickhouse'],
            array_map(static fn(Engine $engine): string => $engine->getKey(), Engine::fromList(''))
        );
    }

    public function testAuroraIsAnAliasForMysql(): void
    {
        self::assertSame(Engine::MYSQL, Engine::fromKey('Aurora')->getKey());
        self::assertSame(Engine::CLICKHOUSE, Engine::fromKey('ch')->getKey());
    }

    public function testARepeatedEngineIsOnlyMeasuredOnce(): void
    {
        self::assertCount(1, Engine::fromList('mysql,aurora,mysql'));
    }

    public function testAnUnknownEngineIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        Engine::fromKey('postgres');
    }

    /**
     * Both legs set the variable explicitly. If the ClickHouse leg merely omitted it, an
     * ambient MATOMO_ANALYTICS_DB_DISABLED=1 exported in the operator's shell would silently
     * turn the ClickHouse leg into a second MySQL leg, and the table would look normal.
     */
    public function testBothLegsSetTheSwitchExplicitly(): void
    {
        self::assertSame(
            ['MATOMO_ANALYTICS_DB_DISABLED' => '1'],
            Engine::fromKey('mysql')->getChildEnvironment()
        );
        self::assertSame(
            ['MATOMO_ANALYTICS_DB_DISABLED' => '0'],
            Engine::fromKey('clickhouse')->getChildEnvironment()
        );
    }
}
