<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SegmentEditor\tests\System;

use Piwik\ArchiveProcessor\Rules;
use Piwik\Common;
use Piwik\Config;
use Piwik\Date;
use Piwik\Db;
use Piwik\Plugins\SegmentEditor\API;
use Piwik\Plugins\VisitsSummary;
use Piwik\Tests\Fixtures\OneVisitorTwoVisits;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group SegmentEditor
 * @group System
 * @group UnprocessedSegmentsTest
 */
class UnprocessedSegmentsTest extends IntegrationTestCase
{
    /**
     * @var OneVisitorTwoVisits
     */
    public static $fixture;

    const TEST_SEGMENT = 'browserCode==ff';

    public function test_apiOutput_whenCustomSegmentUsed_WithBrowserArchivingDisabled()
    {
        Rules::setBrowserTriggerArchiving(false);

        $segments = Rules::getSegmentsToProcess([self::$fixture->idSite]);
        self::assertTrue(!in_array(self::TEST_SEGMENT, $segments));

        $this->runAnyApiTest('VisitsSummary.get', 'customSegmentUnprocessed', [
            'idSite' => self::$fixture->idSite,
            'date' => Date::factory(self::$fixture->dateTime)->toString(),
            'period' => 'week',
            'segment' => self::TEST_SEGMENT,
        ]);
    }

    public function test_apiOutput_whenRealTimeProcessedSegmentUsed_WithBrowserArchivingDisabled()
    {
        $idSegment = API::getInstance()->add('testsegment', self::TEST_SEGMENT, self::$fixture->idSite, $autoArchive = false);

        $storedSegment = API::getInstance()->get($idSegment);
        $this->assertNotEmpty($storedSegment);

        Rules::setBrowserTriggerArchiving(false);

        $segments = Rules::getSegmentsToProcess([self::$fixture->idSite]);
        self::assertTrue(!in_array(self::TEST_SEGMENT, $segments));

        $this->runAnyApiTest('VisitsSummary.get', 'realTimeSegmentUnprocessed', [
            'idSite' => self::$fixture->idSite,
            'date' => Date::factory(self::$fixture->dateTime)->toString(),
            'period' => 'week',
            'segment' => self::TEST_SEGMENT,
        ]);
    }

    public function test_apiOutput_whenUnprocessedAutoArchiveSegmentUsed_WithBrowserArchivingDisabled()
    {
        $idSegment = API::getInstance()->add('testsegment', self::TEST_SEGMENT, self::$fixture->idSite, $autoArchive = true);

        $storedSegment = API::getInstance()->get($idSegment);
        $this->assertNotEmpty($storedSegment);

        Rules::setBrowserTriggerArchiving(false);

        $segments = Rules::getSegmentsToProcess([self::$fixture->idSite]);
        self::assertTrue(in_array(self::TEST_SEGMENT, $segments));

        $this->runAnyApiTest('VisitsSummary.get', 'autoArchiveSegmentUnprocessed', [
            'idSite' => self::$fixture->idSite,
            'date' => Date::factory(self::$fixture->dateTime)->toString(),
            'period' => 'week',
            'segment' => self::TEST_SEGMENT,
        ]);
    }

    public function test_apiOutput_whenUnprocessedAutoArchiveSegmentUsed_WithBrowserArchivingDisabled_AndEncodedSegment()
    {
        $idSegment = API::getInstance()->add('testsegment', self::TEST_SEGMENT, self::$fixture->idSite, $autoArchive = true);

        $storedSegment = API::getInstance()->get($idSegment);
        $this->assertNotEmpty($storedSegment);

        Rules::setBrowserTriggerArchiving(false);

        $segments = Rules::getSegmentsToProcess([self::$fixture->idSite]);
        self::assertTrue(in_array(self::TEST_SEGMENT, $segments));

        $this->runAnyApiTest('VisitsSummary.get', 'autoArchiveSegmentUnprocessedEncoded', [
            'idSite' => self::$fixture->idSite,
            'date' => Date::factory(self::$fixture->dateTime)->toString(),
            'period' => 'week',
            'segment' => urlencode(self::TEST_SEGMENT),
        ]);
    }

    public function test_apiOutput_whenPreprocessedSegmentUsed_WithBrowserArchivingDisabled()
    {
        $idSegment = API::getInstance()->add('testsegment', self::TEST_SEGMENT, self::$fixture->idSite, $autoArchive = true);

        $storedSegment = API::getInstance()->get($idSegment);
        $this->assertNotEmpty($storedSegment);

        VisitsSummary\API::getInstance()->get(self::$fixture->idSite, 'week',
            Date::factory(self::$fixture->dateTime)->toString(), self::TEST_SEGMENT); // archive

        Rules::setBrowserTriggerArchiving(false);

        $segments = Rules::getSegmentsToProcess([self::$fixture->idSite]);
        self::assertTrue(in_array(self::TEST_SEGMENT, $segments));

        $this->runAnyApiTest('VisitsSummary.get', 'autoArchiveSegmentPreprocessed', [
            'idSite' => self::$fixture->idSite,
            'date' => Date::factory(self::$fixture->dateTime)->toString(),
            'period' => 'week',
            'segment' => self::TEST_SEGMENT,
        ]);
    }

