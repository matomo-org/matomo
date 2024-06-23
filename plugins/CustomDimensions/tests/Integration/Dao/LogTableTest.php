<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomDimensions\tests\Integration\Dao;

use Piwik\Common;
use Piwik\DbHelper;
use Piwik\Plugins\CustomDimensions\CustomDimensions;
use Piwik\Plugins\CustomDimensions\Dao\LogTable;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group CustomDimensions
 * @group LogTableTest
 * @group LogTable
 * @group Dao
 * @group Plugins
 */
class LogTableTest extends IntegrationTestCase
{
    /**
     * @var LogTable
     */
    private $logVisit;

    /**
     * @var LogTable
     */
    private $logAction;

    /**
     * @var LogTable
     */
    private $logConverison;

    public function setUp(): void
    {
        parent::setUp();

        $this->logVisit = new LogTable(CustomDimensions::SCOPE_VISIT);
        $this->logAction = new LogTable(CustomDimensions::SCOPE_ACTION);
        $this->logConverison = new LogTable(CustomDimensions::SCOPE_CONVERSION);

        $this->logVisit->install();
        $this->logAction->install();
        $this->logConverison->install();
    }

    public function tearDown(): void
    {
        $this->logVisit->uninstall();
        $this->logAction->uninstall();
        $this->logConverison->uninstall();

        parent::tearDown();
    }

    public function testShouldInstall5IndexesByDefault()
    {
        $this->assertSame(5, $this->logVisit->getNumInstalledIndexes());
        $this->assertSame(5, $this->logAction->getNumInstalledIndexes());
        $this->assertSame(5, $this->logConverison->getNumInstalledIndexes());
    }

    public function testInstallShouldInstallColumnLogLinkVisitActionTimeSpent()
    {
        $columnn = DbHelper::getTableColumns(Common::prefixTable('log_link_visit_action'));
        $this->assertArrayHasKey('time_spent', $columnn);
    }

    public function testInstallShouldInstallColumnLogVisitLastIdlinkVa()
    {
        $columnn = DbHelper::getTableColumns(Common::prefixTable('log_visit'));
        $this->assertArrayHasKey('last_idlink_va', $columnn);
    }

    public function testUninstallShouldRemoveAllInstalledColumns()
    {
        $this->logVisit->uninstall();
        $this->logAction->uninstall();
        $this->logConverison->uninstall();

        $this->assertSame(0, $this->logVisit->getNumInstalledIndexes());
        $this->assertSame(0, $this->logAction->getNumInstalledIndexes());
        $this->assertSame(0, $this->logConverison->getNumInstalledIndexes());
    }

    public function testUninstallShouldInstallColumnLogLinkVisitActionTimeSpent()
    {
        $this->logAction->uninstall();

        $columnn = DbHelper::getTableColumns(Common::prefixTable('log_link_visit_action'));
        $this->assertArrayNotHasKey('time_spent', $columnn);
    }

    public function testUninstallShouldInstallColumnLogVisitLastIdlinkVa()
    {
        $this->logVisit->uninstall();

        $columnn = DbHelper::getTableColumns(Common::prefixTable('log_visit'));
        $this->assertArrayNotHasKey('last_idlink_va', $columnn);
    }

    public function testInstallShouldMakeSureThereAreAtLeast5Installed()
    {
        $this->logVisit->removeCustomDimension(3);
        $this->logVisit->removeCustomDimension(4);
        $this->logVisit->removeCustomDimension(1);

        $this->assertSame(2, $this->logVisit->getNumInstalledIndexes());

        // should automatically detect to install 3
        $this->logVisit->install();

        $this->assertSame(5, $this->logVisit->getNumInstalledIndexes());
        $this->assertSame(array(2,5,6,7,8), $this->logVisit->getInstalledIndexes());
    }

    public function testGetInstalledIndexesShouldReturnInstalledIndexes()
    {
        $expected = range(1, 5);
        $this->assertSame($expected, $this->logVisit->getInstalledIndexes());
        $this->assertSame($expected, $this->logAction->getInstalledIndexes());
        $this->assertSame($expected, $this->logConverison->getInstalledIndexes());

        $this->logAction->addManyCustomDimensions(1);

        $this->assertSame($expected, $this->logVisit->getInstalledIndexes());
        $this->assertSame(range(1, 6), $this->logAction->getInstalledIndexes());
        $this->assertSame($expected, $this->logConverison->getInstalledIndexes());

        $this->logVisit->removeCustomDimension(2);

        $this->assertSame(array(1,3,4,5), $this->logVisit->getInstalledIndexes());
        $this->assertSame(range(1, 6), $this->logAction->getInstalledIndexes());
        $this->assertSame($expected, $this->logConverison->getInstalledIndexes());
    }

