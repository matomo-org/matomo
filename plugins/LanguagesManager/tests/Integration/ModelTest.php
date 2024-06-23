<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\LanguagesManager\tests\Integration;

use Piwik\Common;
use Piwik\Db;
use Piwik\Plugins\LanguagesManager\Model;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group LanguagesManager
 * @group ModelTest
 * @group Plugins
 */
class ModelTest extends IntegrationTestCase
{
    /**
     * @var Model
     */
    protected $model;

    public function setUp(): void
    {
        $this->model = new Model();
        parent::setUp();
    }

    public function testInstallShouldNotFailAndActuallyCreateTheDatabases()
    {
        $this->assertContainTables(array('user_language'));

        $columns = Db::fetchAll('show columns from ' . Common::prefixTable('user_language'));
        $this->assertCount(3, $columns);
    }

    public function testUninstallShouldNotFailAndRemovesAllAlertTables()
    {
        Model::uninstall();

        $this->assertNotContainTables(array('user_language'));

        Model::install();
    }

    public function testHandlesUserLanguageEntriesCorrectly()
    {
        $this->model->setLanguageForUser('admin', 'de');

        $this->assertTableEntryCount(1);

        $this->assertEquals('de', $this->model->getLanguageForUser('admin'));

        $this->model->deleteUserLanguage('admin');

        $this->assertTableEntryCount(0);
    }

    public function testHandlesUserTimeFormatEntriesCorrectly()
    {
        $this->model->set12HourClock('admin', false);

        $this->assertTableEntryCount(1);

        $this->assertEquals(false, $this->model->uses12HourClock('admin'));

        $this->model->deleteUserLanguage('admin');

        $this->assertTableEntryCount(0);
    }

    public function testHandlesUserLanguageAndTimeFormatEntriesCorrectly()
    {
        $this->model->setLanguageForUser('admin', 'de');

        $this->assertTableEntryCount(1);

        $this->model->set12HourClock('admin', false);
        $this->model->set12HourClock('user', true);

        $this->assertTableEntryCount(2);

        $this->assertEquals('de', $this->model->getLanguageForUser('admin'));
        $this->assertEquals('', $this->model->getLanguageForUser('user'));
        $this->assertEquals(false, $this->model->uses12HourClock('admin'));
        $this->assertEquals(true, $this->model->uses12HourClock('user'));

        $this->model->deleteUserLanguage('admin');

        $this->assertTableEntryCount(1);
    }

    private function assertTableEntryCount($count)
    {
        $entryCount = Db::fetchOne('SELECT COUNT(*) FROM ' . Common::prefixTable('user_language'));

        $this->assertEquals($count, $entryCount);
    }

    private function assertContainTables($expectedTables)
    {
        $tableNames = $this->getCurrentAvailableTableNames();

        foreach ($expectedTables as $expectedTable) {
            self::assertTrue(in_array(Common::prefixTable($expectedTable), $tableNames));
        }
    }

    private function assertNotContainTables($expectedTables)
    {
        $tableNames = $this->getCurrentAvailableTableNames();

        foreach ($expectedTables as $expectedTable) {
            self::assertTrue(!in_array(Common::prefixTable($expectedTable), $tableNames));
        }
    }

    private function getCurrentAvailableTableNames()
    {
        $tables = Db::fetchAll('show tables');

        $tableNames = array();
        foreach ($tables as $table) {
            $tableNames[] = array_shift($table);
        }

        return $tableNames;
    }
}
