<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PrivacyManager\tests\Integration\Model;

use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Db;
use Piwik\Plugins\PrivacyManager\Model\DataSubjects;
use Piwik\Plugins\PrivacyManager\tests\Fixtures\MultipleSitesMultipleVisitsFixture;
use Piwik\Plugins\PrivacyManager\tests\Fixtures\TestLogFoo;
use Piwik\Plugins\PrivacyManager\tests\Fixtures\TestLogFooBar;
use Piwik\Plugins\PrivacyManager\tests\Fixtures\TestLogFooBarBaz;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Class DataSubjectsTest
 *
 * @group Plugins
 */
class DataSubjectsTest extends IntegrationTestCase
{
    /**
     * @var DataSubjects
     */
    private $dataSubjects;

    /**
     * @var MultipleSitesMultipleVisitsFixture
     */
    private $theFixture;

    public function setUp()
    {
        parent::setUp();

        $this->theFixture = new MultipleSitesMultipleVisitsFixture();
        $this->theFixture->installLogTables();
        $this->theFixture->setUpLocation();

        $logTablesProvider = StaticContainer::get('Piwik\Plugin\LogTablesProvider');
        $this->dataSubjects = new DataSubjects($logTablesProvider);
    }

    public function tearDown()
    {
        $this->theFixture->uninstallLogTables();
        $this->theFixture->tearDownLocation();
    }

    public function test_deleteExport_deleteOneVisit()
    {
        $this->theFixture->setUpWebsites();
        $this->theFixture->trackVisits($idSite = 1, 1);

        $this->assertNotEmpty($this->getVisit(1, 1));
        $this->assertNotEmpty($this->getLinkAction(1, 1));
        $this->assertNotEmpty($this->getConversion(1, 1));
        $this->assertNotEmpty($this->getOneVisit(1, 1, TestLogFoo::TABLE));

        $this->assertNotEmpty($this->getVisit(1, 2));
        $this->assertNotEmpty($this->getLinkAction(1, 2));
        $this->assertNotEmpty($this->getOneVisit(1, 2, TestLogFoo::TABLE));

        $result = $this->dataSubjects->deleteDataSubjects(array(array('idsite' => '1', 'idvisit' => 1)));
        $this->assertEquals(array(
            'log_conversion' => 1,
            'log_conversion_item' => 0,
            'log_link_visit_action' => 11,
            'log_visit' => 1,
            'log_foo_bar_baz' => 2,
            'log_foo_bar' => 2,
            'log_foo' => 2
        ), $result);

        $this->assertEmpty($this->getVisit(1, 1));
        $this->assertEmpty($this->getLinkAction(1, 1));
        $this->assertEmpty($this->getConversion(1, 1));
        $this->assertEmpty($this->getOneVisit(1, 1, TestLogFoo::TABLE));

        // idvisit 2 still exists
        $this->assertNotEmpty($this->getVisit(1, 2));
        $this->assertNotEmpty($this->getLinkAction(1, 2));
        $this->assertNotEmpty($this->getOneVisit(1, 2, TestLogFoo::TABLE));
    }

    public function test_deleteExport_deleteAllVisits()
    {
        $this->theFixture->setUpWebsites();
        $this->theFixture->trackVisits($idSite = 1, 1);
        $this->theFixture->trackVisits($idSite = 2, 1);

        $this->assertNotEmpty($this->getAllVisits());
        $this->assertNotEmpty($this->getAllLinkActions());
        $this->assertNotEmpty($this->getAllConversions());
        $this->assertNotEmpty($this->getAllConversionItems());
        $this->assertNotEmpty($this->getAllLogFoo());
        $this->assertNotEmpty($this->getAllLogFooBar());
        $this->assertNotEmpty($this->getAllLogFooBarBaz());

        $visits = $this->getAllVisits();
        $result = $this->dataSubjects->deleteDataSubjects($visits);
        $this->assertEquals(array(
            'log_conversion' => 32,
            'log_conversion_item' => 8,
            'log_link_visit_action' => 232,
            'log_visit' => 64,
            'log_foo_bar_baz' => 2,
            'log_foo_bar' => 3,
            'log_foo' => 3
        ), $result);

        $this->assertSame(array(), $this->getAllVisits());
        $this->assertSame(array(), $this->getAllLinkActions());
        $this->assertSame(array(), $this->getAllConversions());
        $this->assertSame(array(), $this->getAllConversionItems());
        $this->assertSame(array(), $this->getAllLogFoo());
        $this->assertSame(array(), $this->getAllLogFooBar());
        $this->assertSame(array(), $this->getAllLogFooBarBaz());
    }

    private function getFromTable($table)
    {
        $rows = Db::fetchAll('SELECT * from ' . Common::prefixTable($table));
        return $rows;
    }

    private function getAllVisits()
    {
        return $this->getFromTable('log_visit');
    }

    private function getAllLinkActions()
    {
        return $this->getFromTable('log_link_visit_action');
    }

    private function getAllConversions()
    {
        return $this->getFromTable('log_conversion');
    }

    private function getAllConversionItems()
    {
        return $this->getFromTable('log_conversion_item');
    }

    private function getAllLogFooBar()
    {
        return $this->getFromTable(TestLogFooBarBaz::TABLE);
    }

    private function getAllLogFooBarBaz()
    {
        return $this->getFromTable(TestLogFooBar::TABLE);
    }

    private function getAllLogFoo()
    {
        return $this->getFromTable(TestLogFoo::TABLE);
    }

    private function getOneVisit($idSite, $idVisit, $tableName)
    {
        $rows = Db::fetchAll('SELECT idsite, idvisit from ' . Common::prefixTable($tableName) . ' WHERE idsite = ? and idvisit = ?', array($idSite, $idVisit));
        return $rows;
    }

    private function getVisit($idSite, $idVisit)
    {
        return $this->getOneVisit($idSite, $idVisit, 'log_visit');
    }

    private function getConversion($idSite, $idVisit)
    {
        return $this->getOneVisit($idSite, $idVisit, 'log_conversion');
    }

    private function getLinkAction($idSite, $idVisit)
    {
        return $this->getOneVisit($idSite, $idVisit, 'log_link_visit_action');
    }

}
