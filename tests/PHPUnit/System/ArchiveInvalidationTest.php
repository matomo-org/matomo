<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\API\Request;
use Piwik\ArchiveProcessor\Rules;
use Piwik\Config;
use Piwik\Date;
use Piwik\Plugins\SegmentEditor\API;
use Piwik\Plugins\CoreAdminHome\API as CoreAdminHomeApi;
use Piwik\Tests\Fixtures\VisitsTwoWebsitesWithAdditionalVisits;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * Track visits before website creation date and test that Piwik handles them correctly.
 *
 * This tests that the API method invalidateArchivedReports works correctly, that it deletes data:
 * - on one or multiple websites
 * - for a given set of dates (and optional period)
 *
 * @group Core
 * @group ArchiveInvalidationTest
 */
class ArchiveInvalidationTest extends SystemTestCase
{
    const TEST_SEGMENT = 'pageUrl=@category%252F';

    /**
     * @var VisitsTwoWebsitesWithAdditionalVisits
     */
    public static $fixture = null; // initialized below class definition

    protected $suffix = '_NewDataShouldNotAppear';

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::addSegments();
    }

    private static function addSegments()
    {
        Rules::setBrowserTriggerArchiving(false);
        API::getInstance()->add('segment 1', urlencode(self::TEST_SEGMENT));
        Rules::setBrowserTriggerArchiving(true);
    }

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    /**
     * This should NOT return data for old dates before website creation
     */
    public function getApiForTesting()
    {
        // We test a typical Numeric and a Recursive blob reports
        $apiToCall = ['VisitsSummary.get', 'Actions.getPageUrls'];

        // Build tests for the 2 websites
        return [

            [
                $apiToCall,
                [
                    'idSite'                 => self::$fixture->idSite2,
                    'testSuffix'             => 'Website' . self::$fixture->idSite2 . $this->suffix,
                    'date'                   => self::$fixture->dateTimeFirstDateWebsite2,
                    'periods'                => 'day',
                    'segment'                => self::TEST_SEGMENT,
                    'setDateLastN'           => 4, // 4months ahead
                    'otherRequestParameters' => ['expanded' => 1],
                ],
            ],
            [
                $apiToCall,
                [
                    'idSite'                 => self::$fixture->idSite1,
                    'testSuffix'             => 'Website' . self::$fixture->idSite1 . $this->suffix,
                    'date'                   => self::$fixture->dateTimeFirstDateWebsite1,
                    'periods'                => 'month',
                    'setDateLastN'           => 4, // 4months ahead
                    'otherRequestParameters' => ['expanded' => 1],
                ],
            ],

            [
                $apiToCall,
                [
                    'idSite'                 => self::$fixture->idSite2,
                    'testSuffix'             => 'Website' . self::$fixture->idSite2 . $this->suffix,
                    'date'                   => self::$fixture->dateTimeFirstDateWebsite2,
                    'periods'                => 'month',
                    'segment'                => self::TEST_SEGMENT,
                    'setDateLastN'           => 4, // 4months ahead
                    'otherRequestParameters' => ['expanded' => 1],
                ],
            ],
        ];
    }

    /**
     * test same api w/o invalidating or tracking (which also invalidates), (NewDataShouldNotAppear)
     *
     * @depends      testApi
     * @dataProvider getApiForTesting
     */
    public function testSameApi($api, $params)
    {
        Rules::setBrowserTriggerArchiving(false);
        $this->runApiTests($api, $params);
    }

    /**
     * test same api after invalidating (NewDataShouldAppear)
     *
     * @depends      testApi
     * @depends      testSameApi
     */
    public function testAnotherApi()
    {
        self::$fixture->trackMoreVisits(self::$fixture->idSite1);
        self::$fixture->trackMoreVisits(self::$fixture->idSite2);

        Rules::setBrowserTriggerArchiving(true);

        foreach ($this->getAnotherApiForTesting() as [$api, $params]) {
            $this->runApiTests($api, $params);
        }
    }

    public function testDisablePluginArchive()
    {
        $config                                                   = Config::getInstance();
        $config->General['disable_archiving_segment_for_plugins'] = 'testPlugin';
        $this->assertTrue(Rules::isSegmentPluginArchivingDisabled('testPlugin'));

        $config->General['disable_archiving_segment_for_plugins'] = ['testPlugin', 'testPlugin2'];
        $this->assertTrue(Rules::isSegmentPluginArchivingDisabled('testPlugin'));

        $config->General['disable_archiving_segment_for_plugins'] = 'testPlugin,testPlugin2';
        $this->assertTrue(Rules::isSegmentPluginArchivingDisabled('testPlugin2'));

        $config->General['disable_archiving_segment_for_plugins'] = '';
        $this->assertFalse(Rules::isSegmentPluginArchivingDisabled('testPlugin'));
    }

    public function testDisablePluginArchiveCaseInsensitive()
    {
        $config                                                   = Config::getInstance();
        $config->General['disable_archiving_segment_for_plugins'] = 'testplugin,testplugin2';
        $this->assertTrue(Rules::isSegmentPluginArchivingDisabled('testPlugin'));
    }

    public function testDisablePluginArchiveSpecialCharacters()
    {
        //special characters will not work
        $config                                                   = Config::getInstance();
        $config->General['disable_archiving_segment_for_plugins'] = '!@##$%^^&&**(()_+';
        $this->assertFalse(Rules::isSegmentPluginArchivingDisabled('!@##$%^^&&**(()_+'));
    }

    public function testDisablePluginArchiveBySiteId()
    {
        //test siteId 1 by string
        Config::setSetting('General_1', 'disable_archiving_segment_for_plugins', 'testPlugin');
        $this->assertTrue(Rules::isSegmentPluginArchivingDisabled('testPlugin', 1));

        //test siteId 1 by array
        Config::setSetting('General_1', 'disable_archiving_segment_for_plugins', ['testPlugin', 'testPlugin2']);
        $this->assertTrue(Rules::isSegmentPluginArchivingDisabled('testPlugin', 1));

        //test siteId 1 by string with comma
        Config::setSetting('General_1', 'disable_archiving_segment_for_plugins', 'testPlugin,testPlugin2');
        $this->assertTrue(Rules::isSegmentPluginArchivingDisabled('testPlugin', 1));

        //test empty
        Config::setSetting('General_1', 'disable_archiving_segment_for_plugins', '');
        $this->assertFalse(Rules::isSegmentPluginArchivingDisabled('testPlugin', 1));

        //test siteId 2 not affect siteId1
        Config::setSetting('General_2', 'disable_archiving_segment_for_plugins', 'testPlugin');
        $this->assertFalse(Rules::isSegmentPluginArchivingDisabled('testPlugin', 1));


        //test general setting not affect siteId1
        Config::setSetting('General', 'disable_archiving_segment_for_plugins', 'myPlugin');
        Config::setSetting('General_1', 'disable_archiving_segment_for_plugins', 'testPlugin');
        $this->assertFalse(Rules::isSegmentPluginArchivingDisabled('myPlugin', 1));

        Config::setSetting('General_1', 'disable_archiving_segment_for_plugins', 'testPlugin2');
        $this->assertFalse(Rules::isSegmentPluginArchivingDisabled('testPlugin', 1));
    }

    public function getDatesToParse(): iterable
    {
        yield 'normal range as array' => [
            ['2020-01-01,2020-03-31'],
            'range',
            [['2020-01-01,2020-03-31'], []],
        ];

        yield 'range not provided as array' => [
            '2020-01-31,2020-03-31',
            'range',
            [['2020-01-31,2020-03-31'], []],
        ];

        yield 'multiple ranges' => [
            ['2020-01-01,2020-03-31', '2020-07-01,2020-08-30'],
            'range',
            [['2020-01-01,2020-03-31', '2020-07-01,2020-08-30'], []],
        ];

        yield 'range using lastX keyword' => [
            'last30',
            'range',
            [[Date::factory('now')->subDay(29)->toString() . ',' . Date::factory('now')->toString()], []],
        ];

        yield 'single day' => [
            '2020-01-01',
            'day',
            [['2020-01-01'], []],
        ];

        yield 'single month' => [
            '2020-04-01',
            'month',
            [['2020-04-01'], []],
        ];

        yield 'multiple days' => [
            '2020-05-01,2020-05-02',
            'day',
            [[Date::factory('2020-05-01'), Date::factory('2020-05-02')], []],
        ];

        yield 'magic day keywords' => [
            'today,yesterday',
            'period' => 'day',
            [[Date::factory('today'), Date::factory('yesterday')], []],
        ];

        yield 'valid and invalid days' => [
            '2022-12-12,2022-31-15',
            'period' => 'day',
            [['2022-12-12'], ['2022-31-15']],
        ];

        yield 'valid and invalid months' => [
            '2022-12-12,2022-31-15',
            'period' => 'month',
            [['2022-12-12'], ['2022-31-15']],
        ];

        yield 'valid and invalid ranges' => [
            ['2022-12-12,2022-12-15', '2020-12-12,2019-01-01'],
            'period' => 'range',
            [['2022-12-12,2022-12-15'], ['2020-12-12,2019-01-01']],
        ];

        yield 'invalid text' => [
            'abcdef',
            'period' => 'day',
            [[], ['abcdef']],
        ];

        yield 'invalid number' => [
            167389494,
            'period' => 'day',
            [[], [167389494]],
        ];

        yield 'invalid day' => [
            '2020-02-31',
            'period' => 'day',
            [[], ['2020-02-31']],
        ];

        yield 'invalid range' => [
            ['2020-08-15, 2020-08-01'],
            'period' => 'range',
            [[], ['2020-08-15, 2020-08-01']],
        ];

        yield 'another invalid range' => [
            ['2020-08-15,2020-08-01,2020-09-01'],
            'period' => 'range',
            [[], ['2020-08-15,2020-08-01,2020-09-01']],
        ];
    }

    /**
     * @dataProvider getDatesToParse
     */
    public function testDatesCorrectlyParsed($dates, $period, $expected)
    {
        // Test API
        $r = new Request(
            [
                'module'  => 'API',
                'method'  => 'CoreAdminHome.invalidateArchivedReports',
                'idSites' => self::$fixture->idSite1,
                'period'  => $period,
                'dates'   => $dates,
            ],
            []
        );

        $this->assertApiResponseHasNoError($r->process());

        // Test date parsing method
        $api        = CoreAdminHomeApi::getInstance();
        $reflection = new \ReflectionClass(CoreAdminHomeApi::class);
        $method     = $reflection->getMethod('getDatesToInvalidateFromString');
        $method->setAccessible(true);

        $parameters = [$dates, $period];

        $result = $method->invokeArgs($api, $parameters);
        self::assertEquals($expected, $result);
    }

    /**
     * This is called after getApiToTest()
     * We invalidate old reports and check that data is now returned for old dates
     */
    public function getAnotherApiForTesting()
    {
        $this->suffix = '_NewDataShouldAppear';
        return $this->getApiForTesting();
    }

    public static function getOutputPrefix()
    {
        return 'Archive_Invalidation';
    }

    protected function invalidateTestArchives()
    {
        $dateToInvalidate1 = new \DateTime(self::$fixture->dateTimeFirstDateWebsite1);

        $r = new Request(
            "module=API&method=CoreAdminHome.invalidateArchivedReports&idSites=" . self::$fixture->idSite1 . "&dates=" . $dateToInvalidate1->format(
                'Y-m-d'
            )
        );
        $this->assertApiResponseHasNoError($r->process());

        // week reports only are invalidated. we test our daily report will show new data, even though weekly reports only are invalidated,
        // because when we track data, it invalidates day periods as well.
        $this->invalidateTestArchive(self::$fixture->idSite2, 'week', self::$fixture->dateTimeFirstDateWebsite2);
    }

    private function invalidateTestArchive($idSite, $period, $dateTime, $cascadeDown = false)
    {
        $dates = new \DateTime($dateTime);
        $dates = $dates->format('Y-m-d');
        $r     = new Request(
            "module=API&method=CoreAdminHome.invalidateArchivedReports&period=$period&idSites=$idSite&dates=$dates&cascadeDown=" . (int)$cascadeDown
        );
        $this->assertApiResponseHasNoError($r->process());
    }
}

ArchiveInvalidationTest::$fixture = new VisitsTwoWebsitesWithAdditionalVisits();
