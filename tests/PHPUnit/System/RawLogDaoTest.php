<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\DataAccess\RawLogDao;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

class CustomRawLogDao extends RawLogDao
{
    public function getTableIdColumns()
    {
        return parent::getTableIdColumns();
    }

    public function getIdFieldForLogTable($logTable)
    {
        return parent::getIdFieldForLogTable($logTable);
    }

    public function getMaxIdsInLogTables()
    {
        return parent::getMaxIdsInLogTables();
    }
}

/**
 * @group Core
 * @group RawLogDao
 * @group RawLogDaoTest
 */
class RawLogDaoTest extends SystemTestCase
{
    /**
     * @var CustomRawLogDao
     */
    private $dao;

    private $idSite = 1;

    public function setUp(): void
    {
        parent::setUp();

        if (!Fixture::siteCreated($this->idSite)) {
            Fixture::createWebsite('2010-00-00 00:00:00');
        }

        $this->dao = new CustomRawLogDao();
    }

    /**
     * @dataProvider getVisitsInTimeFrameData
     */
    public function test_hasSiteVisitsInTimeframe_shouldDetectWhetherThereAreVisitsInCertainTimeframe($from, $to, $idSite, $expectedHasVisits)
    {
        Fixture::getTracker($this->idSite, '2015-01-25 05:35:27')->doTrackPageView('/test');

        $hasVisits = $this->dao->hasSiteVisitsBetweenTimeframe($from, $to, $idSite);
        $this->assertSame($expectedHasVisits, $hasVisits);
    }

    public function test_getIdColumns()
    {
        $expected = array(
            'log_action' => 'idaction',
            'log_conversion' => 'idvisit',
            'log_conversion_item' => 'idvisit',
            'log_link_visit_action' => 'idlink_va',
            'log_visit' => 'idvisit',
        );
        $this->assertSame($expected, $this->dao->getTableIdColumns());
    }

    public function test_getIdFieldForLogTable()
    {
        $this->assertSame('idaction', $this->dao->getIdFieldForLogTable('log_action'));
        $this->assertSame('idlink_va', $this->dao->getIdFieldForLogTable('log_link_visit_action'));
        $this->assertSame('idvisit', $this->dao->getIdFieldForLogTable('log_visit'));
    }

    public function test_getIdFieldForLogTable_whenUnknownTable()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Unknown log table \'log_foobarbaz\'');

        $this->dao->getIdFieldForLogTable('log_foobarbaz');
    }

    public function test_getMaxIdsInLogTables()
    {
        $expected = array(
            'log_action' => '2',
            'log_conversion_item' => null,
            'log_conversion' => null,
            'log_link_visit_action' => '11',
            'log_visit' => '1',
        );
        $this->assertEquals($expected, $this->dao->getMaxIdsInLogTables());
    }

    public function getVisitsInTimeFrameData()
    {
        return array(
            array($from = '2015-01-25 05:35:26', $to = '2015-01-25 05:35:27', $this->idSite, $hasVisits = true), // there are two seconds where the timeframe can have visits
            array($from = '2015-01-25 05:35:27', $to = '2015-01-25 05:35:28', $this->idSite, $hasVisits = true),
            array($from = '2015-01-25 05:35:26', $to = '2015-01-25 05:35:28', $this->idSite, $hasVisits = true), // only one sec difference between from and to
            array($from = '2015-01-25 05:35:26', $to = '2015-01-26 05:35:27', $this->idSite, $hasVisits = true),
            array($from = '2015-01-24 05:35:26', $to = '2015-01-26 05:35:27', $this->idSite, $hasVisits = true),
            array($from = '2015-01-25 05:35:26', $to = '2015-01-25 05:35:27', $idSite = 2, $hasVisits = false),  // no because idSite does not match
            array($from = '2015-01-24 05:35:26', $to = '2015-01-25 05:35:27', $idSite = 2, $hasVisits = false),  // ...
            array($from = '2015-01-25 05:35:26', $to = '2015-01-26 05:35:27', $idSite = 2, $hasVisits = false),  // ...
            array($from = '2015-01-24 05:35:26', $to = '2015-01-26 05:35:27', $idSite = 2, $hasVisits = false),  // ... no because not matching idsite
            array($from = '2015-01-24 05:35:26', $to = '2015-01-25 05:35:26', $this->idSite, $hasVisits = false), // time of visit is later
            array($from = '2015-01-25 05:35:28', $to = '2015-01-27 05:35:27', $this->idSite, $hasVisits = false),  // time of visit is earlier
        );
    }
}