    public function testGetNumInstalledIndexesShouldReturnInstalledIndexes()
    {
        $expected = 5;
        $this->assertSame($expected, $this->logVisit->getNumInstalledIndexes());
        $this->assertSame($expected, $this->logAction->getNumInstalledIndexes());
        $this->assertSame($expected, $this->logConverison->getNumInstalledIndexes());

        $this->logAction->addManyCustomDimensions(1);

        $this->assertSame($expected, $this->logVisit->getNumInstalledIndexes());
        $this->assertSame(6, $this->logAction->getNumInstalledIndexes());
        $this->assertSame($expected, $this->logConverison->getNumInstalledIndexes());

        $this->logVisit->removeCustomDimension(2);

        $this->assertSame(4, $this->logVisit->getNumInstalledIndexes());
        $this->assertSame(6, $this->logAction->getNumInstalledIndexes());
        $this->assertSame($expected, $this->logConverison->getNumInstalledIndexes());
    }

    public function testBuildCustomDimensionColumnName()
    {
        $this->assertNull(LogTable::buildCustomDimensionColumnName('0'));
        $this->assertNull(LogTable::buildCustomDimensionColumnName(''));
        $this->assertNull(LogTable::buildCustomDimensionColumnName(null));
        $this->assertNull(LogTable::buildCustomDimensionColumnName(array()));
        $this->assertNull(LogTable::buildCustomDimensionColumnName(array('index' => '')));

        $this->assertSame('custom_dimension_1', LogTable::buildCustomDimensionColumnName('1'));
        $this->assertSame('custom_dimension_1', LogTable::buildCustomDimensionColumnName('1'));
        $this->assertSame('custom_dimension_99', LogTable::buildCustomDimensionColumnName('99'));
        $this->assertSame('custom_dimension_94', LogTable::buildCustomDimensionColumnName('94te'));
        $this->assertSame('custom_dimension_95', LogTable::buildCustomDimensionColumnName(array('index' => '95')));
    }

    public function testRemoveCustomDimensionShouldRemoveASpecificIndex()
    {
        // should remove nothing as not a valid index
        $this->logVisit->removeCustomDimension(0);
        $this->logVisit->removeCustomDimension(null);

        $this->assertSame(range(1, 5), $this->logVisit->getInstalledIndexes());

        $this->logVisit->removeCustomDimension(3);
        $this->assertSame(array(1,2,4,5), $this->logVisit->getInstalledIndexes());

        $this->logVisit->removeCustomDimension('1');
        $this->assertSame(array(2,4,5), $this->logVisit->getInstalledIndexes());
    }

    public function testAddManyCustomDimensionsShouldAddNewColumns()
    {
        // should add nothing as not a valid index
        $this->logVisit->addManyCustomDimensions(0);
        $this->logVisit->addManyCustomDimensions(null);

        $this->assertSame(range(1, 5), $this->logVisit->getInstalledIndexes());

        $this->logVisit->addManyCustomDimensions(1);
        $this->assertSame(range(1, 6), $this->logVisit->getInstalledIndexes());

        $this->logVisit->addManyCustomDimensions(4);
        $this->assertSame(range(1, 10), $this->logVisit->getInstalledIndexes());

        // should automatically add after highest index if some indexes are missing in between
        $this->logVisit->removeCustomDimension('8');
        $this->logVisit->removeCustomDimension('2');
        $this->logVisit->removeCustomDimension('1');
        $this->logVisit->addManyCustomDimensions(2);

        $this->assertSame(array(3,4,5,6,7,9,10,11,12), $this->logVisit->getInstalledIndexes());
    }

    /**
     * @dataProvider getDimensionColumnTestNames
     */
    public function testIsCustomDimensionColumn($expected, $name)
    {
        $this->assertSame($expected, LogTable::isCustomDimensionColumn($name));
    }

    public function getDimensionColumnTestNames()
    {
        return array(
            array(true, 'custom_dimension_5'),
            array(true, 'custom_dimension_99'),
            array(true, 'custom_dimension_0'), // this one should be in theory false, but to keep things simple we return true, logic will be handled somewhere else
            array(false, 'custom_dimension_'),
            array(false, 'dimension_5'),
            array(false, 'anything_6'),
            array(false, ''),
            array(false, 5),
        );
    }
}
