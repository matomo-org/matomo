<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DebugView\tests\Integration;

use Piwik\Common;
use Piwik\Db;
use Piwik\Plugins\DebugView\Dao\RawRequestLog;
use Piwik\Plugins\DebugView\DebugView;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group DebugView
 * @group DebugViewPluginTest
 * @group Plugins
 */
class PluginTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        if (class_exists('\Piwik\Plugins\TagManager\TagManager')) {
            \Piwik\Plugins\TagManager\TagManager::$enableAutoContainerCreation = false;
        }
    }

    public function testDeletingASiteRemovesItsRemainingArmingOption()
    {
        Fixture::createSuperUser();
        FakeAccess::$superUser = true;

        foreach ([1, 2] as $idSite) {
            if (!Fixture::siteCreated($idSite)) {
                Fixture::createWebsite('2020-01-01 00:00:00');
            }
        }

        $model = new \Piwik\Plugins\DebugView\Model\DebugRequests(new RawRequestLog());
        $model->markSiteActive(1);
        $model->markSiteActive(2);
        $this->assertGreaterThan(0, $model->getActiveUntilTimestamp(2));

        \Piwik\Plugins\SitesManager\API::getInstance()->deleteSite(2);

        $this->assertSame(0, $model->getActiveUntilTimestamp(2));
        // other sites' arming state is untouched
        $this->assertGreaterThan(0, $model->getActiveUntilTimestamp(1));
    }

    public function testGetTablesInstalledRegistersThePrefixedRawRequestTable()
    {
        $tables = [];
        (new DebugView('DebugView'))->getTablesInstalled($tables);

        $this->assertSame([Common::prefixTable(RawRequestLog::TABLE)], $tables);
    }

    public function testInstallCreatesTheTableAndUninstallDropsIt()
    {
        $plugin = new DebugView('DebugView');
        $table = Common::prefixTable(RawRequestLog::TABLE);

        $plugin->install();
        $this->assertNotFalse(Db::fetchOne('SHOW TABLES LIKE "' . $table . '"'));

        $plugin->uninstall();
        $this->assertFalse(Db::fetchOne('SHOW TABLES LIKE "' . $table . '"'));

        // restore for other tests
        $plugin->install();
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }
}
