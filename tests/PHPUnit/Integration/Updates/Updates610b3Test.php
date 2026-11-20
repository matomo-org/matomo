<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Updates;

use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Plugin\Manager;
use Piwik\Plugins\DebugView\Dao\RawRequestLog;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Updater;
use Piwik\Updater\Migration\Factory as MigrationFactory;
use Piwik\Updates\Updates_6_1_0_b3;

require_once PIWIK_INCLUDE_PATH . '/core/Updates/6.1.0-b3.php';

/**
 * @group Core
 * @group Updates
 */
class Updates610b3Test extends IntegrationTestCase
{
    private string $table;

    public function setUp(): void
    {
        parent::setUp();

        $this->table = Common::prefixTable(RawRequestLog::TABLE);
    }

    public function tearDown(): void
    {
        // leave the plugin installed and activated for the following tests
        (new RawRequestLog())->install();
        Manager::getInstance()->activatePlugin('DebugView');

        parent::tearDown();
    }

    public function testUpdateInstallsAndActivatesDebugViewWithTheFreshInstallSchema(): void
    {
        $freshInstallSchema = $this->getFreshInstallSchema();
        $this->simulateInstallationThatPredatesDebugView();

        $this->runUpdate();

        self::assertTrue(Manager::getInstance()->isPluginActivated('DebugView'));
        self::assertTrue(Manager::getInstance()->isPluginInstalled('DebugView'));
        self::assertSame($freshInstallSchema, $this->getSchema());
    }

    public function testUpdateIsIdempotent(): void
    {
        $this->simulateInstallationThatPredatesDebugView();

        $this->runUpdate();
        $schemaAfterFirstRun = $this->getSchema();
        $this->runUpdate();

        self::assertTrue(Manager::getInstance()->isPluginActivated('DebugView'));
        self::assertSame($schemaAfterFirstRun, $this->getSchema());
    }

    private function getFreshInstallSchema(): string
    {
        Db::query("DROP TABLE IF EXISTS `{$this->table}`");
        (new RawRequestLog())->install();

        return $this->getSchema();
    }

    /**
     * Deactivates the plugin, drops its table and forgets that it was ever installed, as on an installation
     * upgraded from a version that did not ship DebugView. (Manager::uninstallPlugin() would delete the
     * plugin's files.)
     */
    private function simulateInstallationThatPredatesDebugView(): void
    {
        $manager = Manager::getInstance();
        $manager->deactivatePlugin('DebugView');
        (new RawRequestLog())->uninstall();

        $section = Config::getInstance()->PluginsInstalled;
        $section['PluginsInstalled'] = array_values(array_diff($section['PluginsInstalled'], ['DebugView']));
        Config::getInstance()->PluginsInstalled = $section;

        self::assertFalse($manager->isPluginActivated('DebugView'));
        self::assertFalse($manager->isPluginInstalled('DebugView'));
        self::assertFalse(Db::fetchOne('SHOW TABLES LIKE ?', [$this->table]));
    }

    private function getSchema(): string
    {
        $row = Db::fetchRow("SHOW CREATE TABLE `{$this->table}`");

        return $row['Create Table'];
    }

    private function runUpdate(): void
    {
        $update = new Updates_6_1_0_b3(StaticContainer::get(MigrationFactory::class));
        $update->doUpdate(new Updater());
    }
}
