<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\tests\Integration;

use Piwik\Cache;
use Piwik\Common;
use Piwik\Container\StaticContainer;
use Piwik\Date;
use Piwik\Db;
use Piwik\Option;
use Piwik\Piwik;
use Piwik\Plugins\SitesManager\SitesManager;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Class Plugins_SitesManagerTest
 *
 * @group Plugins
 */
class SitesManagerTest extends IntegrationTestCase
{
    /**
     * @var SitesManager
     */
    private $manager;

    private $siteId;

    public function setUp(): void
    {
        parent::setUp();

        // setup the access layer
        FakeAccess::$superUser = true;

        $this->manager = new SitesManager();
        $this->siteId  = Fixture::createWebsite('2014-03-03 00:00:00');
    }

    public function testOnSiteDeletedShouldClearSiteCache()
    {
        $cache = Cache::getLazyCache();
        $cache->save($this->siteId, 'testcontent');

        $this->manager->onSiteDeleted($this->siteId);

        $this->assertFalse($cache->contains($this->siteId));
    }

    public function testOnSiteDeletedShouldRemoveRememberedArchiveReports()
    {
        $archive = StaticContainer::get('Piwik\Archive\ArchiveInvalidator');
        $archive->rememberToInvalidateArchivedReportsLater($this->siteId, Date::factory('2014-04-05'));
        $archive->rememberToInvalidateArchivedReportsLater($this->siteId, Date::factory('2014-04-06'));
        $archive->rememberToInvalidateArchivedReportsLater(4949, Date::factory('2014-04-05'));

        $remembered = $archive->getRememberedArchivedReportsThatShouldBeInvalidated();
        $this->assertCount(2, $remembered);

        sort($remembered['2014-04-05']);
        $this->assertSame([$this->siteId, 4949], $remembered['2014-04-05']);

        sort($remembered['2014-04-06']);
        $this->assertSame([$this->siteId], $remembered['2014-04-06']);

        $this->manager->onSiteDeleted($this->siteId);

        $expected = [
            '2014-04-05' => [4949],
        ];
        $this->assertEquals($expected, $archive->getRememberedArchivedReportsThatShouldBeInvalidated());
    }

    public function testShouldShowEmptySiteMessageReturnsFalseIfThereIsNoIdSite()
    {
        $this->assertFalse(SitesManager::shouldShowEmptySiteMessage(0));
    }

    public function testShouldShowEmptySiteMessageReturnsFalseIfAVisitWasTrackedInThePast()
    {
        $tracker = Fixture::getTracker($this->siteId, '2015-02-04 04:12:35');
        $tracker->setUrl('http://example.com/');
        Fixture::checkResponse($tracker->doTrackPageView('a test title'));

        $this->assertEquals(false, Option::get('SitesManagerHadTrafficInPast_' . $this->siteId));

        $this->assertFalse(SitesManager::shouldShowEmptySiteMessage($this->siteId));

        // tracked traffic is remembered, so the screen stays hidden even if visits are later purged
        $this->assertEquals('1', Option::get('SitesManagerHadTrafficInPast_' . $this->siteId));
    }

    public function testShouldShowEmptySiteMessageReturnsFalseIfAVisitWasTrackedAndWasLaterPurged()
    {
        $tracker = Fixture::getTracker($this->siteId, '2015-02-04 04:12:35');
        $tracker->setUrl('http://example.com/');
        Fixture::checkResponse($tracker->doTrackPageView('a test title'));

        $this->assertFalse(SitesManager::shouldShowEmptySiteMessage($this->siteId));

        Db::exec('TRUNCATE ' . Common::prefixTable('log_visit'));

        $this->assertFalse(SitesManager::shouldShowEmptySiteMessage($this->siteId));
    }

    public function testShouldShowEmptySiteMessageReturnsTrueIfThereIsNoData()
    {
        \Zend_Session::$_unitTestEnabled = true;

        $this->assertTrue(SitesManager::shouldShowEmptySiteMessage($this->siteId));
    }

    public function testShouldShowEmptySiteMessageReturnsFalseIfTheMessageWasDismissed()
    {
        \Zend_Session::$_unitTestEnabled = true;

        $session = new \Piwik\Session\SessionNamespace('siteWithoutData');
        $session->ignoreMessage = true;

        $this->assertFalse(SitesManager::shouldShowEmptySiteMessage($this->siteId));
    }

    public function testShouldShowEmptySiteMessageReturnsFalseIfTheEmptySiteCheckIsDisabled()
    {
        \Zend_Session::$_unitTestEnabled = true;

        Piwik::addAction('SitesManager.shouldPerformEmptySiteCheck', function (&$shouldPerformEmptySiteCheck) {
            $shouldPerformEmptySiteCheck = false;
        });

        $this->assertFalse(SitesManager::shouldShowEmptySiteMessage($this->siteId));
    }

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }
}