    public function test_apiOutput_whenPreprocessedCustomSegmentUsed_WithBrowserArchivingDisabled()
    {
        VisitsSummary\API::getInstance()->get(self::$fixture->idSite, 'week',
            Date::factory(self::$fixture->dateTime)->toString(), self::TEST_SEGMENT); // archive

        Rules::setBrowserTriggerArchiving(false);

        $segments = Rules::getSegmentsToProcess([self::$fixture->idSite]);
        self::assertTrue(!in_array(self::TEST_SEGMENT, $segments));

        $this->runAnyApiTest('VisitsSummary.get', 'customSegmentPreprocessed', [
            'idSite' => self::$fixture->idSite,
            'date' => Date::factory(self::$fixture->dateTime)->toString(),
            'period' => 'week',
            'segment' => self::TEST_SEGMENT,
        ]);
    }

    public function test_apiOutput_whenPreprocessedSegmentUsed_WithNoData_AndBrowserArchivingDisabled()
    {
        $this->clearLogData();

        $idSegment = API::getInstance()->add('testsegment', self::TEST_SEGMENT, self::$fixture->idSite, $autoArchive = true);

        $storedSegment = API::getInstance()->get($idSegment);
        $this->assertNotEmpty($storedSegment);

        VisitsSummary\API::getInstance()->get(self::$fixture->idSite, 'week',
            Date::factory(self::$fixture->dateTime)->toString(), self::TEST_SEGMENT); // archive

        Rules::setBrowserTriggerArchiving(false);

        $segments = Rules::getSegmentsToProcess([self::$fixture->idSite]);
        self::assertTrue(in_array(self::TEST_SEGMENT, $segments));

        $this->runAnyApiTest('VisitsSummary.get', 'autoArchiveSegmentNoDataPreprocessed', [
            'idSite' => self::$fixture->idSite,
            'date' => Date::factory(self::$fixture->dateTime)->toString(),
            'period' => 'week',
            'segment' => self::TEST_SEGMENT,
        ]);
    }

    public function test_apiOutput_whenNoLogDataAndUnprocessedSegmentUsed_AndBrowserArchivingDisabled()
    {
        $this->clearLogData();

        $idSegment = API::getInstance()->add('testsegment', self::TEST_SEGMENT, self::$fixture->idSite, $autoArchive = true);

        $storedSegment = API::getInstance()->get($idSegment);
        $this->assertNotEmpty($storedSegment);

        Rules::setBrowserTriggerArchiving(false);

        $segments = Rules::getSegmentsToProcess([self::$fixture->idSite]);
        self::assertTrue(in_array(self::TEST_SEGMENT, $segments));

        $this->runAnyApiTest('VisitsSummary.get', 'noLogDataSegmentUnprocessed', [
            'idSite' => self::$fixture->idSite,
            'date' => Date::factory(self::$fixture->dateTime)->toString(),
            'period' => 'week',
            'segment' => self::TEST_SEGMENT,
        ]);
    }

    public function test_apiOutput_whenMultipleSitesRequested_OneWithDataOneNot_AndBrowserArchivingDisabled()
    {
        $idSegment = API::getInstance()->add('testsegment', self::TEST_SEGMENT, $idSite = false, $autoArchive = true);

        $storedSegment = API::getInstance()->get($idSegment);
        $this->assertNotEmpty($storedSegment);

        Rules::setBrowserTriggerArchiving(false);

        $segments = Rules::getSegmentsToProcess([self::$fixture->idSite]);
        self::assertTrue(in_array(self::TEST_SEGMENT, $segments));

        $this->runAnyApiTest('VisitsSummary.get', 'noLogDataSegmentUnprocessedMultiSite', [
            'idSite' => 'all',
            'date' => Date::factory(self::$fixture->dateTime)->toString(),
            'period' => 'week',
            'segment' => self::TEST_SEGMENT,
        ]);
    }

    public static function getOutputPrefix()
    {
        return '';
    }

    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }

    public function provideContainerConfig()
    {
        return [
            Config::class => \DI\decorate(function (Config $previous) {
                $previous->General['browser_archiving_disabled_enforce'] = 1;
                return $previous;
            }),
        ];
    }

    private function clearLogData()
    {
        Db::query('TRUNCATE ' . Common::prefixTable('log_visit'));
        Db::query('TRUNCATE ' . Common::prefixTable('log_link_visit_action'));
        Db::query('TRUNCATE ' . Common::prefixTable('log_conversion'));
    }
}

UnprocessedSegmentsTest::$fixture = new OneVisitorTwoVisits();
