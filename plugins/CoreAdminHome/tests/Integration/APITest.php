<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\tests\Integration;

use Piwik\API\Request;
use Piwik\Archive\ArchiveInvalidator;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Db;
use Piwik\Exception\MissingFilePermissionException;
use Piwik\Http\BadRequestException;
use Piwik\Plugins\CoreAdminHome\API;
use Piwik\Plugins\CoreAdminHome\tests\Framework\Mock\FileBackedConfig;
use Piwik\Plugins\CoreAdminHome\TrackingFailuresNotificationSettings;
use Piwik\Plugins\SitesManager\API as SitesManagerAPI;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;

/**
 * @group CoreAdminHome
 * @group APITest
 * @group API
 * @group Plugins
 */
class APITest extends \Piwik\Tests\Framework\TestCase\IntegrationTestCase
{
    /**
     * @var int
     */
    private $idSite;

    /**
     * @var API
     */
    private $api;

    /**
     * @var FileBackedConfig|null
     */
    private $fileBackedConfig;

    public function setUp(): void
    {
        parent::setUp();
        $this->api = API::getInstance();
        for ($i = 0; $i < 5; $i++) {
            Fixture::createWebsite('2014-01-02 03:04:05');
        }
    }

    public function tearDown(): void
    {
        if ($this->fileBackedConfig !== null) {
            $this->fileBackedConfig->deleteFile();
            $this->fileBackedConfig = null;
        }

        parent::tearDown();
    }

    public function testGetTrackingFailuresFailsForViewUser()
    {
        $this->expectException(\Piwik\NoAccessException::class);
        $this->expectExceptionMessage('checkUserHasSomeAdminAccess');

        $this->setUser();
        $this->api->getTrackingFailures();
    }

    public function testGetTrackingFailuresWorksForAdminAndSuperuser()
    {
        $this->setAdminUser();
        $this->assertSame(array(), $this->api->getTrackingFailures());
        $this->setSuperUser();
        $this->api->getTrackingFailures();
        $this->assertSame(array(), $this->api->getTrackingFailures());
    }

    public function testSetTrackingFailuresNotificationSettingsFailsForAdminUser()
    {
        $this->expectException(\Piwik\NoAccessException::class);
        $this->expectExceptionMessage('checkUserHasSuperUserAccess');

        $this->setAdminUser();
        $this->api->setTrackingFailuresNotificationSettings(true, '1');
    }

    public function testSetTrackingFailuresNotificationSettingsStoresOnlyExistingSitesAndLeavesConfigAlone()
    {
        $numConfigSaves = 0;
        $this->replaceConfigWithFileBackedConfig()->onSave(function () use (&$numConfigSaves) {
            $numConfigSaves++;
        });
        $this->setSuperUser();

        $this->api->setTrackingFailuresNotificationSettings(true, '2, 1,2,999');

        $settings = new TrackingFailuresNotificationSettings();
        $this->assertSame([2, 1], $settings->getExcludedSiteIds());
        $this->assertTrue($settings->isEnabled());
        $this->assertSame(0, $numConfigSaves);
    }

    /**
     * @dataProvider getInvalidExcludedIdSites
     */
    public function testSetTrackingFailuresNotificationSettingsRejectsInvalidSiteIdsAndKeepsStoredList(
        string $excludedIdSites
    ) {
        $this->setSuperUser();
        $this->api->setTrackingFailuresNotificationSettings(true, '1,2');

        try {
            $this->api->setTrackingFailuresNotificationSettings(true, $excludedIdSites);
            $this->fail('Expected a BadRequestException');
        } catch (BadRequestException $e) {
            $this->assertSame([1, 2], (new TrackingFailuresNotificationSettings())->getExcludedSiteIds());
        }
    }

    public function getInvalidExcludedIdSites(): array
    {
        return [['abc'], ['1,abc'], ['0'], ['1.5'], ['-2']];
    }

