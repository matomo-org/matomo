<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Tour\tests\System;

use Piwik\API\Request;
use Piwik\Plugins\ScheduledReports\ScheduledReports;
use Piwik\Plugins\Tour\Dao\DataFinder;
use Piwik\Plugins\Tour\tests\Fixtures\SimpleFixtureTrackFewVisits;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group Tour
 * @group DataFinderTest
 * @group Plugins
 */
class DataFinderTest extends SystemTestCase
{
    /**
     * @var SimpleFixtureTrackFewVisits
     */
    public static $fixture = null; // initialized below class definition

    /**
     * @var DataFinder
     */
    private $dataFinder;

    public function setUp(): void
    {
        parent::setUp();
        $this->dataFinder = new DataFinder();
    }

    public function test_hasTracked()
    {
        $this->assertTrue($this->dataFinder->hasTrackedData());
    }

    public function test_hasAddedWebsite()
    {
        Fixture::createWebsite('2014-03-04 00:00:00');

        $this->assertFalse($this->dataFinder->hasAddedWebsite('foobar'));
        $this->assertTrue($this->dataFinder->hasAddedWebsite(Fixture::ADMIN_USER_LOGIN));
    }

    public function test_hasAddedSegment()
    {
        $this->assertFalse($this->dataFinder->hasAddedSegment(Fixture::ADMIN_USER_LOGIN));

        Request::processRequest('SegmentEditor.add', array(
            'name' => 'foo', 'definition' => 'visitServerHour==5'
        ));
        $this->assertTrue($this->dataFinder->hasAddedSegment(Fixture::ADMIN_USER_LOGIN));
    }

    public function test_hasAddedOrCustomisedDashboard()
    {
        $this->assertFalse($this->dataFinder->hasAddedOrCustomisedDashboard(Fixture::ADMIN_USER_LOGIN));

        Request::processRequest('Dashboard.createNewDashboardForUser', array(
            'login' => Fixture::ADMIN_USER_LOGIN, 'dashboardName' => 'foo'
        ));
        $this->assertTrue($this->dataFinder->hasAddedOrCustomisedDashboard(Fixture::ADMIN_USER_LOGIN));
    }

    public function test_hasAddedNewEmailReport()
    {
        $this->assertFalse($this->dataFinder->hasAddedNewEmailReport(Fixture::ADMIN_USER_LOGIN));

        Request::processRequest('ScheduledReports.addReport', array(
            'idSite' => self::$fixture->idSite, 'description' => 'foo', 'period' => 'week', 'hour' => 5,
            'reportType' => 'email', 'reportFormat' => 'html', 'reports' => array('MultiSites_getAll'), 'parameters' => array('emailMe' => true, 'evolutionGraph' => false, 'displayFormat' => ScheduledReports::DISPLAY_FORMAT_GRAPHS_ONLY_FOR_KEY_METRICS)
        ));
        $this->assertTrue($this->dataFinder->hasAddedNewEmailReport(Fixture::ADMIN_USER_LOGIN));
    }

    public static function getOutputPrefix()
    {
        return '';
    }

    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }
}

DataFinderTest::$fixture = new SimpleFixtureTrackFewVisits();
