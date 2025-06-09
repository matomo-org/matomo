<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\Common;
use Piwik\Db;
use Piwik\Plugin\Manager;
use Piwik\Plugins\VisitFrequency\API as VisitFrequencyApi;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Fixtures\SqlDump;
use Piwik\Tests\Framework\Fixture;

/**
 * Tests that Piwik 2.0 works w/ data from Piwik 1.12.
 *
 * @group BackwardsCompatibility1XTest
 * @group Core
 */
class BackwardsCompatibility1XTest extends SystemTestCase
{
    public const FIXTURE_LOCATION = '/tests/resources/piwik-1.13-dump.sql';

    /** @var SqlDump $fixture */
    public static $fixture = null; // initialized below class

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $installedPlugins = Manager::getInstance()->getInstalledPluginsName();

        // ensure all plugins are installed correctly (some plugins database tables would be missing otherwise)
        foreach ($installedPlugins as $installedPlugin) {
            \Piwik\Plugin\Manager::getInstance()->loadPlugin($installedPlugin)->install();
        }

        $result = Fixture::updateDatabase();
        if ($result === false) {
            throw new \Exception("Failed to update pre-2.0 database (nothing to update).");
        }

        // truncate log tables so old data won't be re-archived
        foreach (['log_visit', 'log_link_visit_action', 'log_conversion', 'log_conversion_item'] as $table) {
            Db::query("TRUNCATE TABLE " . Common::prefixTable($table));
        }

        self::trackTwoVisitsOnSameDay();