    public function testSetTrackingFailuresNotificationSettingsReadsSiteIdsFromRequest()
    {
        $this->setSuperUser();

        Request::processRequest('CoreAdminHome.setTrackingFailuresNotificationSettings', [
            'enabled' => '1',
            'excludedIdSites' => '3,4',
        ]);

        $this->assertSame([3, 4], (new TrackingFailuresNotificationSettings())->getExcludedSiteIds());
    }

    public function testSetTrackingFailuresNotificationSettingsDisablesAndReEnablesTheEmailInConfig()
    {
        $config = $this->replaceConfigWithFileBackedConfig();
        $this->setSuperUser();

        $this->api->setTrackingFailuresNotificationSettings(false);

        $this->assertEquals(0, $config->readFile()['General']['enable_tracking_failures_notification']);
        $this->assertFalse((new TrackingFailuresNotificationSettings())->isEnabled());

        $this->api->setTrackingFailuresNotificationSettings(true);

        // back to the default value, so the setting is no longer in the local config file
        $this->assertArrayNotHasKey('enable_tracking_failures_notification', $config->readFile()['General'] ?? []);
        $this->assertTrue((new TrackingFailuresNotificationSettings())->isEnabled());
    }

    public function testSetTrackingFailuresNotificationSettingsKeepsStoredListWhenConfigCannotBeSaved()
    {
        $this->setSuperUser();
        $this->api->setTrackingFailuresNotificationSettings(true, '1');

        $config = $this->replaceConfigWithFileBackedConfig();
        $config->onSave(function () use ($config) {
            throw $config->getConfigNotWritableException();
        });

        try {
            $this->api->setTrackingFailuresNotificationSettings(false, '2,3');
            $this->fail('Expected a MissingFilePermissionException');
        } catch (MissingFilePermissionException $e) {
            $this->assertSame([1], (new TrackingFailuresNotificationSettings())->getExcludedSiteIds());
        }
    }

    public function testExcludedSiteIsNoLongerExcludedOnceItIsDeleted()
    {
        $this->setSuperUser();
        $this->api->setTrackingFailuresNotificationSettings(true, '1,2');

        SitesManagerAPI::getInstance()->deleteSite(2);

        $this->assertSame([1], (new TrackingFailuresNotificationSettings())->getExcludedSiteIds());
    }

    private function replaceConfigWithFileBackedConfig(): FileBackedConfig
    {
        $this->fileBackedConfig = FileBackedConfig::replaceTestConfig();

        return $this->fileBackedConfig;
    }

    public function testInvalidateArchivedReportsWarnsAboutGivenUpArchives()
    {
        $this->setSuperUser();
        Db::query(
            'INSERT INTO `' . Common::prefixTable('archive_failures') . "` (idsite, period, date1, date2, archive_hash, name, `report`, attempts,
                    last_error, ts_first_failed, ts_last_failed, ts_retry_after)
                  VALUES (1, 3, '2019-03-01', '2019-03-31', ?, 'done', '', 5, 'error', NOW(), NOW(), NOW())",
            [md5('done|')]
        );

        $output = $this->api->invalidateArchivedReports('1', '2019-12-01', 'day');

