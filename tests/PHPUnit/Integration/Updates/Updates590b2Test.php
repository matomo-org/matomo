<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Updates;

use PHPUnit\Framework\TestCase;
use Piwik\Updater;
use Piwik\Updater\Migration;
use Piwik\Updater\Migration\Config\Factory as ConfigFactory;
use Piwik\Updater\Migration\Db\Factory as DbFactory;
use Piwik\Updater\Migration\Factory as MigrationFactory;
use Piwik\Updater\Migration\Plugin\Factory as PluginFactory;
use Piwik\Updates\Updates_5_9_0_b2;

require_once __DIR__ . '/../../../../core/Updates/5.9.0-b2.php';

class Updates590b2Test extends TestCase
{
    /**
     * @dataProvider getMigrationTestData
     */
    public function testGetMigrationsReturnsExpectedPluginActivations(string $databaseType, string $databaseVersion, array $expectedMigrationStrings): void
    {
        $pluginFactory = $this->getMockBuilder(PluginFactory::class)->disableOriginalConstructor()->onlyMethods(['activate'])->getMock();
        $pluginFactory->method('activate')->willReturnCallback(function (string $pluginName): Migration {
            return new TestMigration(sprintf('./console plugin:activate "%s"', $pluginName));
        });

        $migrationFactory = new MigrationFactory(
            $this->getMockBuilder(DbFactory::class)->disableOriginalConstructor()->getMock(),
            $pluginFactory,
            $this->getMockBuilder(ConfigFactory::class)->disableOriginalConstructor()->getMock()
        );
        $update = new TestableUpdates590b2($migrationFactory, $databaseType, $databaseVersion);

        $migrations = $update->getMigrations($this->createMock(Updater::class));
        $migrationStrings = array_map('strval', $migrations);

        self::assertSame($expectedMigrationStrings, $migrationStrings);
    }

    public function getMigrationTestData(): iterable
    {
        yield 'mysql below threshold' => [
            'MySQL',
            '8.0.11',
            [
                './console plugin:activate "BotTracking"',
            ],
        ];

        yield 'mysql at threshold' => [
            'MySQL',
            '8.0.12',
            [
                './console plugin:activate "BotTracking"',
                './console plugin:activate "AIAgents"',
            ],
        ];

        yield 'mariadb below threshold' => [
            'MariaDB',
            '10.3.1-MariaDB-1:10.3.1+maria~bionic',
            [
                './console plugin:activate "BotTracking"',
            ],
        ];

        yield 'mariadb at threshold' => [
            'MariaDB',
            '10.3.2-MariaDB-1:10.3.2+maria~bionic',
            [
                './console plugin:activate "BotTracking"',
                './console plugin:activate "AIAgents"',
            ],
        ];

        yield 'unsupported database type' => [
            'TiDB',
            '8.0.11-TiDB-v8.1.0',
            [
                './console plugin:activate "BotTracking"',
            ],
        ];

        yield 'unparseable version' => [
            'MySQL',
            'not-a-version',
            [
                './console plugin:activate "BotTracking"',
            ],
        ];
    }
}

class TestableUpdates590b2 extends Updates_5_9_0_b2
{
    /**
     * @var string
     */
    private $databaseType;

    /**
     * @var string
     */
    private $databaseVersion;

    public function __construct(MigrationFactory $factory, string $databaseType, string $databaseVersion)
    {
        parent::__construct($factory);
        $this->databaseType = $databaseType;
        $this->databaseVersion = $databaseVersion;
    }

    protected function getDatabaseType(): string
    {
        return $this->databaseType;
    }

    protected function getDatabaseVersion(): string
    {
        return $this->databaseVersion;
    }
}

class TestMigration extends Migration
{
    /**
     * @var string
     */
    private $description;

    public function __construct(string $description)
    {
        $this->description = $description;
    }

    public function exec()
    {
    }

    public function __toString()
    {
        return $this->description;
    }
}