        // launch archiving
        VisitFrequencyApi::getInstance()->get(1, 'year', '2012-12-29');
    }


    /**
     * add two visits from same visitor on dec. 29
     */
    private static function trackTwoVisitsOnSameDay()
    {
        $t = Fixture::getTracker(1, '2012-12-29 01:01:30', $defaultInit = true, $useLocal = true);
        $t->enableBulkTracking();

        $t->setUrl('http://site.com/index.htm');
        $t->setIp('136.5.3.2');
        $t->doTrackPageView('incredible title!');

        $t->setForceVisitDateTime('2012-12-29 03:01:30');
        $t->setUrl('http://site.com/other/index.htm');
        $t->doTrackPageView('other incredible title!');

        $t->doBulkTrack();
    }

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        // note: not sure why I have to manually activate plugin in order for `./console tests:run BackwardsCompatibility1XTest` to work
        try {
            \Piwik\Plugin\Manager::getInstance()->activatePlugin('DevicesDetection');
        } catch (\Exception $e) {
        }

        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $idSite = 1;
        $dateTime = '2012-03-06 11:22:33';

        // page performance metrics added in Matomo 4
        $performanceMetrics = [
            'sum_time_generation',
            'nb_hits_with_time_generation',
            'min_time_generation',
            'max_time_generation',
            'sum_time_network',
            'nb_hits_with_time_network',
            'min_time_network',
            'max_time_network',
            'sum_time_server',
            'nb_hits_with_time_server',
            'min_time_server',
            'max_time_server',
            'sum_time_transfer',
            'nb_hits_with_time_transfer',
            'min_time_transfer',
            'max_time_transfer',
            'sum_time_dom_processing',
            'nb_hits_with_time_dom_processing',
            'min_time_dom_processing',
            'max_time_dom_processing',
            'sum_time_dom_completion',
            'nb_hits_with_time_dom_completion',
            'min_time_dom_completion',
            'max_time_dom_completion',
            'sum_time_on_load',
            'nb_hits_with_time_on_load',
            'min_time_on_load',
            'max_time_on_load',
            'avg_time_generation',
            'avg_time_network',
            'avg_time_server',
            'avg_time_transfer',
            'avg_time_dom_processing',
            'avg_time_dom_completion',
            'avg_time_on_load',
            'avg_page_load_time',
        ];

        $defaultOptions = [
            'idSite' => $idSite,
            'date'   => $dateTime,
            'disableArchiving' => true,
            'otherRequestParameters' => [
                // when changing this, might also need to change the same line in OneVisitorTwoVisitsTest.php
                'hideColumns' => 'nb_users,sum_bandwidth,nb_hits_with_bandwidth,min_bandwidth,max_bandwidth'
            ],
            'xmlFieldsToRemove' => array_merge([
                'entry_sum_visit_length',
                'sum_visit_length',
                'nb_visits_converted',
                'interactionPosition',
                'pageviewPosition',
            ], $performanceMetrics),
        ];

        /**
         * When Piwik\Tests\System\BackwardsCompatibility1XTest is failing,
         * as this test compares fixtures to OneVisitorTwoVisits* fixtures,
         * sometimes for a given API method that fails to generate the same output as OneVisitorTwoVisits does,
         * we need to add the API below which will cause the fixtures for this API to be created in processed/
         */
        $reportsToCompareSeparately = [
            // those reports generate a different segment as a different raw value was stored that time
            'DevicesDetection.getOsVersions',
            'DevicesDetection.getBrowserVersions',
            'DevicesDetection.getBrowserEngines',
            'DevicesDetection.getBrowsers',
            'Goals.get',

            // Following #9345
            'Actions.getPageUrls',
            'Actions.getDownloads',
            'Actions.getDownload',

            'Actions.getEntryPageUrls',
            'Actions.getExitPageUrls',
            'Actions.getPageTitle',

            // new flag dimensions
            'UserCountry.getCountry',

            'Tour.getLevel',
            'Tour.getChallenges'
        ];

        $apiNotToCall = [
            // in the SQL dump, a referrer is named referer.com, but now in OneVisitorTwoVisits it is referrer.com
            'Referrers',

            // changes made to SQL dump to test VisitFrequency change the day of week
            'VisitTime.getByDayOfWeek',

            // did not exist in Piwik 1.X
            'DevicesDetection.getBrowserEngines',

            // now enriched with goal metrics
            'DevicesDetection.getType',
            'DevicesDetection.getBrand',
            'DevicesDetection.getModel',

            // different result as some plugins have been removed in Matomo 4
            'DevicePlugins.getPlugin',

            // has different output before and after
            'PrivacyManager.getAvailableVisitColumnsToAnonymize',

            // we test VisitFrequency explicitly
            'VisitFrequency.get',

            // do not test as label formats have changed
            'VisitTime.getVisitInformationPerLocalTime',
            'VisitTime.getVisitInformationPerServerTime',

             // the Actions.getPageTitles test fails for unknown reason, so skipping it
             // @todo check if that still the case
            'Actions.getPageTitles',
            'Actions.getEntryPageTitles', // segment values can differ due to missing metadata in old reports
            'Actions.getExitPageTitles',

            // Outlinks now tracked with URL Fragment which was not the case in 1.X
            'Actions.get',
            'Actions.getOutlink',
            'Actions.getOutlinks',

            // system settings such as enable_plugin_update_communication are enabled by default in newest version,
            // but ugpraded Piwik are not
            'CorePluginsAdmin.getSystemSettings',

            // visit length changes slightly with change to previous visitor detection in #13935
            'VisitsSummary.getSumVisitsLength',
            'VisitsSummary.getSumVisitsLengthPretty',

            // did not exist before Matomo 4

            'PagePerformance.get',

            // Did not exist before Matomo 4.11.
            'MultiSites.getAllWithGroups',

            // compare separately, as changes in columns
            'MultiSites.getAll',
            'MultiSites.getOne',
        ];

        if (!Manager::getInstance()->isPluginActivated('CustomVariables')) {
            // includes some columns that are not available when CustomVariables plugin is not available
            $apiNotToCall[] = 'PrivacyManager.getAvailableLinkVisitActionColumnsToAnonymize';
        }

        $apiNotToCall = array_merge($apiNotToCall, $reportsToCompareSeparately);

        $allReportsOptions = $defaultOptions;
        $allReportsOptions['compareAgainst'] = 'OneVisitorTwoVisits';
        $allReportsOptions['apiNotToCall']   = $apiNotToCall;

        return [
            ['all', $allReportsOptions],
            ['MultiSites.getAll', array_merge($defaultOptions, ['xmlFieldsToRemove' => ['hits', 'hits_evolution', 'hits_evolution_trend']])],
            ['MultiSites.getOne', array_merge($allReportsOptions, ['apiNotToCall' => '', 'xmlFieldsToRemove' => ['hits', 'hits_evolution', 'hits_evolution_trend']])],

            ['VisitFrequency.get', ['idSite' => $idSite, 'date' => '2012-03-03', 'setDateLastN' => true,
                                              'disableArchiving' => true, 'testSuffix' => '_multipleDates']],

            ['VisitFrequency.get', ['idSite' => $idSite, 'date' => $dateTime,
                                              'periods' => ['day', 'week', 'month', 'year'],
                                              'disableArchiving' => false]],

            ['VisitFrequency.get', ['idSite' => $idSite, 'date' => '2012-03-06,2012-12-31',
                                              'periods' => ['range'], 'disableArchiving' => true]],

            ['Actions.getPageUrls', ['idSite' => $idSite, 'date' => '2012-03-06,2012-12-31',
                                               'otherRequestParameters' => ['expanded' => '1'],
                                               'xmlFieldsToRemove' => $performanceMetrics,
                                               'testSuffix' => '_expanded',
                                               'periods' => ['range'], 'disableArchiving' => true]],

            ['Actions.getPageUrls', ['idSite' => $idSite, 'date' => '2012-03-06,2012-12-31',
                                               'otherRequestParameters' => ['flat' => '1'],
                                               'xmlFieldsToRemove' => $performanceMetrics,
                                               'testSuffix' => '_flat',
                                               'periods' => ['range'], 'disableArchiving' => true]],

            ['Actions.getPageUrls', ['idSite' => $idSite, 'date' => '2012-03-06',
                                               'otherRequestParameters' => ['idSubtable' => '30'],
                                               'xmlFieldsToRemove' => $performanceMetrics,
                                               'testSuffix' => '_subtable',
                                               'periods' => ['day'], 'disableArchiving' => true]],

            ['VisitFrequency.get', ['idSite' => $idSite, 'date' => '2012-03-03,2012-12-12', 'periods' => ['month'],
                                              'testSuffix' => '_multipleOldNew', 'disableArchiving' => true]],
            [$reportsToCompareSeparately, $defaultOptions],
        ];
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Config' => \Piwik\DI::decorate(function ($previous) {
                $general = $previous->General;
                $general['action_title_category_delimiter'] = "/";
                $previous->General = $general;
                return $previous;
            }),
        ];
    }
}

BackwardsCompatibility1XTest::$fixture = new SqlDump();
BackwardsCompatibility1XTest::$fixture->dumpUrl = PIWIK_INCLUDE_PATH . BackwardsCompatibility1XTest::FIXTURE_LOCATION;
BackwardsCompatibility1XTest::$fixture->tablesPrefix = '';
