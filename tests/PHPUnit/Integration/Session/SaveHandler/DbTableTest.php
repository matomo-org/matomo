<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Session\SaveHandler;

use Piwik\Db;
use Piwik\SettingsPiwik;
use Piwik\Session;
use Piwik\Session\SaveHandler\DbTable;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class DbTableTest extends IntegrationTestCase
{
    /**
     * @var DbTable
     */
    private $testInstance;

    public function setUp(): void
    {
        parent::setUp();
        $this->testInstance = new DbTable(Session::getDbTableConfig());
    }

    public function testReadReturnsTheSessionDataCorrectly()
    {
        $this->testInstance->write('testid', 'testdata');

        $result = $this->testInstance->read('testid');

        $this->assertEquals('testdata', $result);
    }

    public function testWriteKeepsStoredDataWhenThisRequestChangedNothing()
    {
        $this->testInstance->write('testid', 'firstdata');

        $unchanged = new DbTable(Session::getDbTableConfig());
        $unchanged->read('testid');

        $other = new DbTable(Session::getDbTableConfig());
        $other->read('testid');
        $other->write('testid', 'seconddata');

        $unchanged->write('testid', 'firstdata');

        $this->assertEquals('seconddata', $this->testInstance->read('testid'));
    }

    public function testWriteStillRefreshesTheSessionWhenThisRequestChangedNothing()
    {
        $this->testInstance->write('testid', 'testdata');
        $this->testInstance->read('testid');

        // back-date it so a refreshed timestamp is actually distinguishable
        $this->setModified('testid', time() - 120);

        $this->testInstance->write('testid', 'testdata');

        $this->assertGreaterThan(time() - 120, $this->getModified('testid'));
        $this->assertEquals('testdata', $this->testInstance->read('testid'));
    }

    public function testWriteKeepsTheSessionAliveWhenTheRowWasRemoved()
    {
        $this->testInstance->write('testid', 'testdata');
        $this->testInstance->read('testid');

        $other = new DbTable(Session::getDbTableConfig());
        $other->destroy('testid');

        $this->testInstance->write('testid', 'testdata');

        $this->assertEquals('testdata', $this->testInstance->read('testid'));
    }

    public function testWriteStoresTheLastValueWhenWrittenTwice()
    {
        $this->testInstance->write('testid', 'firstdata');
        $this->testInstance->read('testid');

        $this->testInstance->write('testid', 'seconddata');
        $this->testInstance->write('testid', 'firstdata');

        $this->assertEquals('firstdata', $this->testInstance->read('testid'));
    }

    public function testWriteStoresDataThisRequestChanged()
    {
        $this->testInstance->write('testid', 'testdata');
        $this->testInstance->read('testid');

        $this->testInstance->write('testid', 'changeddata');

        $this->assertEquals('changeddata', $this->testInstance->read('testid'));
    }

    private function getModified($id)
    {
        $config = Session::getDbTableConfig();

        return Db::fetchOne(
            'SELECT ' . $config['modifiedColumn'] . ' FROM ' . $config['name']
                . ' WHERE ' . $config['primary'] . ' = ?',
            [hash(DbTable::TOKEN_HASH_ALGO, $id . SettingsPiwik::getSalt())]
        );
    }

    private function setModified($id, $modified)
    {
        $config = Session::getDbTableConfig();

        Db::query(
            'UPDATE ' . $config['name'] . ' SET ' . $config['modifiedColumn'] . ' = ?'
                . ' WHERE ' . $config['primary'] . ' = ?',
            [$modified, hash(DbTable::TOKEN_HASH_ALGO, $id . SettingsPiwik::getSalt())]
        );
    }
}
