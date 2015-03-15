<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\tests\Integration;

use Piwik\Access;
use Piwik\Cache;
use Piwik\Archive\ArchiveInvalidator;
use Piwik\Date;
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

    public function setUp()
    {
        parent::setUp();

        // setup the access layer
        $pseudoMockAccess = new FakeAccess;
        FakeAccess::$superUser = true;
        Access::setSingletonInstance($pseudoMockAccess);

        $this->manager = new SitesManager();
        $this->siteId  = Fixture::createWebsite('2014-03-03 00:00:00');
    }

    public function test_onSiteDeleted_shouldClearSiteCache()
    {
        $cache = Cache::getLazyCache();
        $cache->save($this->siteId, 'testcontent');

        $this->manager->onSiteDeleted($this->siteId);

        $this->assertFalse($cache->contains($this->siteId));
    }

    public function test_onSiteDeleted_shouldRemoveRememberedArchiveReports()
    {
        $archive = new ArchiveInvalidator();
        $archive->rememberToInvalidateArchivedReportsLater($this->siteId, Date::factory('2014-04-05'));
        $archive->rememberToInvalidateArchivedReportsLater($this->siteId, Date::factory('2014-04-06'));
        $archive->rememberToInvalidateArchivedReportsLater(4949, Date::factory('2014-04-05'));

        $expected = array(
            '2014-04-05' => array($this->siteId, 4949),
            '2014-04-06' => array($this->siteId)
        );

        $this->assertEquals($expected, $archive->getRememberedArchivedReportsThatShouldBeInvalidated());

        $this->manager->onSiteDeleted($this->siteId);

        $expected = array(
            '2014-04-05' => array(4949)
        );
        $this->assertEquals($expected, $archive->getRememberedArchivedReportsThatShouldBeInvalidated());
    }

}
