<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SegmentEditor\tests\System;

use Piwik\ArchiveProcessor\Rules;
use Piwik\Common;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Db;
use Piwik\Plugins\SegmentEditor\API;
use Piwik\Plugins\SegmentEditor\Model;
use Piwik\Plugins\SegmentEditor\SegmentEditor;
use Piwik\Plugins\VisitsSummary;
use Piwik\Tests\Fixtures\OneVisitorTwoVisits;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\View;
use Piwik\CronArchive\SegmentArchiving;

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

    public const TEST_SEGMENT = 'browserCode==ff';

    public function testApiOutputWhenCustomSegmentUsedWithBrowserArchivingDisabled()
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

    public function testApiOutputWhenRealTimeProcessedSegmentUsedWithBrowserArchivingDisabled()
    {
        $idSegment = API::getInstance()->add('testsegment', self::TEST_SEGMENT, self::$fixture->idSite, $autoArchive = false);

        $storedSegment = API::getInstance()->get($idSegment);
        $this->assertNotEmpty($storedSegment);

        Rules::setBrowserTriggerArchiving(false);

        $segments = Rules::getSegmentsToProcess([self::$fixture->idSite]);
        self::assertTrue(in_array(self::TEST_SEGMENT, $segments)); // auto archive is forced when browser archiving is fully disabled

        $this->runAnyApiTest('VisitsSummary.get', 'realTimeSegmentUnprocessed', [
            'idSite' => self::$fixture->idSite,
            'date' => Date::factory(self::$fixture->dateTime)->toString(),
            'period' => 'week',
            'segment' => self::TEST_SEGMENT,
        ]);
    }

    public function testApiOutputWhenUnprocessedAutoArchiveSegmentUsedWithBrowserArchivingDisabled()
    {
        Rules::setBrowserTriggerArchiving(false);

        $idSegment = API::getInstance()->add('testsegment', self::TEST_SEGMENT, self::$fixture->idSite, $autoArchive = true);

        $storedSegment = API::getInstance()->get($idSegment);
        $this->assertNotEmpty($storedSegment);

        $segments = Rules::getSegmentsToProcess([self::$fixture->idSite]);
        self::assertTrue(in_array(self::TEST_SEGMENT, $segments));

        $this->runAnyApiTest('VisitsSummary.get', 'autoArchiveSegmentUnprocessed', [
            'idSite' => self::$fixture->idSite,
            'date' => Date::factory(self::$fixture->dateTime)->toString(),
            'period' => 'week',
            'segment' => self::TEST_SEGMENT,
        ]);
    }

    public function testApiOutputWhenUnprocessedAutoArchiveSegmentUsedWithBrowserArchivingDisabledAndEncodedSegment()
    {
        Rules::setBrowserTriggerArchiving(false);

        $idSegment = API::getInstance()->add('testsegment', self::TEST_SEGMENT, self::$fixture->idSite, $autoArchive = true);

        $storedSegment = API::getInstance()->get($idSegment);
        $this->assertNotEmpty($storedSegment);

        $segments = Rules::getSegmentsToProcess([self::$fixture->idSite]);
        self::assertTrue(in_array(self::TEST_SEGMENT, $segments));

        $this->runAnyApiTest('VisitsSummary.get', 'autoArchiveSegmentUnprocessedEncoded', [
            'idSite' => self::$fixture->idSite,
            'date' => Date::factory(self::$fixture->dateTime)->toString(),
            'period' => 'week',
            'segment' => urlencode(self::TEST_SEGMENT),
        ]);
    }

    public function testApiOutputWhenPreprocessedSegmentUsedWithBrowserArchivingDisabled()
    {
        Rules::setBrowserTriggerArchiving(false);

        $idSegment = API::getInstance()->add('testsegment', self::TEST_SEGMENT, self::$fixture->idSite, $autoArchive = true);

        $storedSegment = API::getInstance()->get($idSegment);
        $this->assertNotEmpty($storedSegment);

        Rules::setBrowserTriggerArchiving(true);
        VisitsSummary\API::getInstance()->get(
            self::$fixture->idSite,
            'week',
            Date::factory(self::$fixture->dateTime)->toString(),
            self::TEST_SEGMENT
        ); // archive (make sure there's data for actual test)
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

    public function testApiOutputWhenPreprocessedCustomSegmentUsedWithBrowserArchivingDisabled()
    {
        VisitsSummary\API::getInstance()->get(
            self::$fixture->idSite,
            'week',
            Date::factory(self::$fixture->dateTime)->toString(),
            self::TEST_SEGMENT
        ); // archive

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

    public function testApiOutputWhenPreprocessedSegmentUsedWithNoDataAndBrowserArchivingDisabled()
    {
        $this->clearLogData();

        Rules::setBrowserTriggerArchiving(false);

        $idSegment = API::getInstance()->add('testsegment', self::TEST_SEGMENT, self::$fixture->idSite, $autoArchive = true);

        $storedSegment = API::getInstance()->get($idSegment);
        $this->assertNotEmpty($storedSegment);

        VisitsSummary\API::getInstance()->get(
            self::$fixture->idSite,
            'week',
            Date::factory(self::$fixture->dateTime)->toString(),
            self::TEST_SEGMENT
        ); // archive

        $segments = Rules::getSegmentsToProcess([self::$fixture->idSite]);
        self::assertTrue(in_array(self::TEST_SEGMENT, $segments));

        $this->runAnyApiTest('VisitsSummary.get', 'autoArchiveSegmentNoDataPreprocessed', [
            'idSite' => self::$fixture->idSite,
            'date' => Date::factory(self::$fixture->dateTime)->toString(),
            'period' => 'week',
            'segment' => self::TEST_SEGMENT,
        ]);
    }

    public function testApiOutputWhenNoLogDataAndUnprocessedSegmentUsedAndBrowserArchivingDisabled()
    {
        $this->clearLogData();

        Rules::setBrowserTriggerArchiving(false);

        $idSegment = API::getInstance()->add('testsegment', self::TEST_SEGMENT, self::$fixture->idSite, $autoArchive = true);

        $storedSegment = API::getInstance()->get($idSegment);
        $this->assertNotEmpty($storedSegment);

        $segments = Rules::getSegmentsToProcess([self::$fixture->idSite]);
        self::assertTrue(in_array(self::TEST_SEGMENT, $segments));

        $this->runAnyApiTest('VisitsSummary.get', 'noLogDataSegmentUnprocessed', [
            'idSite' => self::$fixture->idSite,
            'date' => Date::factory(self::$fixture->dateTime)->toString(),
            'period' => 'week',
            'segment' => self::TEST_SEGMENT,
        ]);
    }

    public function testApiOutputWhenMultipleSitesRequestedOneWithDataOneNotAndBrowserArchivingDisabled()
    {
        Rules::setBrowserTriggerArchiving(false);

        $idSegment = API::getInstance()->add('testsegment', self::TEST_SEGMENT, $idSite = false, $autoArchive = true);

        $storedSegment = API::getInstance()->get($idSegment);
        $this->assertNotEmpty($storedSegment);

        $segments = Rules::getSegmentsToProcess([self::$fixture->idSite]);
        self::assertTrue(in_array(self::TEST_SEGMENT, $segments));

        $this->runAnyApiTest('VisitsSummary.get', 'noLogDataSegmentUnprocessedMultiSite', [
            'idSite' => 'all',
            'date' => Date::factory(self::$fixture->dateTime)->toString(),
            'period' => 'week',
            'segment' => self::TEST_SEGMENT,
        ]);
    }

    public function testNoDataNotificationUsesSegmentVisibleToCurrentUser()
    {
        Rules::setBrowserTriggerArchiving(false);

        $model = new Model();
        $preprocessedSegment = ['auto_archive' => 1, 'enable_only_idsite' => self::$fixture->idSite, 'deleted' => 0];
        $model->createSegment($preprocessedSegment + [
            'name' => 'Other user segment',
            'definition' => self::TEST_SEGMENT,
            'login' => 'user2',
            'enable_all_users' => 0,
        ]);
        $model->createSegment($preprocessedSegment + [
            'name' => 'Shared segment',
            'definition' => 'visitCount>=1',
            'login' => 'user2',
            'enable_all_users' => 1,
        ]);

        $originalAccess = StaticContainer::getContainer()->get('Piwik\Access');
        $fakeAccess = new FakeAccess($superUser = false, $idSitesAdmin = [], $idSitesView = [self::$fixture->idSite], $identity = 'user1');
        StaticContainer::getContainer()->set('Piwik\Access', $fakeAccess);
        Fixture::loadAllTranslations();

        try {
            $message = $this->getNoDataNotificationMessage(self::TEST_SEGMENT);
            $this->assertStringContainsString('(' . self::TEST_SEGMENT . ')', $message);
            $this->assertStringNotContainsString('Other user segment', $message);

            $message = $this->getNoDataNotificationMessage('visitCount>=1');
            $this->assertStringContainsString('(Shared segment)', $message);
        } finally {
            Fixture::resetTranslations();
            StaticContainer::getContainer()->set('Piwik\Access', $originalAccess);
        }
    }

    public function testAddRealTimeEnabledInApiWhenRealTimeDisabledInConfig()
    {
        $this->expectExceptionMessage('Real time segments are disabled. You need to enable auto archiving.');
        $this->expectException(\Exception::class);
        $config = Config::getInstance();
        $general = $config->General;
        $general['enable_create_realtime_segments'] = 0;
        $config->General = $general;

        API::getInstance()->add('testsegment', self::TEST_SEGMENT, $idSite = false, $autoArchive = false);
    }

    public function testAddRealTimeEnabledInApiWhenRealTimeEnabledInConfigShouldWork()
    {
        $config = Config::getInstance();
        $general = $config->General;
        $general['enable_create_realtime_segments'] = 1;
        $config->General = $general;

        $id = API::getInstance()->add('testsegment', self::TEST_SEGMENT, $idSite = false, $autoArchive = false);
        $this->assertNotEmpty($id);
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
            Config::class => \Piwik\DI::decorate(function (Config $previous) {
                $previous->General['browser_archiving_disabled_enforce'] = 1;
                return $previous;
            }),

            SegmentArchiving::class => \Piwik\DI::autowire()
                ->constructorParameter('beginningOfTimeLastNInYears', 15),
        ];
    }

    private function getNoDataNotificationMessage(string $segment): string
    {
        $backupGet = $_GET;
        $backupQueryString = $_SERVER['QUERY_STRING'] ?? null;

        $_GET = [
            'idSite' => (string) self::$fixture->idSite,
            'period' => 'week',
            'date' => Date::factory(self::$fixture->dateTime)->toString(),
            'segment' => $segment,
        ];
        $_SERVER['QUERY_STRING'] = http_build_query($_GET);

        try {
            $view = new View('@CoreHome/_dataTable');
            $view->notifications = [];
            (new SegmentEditor())->onNoData($view);
        } finally {
            $_GET = $backupGet;
            $_SERVER['QUERY_STRING'] = $backupQueryString;
        }

        $this->assertArrayHasKey(SegmentEditor::NO_DATA_UNPROCESSED_SEGMENT_ID, $view->notifications);
        return $view->notifications[SegmentEditor::NO_DATA_UNPROCESSED_SEGMENT_ID]->message;
    }

    private function clearLogData()
    {
        Db::query('TRUNCATE ' . Common::prefixTable('log_visit'));
        Db::query('TRUNCATE ' . Common::prefixTable('log_link_visit_action'));
        Db::query('TRUNCATE ' . Common::prefixTable('log_conversion'));
    }
}

UnprocessedSegmentsTest::$fixture = new OneVisitorTwoVisits();