        $this->assertStringStartsWith('Warning: 1 archives between 2019-01-01 and 2019-12-31 (site IDs: 1) were given up', (string) end($output));
        $this->assertNotContains(
            end($output),
            $this->api->invalidateArchivedReports('2', '2019-12-01', 'day'),
            'archives of other sites are not mentioned'
        );
    }

    public function testDeleteAllTrackingFailuresFailsForViewUser()
    {
        $this->expectException(\Piwik\NoAccessException::class);
        $this->expectExceptionMessage('checkUserHasSomeAdminAccess');

        $this->setUser();
        $this->api->deleteAllTrackingFailures();
    }

    public function testDeleteAllTrackingFailuresWorksForAdminAndSuperuser()
    {
        $this->expectNotToPerformAssertions();
        $this->setAdminUser();
        $this->api->deleteAllTrackingFailures();
        $this->setSuperUser();
        $this->api->deleteAllTrackingFailures();
    }

    public function testDeleteTrackingFailureFailsForViewUser()
    {
        $this->expectException(\Piwik\NoAccessException::class);
        $this->expectExceptionMessage('checkUserHasAdminAccess');

        $this->setUser();
        $this->api->deleteTrackingFailure(1, 2);
    }

    public function testDeleteTrackingFailureFailsForAdminUserIfNotAdminAccessToThatSite()
    {
        $this->expectException(\Piwik\NoAccessException::class);
        $this->expectExceptionMessage('checkUserHasAdminAccess');

        $this->setAdminUser();
        $this->api->deleteTrackingFailure(2, 2);
    }

    public function testDeleteTrackingFailureWorksForAdminAndSuperuser()
    {
        $this->expectNotToPerformAssertions();
        $this->setAdminUser();
        $this->api->deleteTrackingFailure(1, 2);
        $this->setSuperUser();
        $this->api->deleteTrackingFailure(1, 2);
    }

    /**
     * @dataProvider getReportOnlyRequestsForArchiveOfAllPlugins
     */
    public function testArchiveReportsRejectsReportOnlyArchivingForArchiveOfAllPlugins($plugin, string $report)
    {
        $this->trackPageViewAndArchive();

        StaticContainer::get(ArchiveInvalidator::class)->rememberToInvalidateArchivedReportsLater(1, Date::factory('2020-01-15'));

        $exception = null;
        try {
            $this->api->archiveReports(1, 'day', '2020-01-15', false, $plugin, $report);
        } catch (\Exception $e) {
            $exception = $e;
        }

        $this->assertSame(1, $this->getPageUrlsRowCount());
        $this->assertNotNull($exception);
        $this->assertStringContainsString('only possible for an archive of a single plugin', $exception->getMessage());
    }

    public function getReportOnlyRequestsForArchiveOfAllPlugins(): iterable
    {
        yield 'no plugin' => [false, 'nb_pageviews'];
        yield 'unknown plugin' => ['UnknownPlugin', 'nb_pageviews'];
        yield 'plugin not providing the report' => ['Actions', 'nb_pageviews'];
        yield 'plugin providing the report' => ['Referrers', 'Referrers_type'];
    }

    public function testArchiveReportsArchivesSpecificReportForRangePeriod()
    {
        $this->trackPageViewAndArchive();

        $result = $this->api->archiveReports(1, 'range', '2020-01-14,2020-01-16', false, 'Actions', 'Actions_actions_url');

        $this->assertNotEmpty($result['idarchives']);
        $this->assertSame(1, $this->getPageUrlsRowCount());
    }

    private function trackPageViewAndArchive(): void
    {
        Fixture::createSuperUser(true);
        $this->setSuperUser();

        $tracker = Fixture::getTracker(1, '2020-01-15 12:00:00');
        $tracker->setUrl('http://example.com/page');
        Fixture::checkResponse($tracker->doTrackPageView('page'));

        $this->assertSame(1, $this->getPageUrlsRowCount());
    }

    private function getPageUrlsRowCount(): int
    {
        return Request::processRequest('Actions.getPageUrls', [
            'idSite' => 1,
            'period' => 'day',
            'date' => '2020-01-15',
            'flat' => 1,
        ])->getRowsCount();
    }

    protected function setSuperUser()
    {
        FakeAccess::clearAccess(true);
    }

    protected function setUser()
    {
        FakeAccess::clearAccess(false);
        FakeAccess::$identity = 'testUser';
        FakeAccess::$idSitesView = array(1,3, $this->idSite);
        FakeAccess::$idSitesAdmin = array();
    }

    protected function setAdminUser()
    {
        FakeAccess::clearAccess(false);
        FakeAccess::$identity = 'testUser';
        FakeAccess::$idSitesView = array();
        FakeAccess::$idSitesAdmin = array(1,3, $this->idSite);
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess(),
        );
    }
}
