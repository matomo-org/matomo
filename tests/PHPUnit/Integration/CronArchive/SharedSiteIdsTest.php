<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\CronArchive\SharedSiteIds;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Core
 * @group SharedSiteIdsTest
 */
class SharedSiteIdsTest extends IntegrationTestCase
{
    /**
     * @var SharedSiteIds
     */
    private $sharedSiteIds;

    public function setUp(): void
    {
        parent::setUp();

        if (!SharedSiteIds::isSupported()) {
            $this->markTestSkipped('Not supported on this platform');
            return;
        }

        $this->sharedSiteIds = $this->makeSharedSiteIds(array(1,2,5,9));
    }

    public function tearDown(): void
    {
        if (!SharedSiteIds::isSupported()) {
            return;
        }

        $siteIdsToCleanup = $this->makeSharedSiteIds(array());
        $siteIdsToCleanup->setSiteIdsToArchive(array());

        parent::tearDown();
    }

    private function makeSharedSiteIds($siteIds, $optionalKey = SharedSiteIds::OPTION_DEFAULT)
    {
        return new SharedSiteIds($siteIds, $optionalKey);
    }

    public function testConstructWithEmptyValue()
    {
        $this->sharedSiteIds->setSiteIdsToArchive(array());

        $siteIds = new SharedSiteIds(null);
        $this->assertEquals(0, $siteIds->getNumSites());
        $this->assertNull($siteIds->getNextSiteId());
    }

    public function testConstructWithCustomOptionName()
    {
        $first = new SharedSiteIds(array(1, 2), 'SharedSiteIdsToArchive_Test');
        $second = new SharedSiteIds(array(), 'SharedSiteIdsToArchive_Test');
        $this->assertEquals(array(1, 2), $first->getAllSiteIdsToArchive());
        $this->assertEquals(array(1, 2), $second->getAllSiteIdsToArchive());
    }

    public function testGetNumSites()
    {
        $this->assertEquals(4, $this->sharedSiteIds->getNumSites());
    }

    public function testGetAllSiteIdsToArchive()
    {
        $this->assertEquals(array(1,2,5,9), $this->sharedSiteIds->getAllSiteIdsToArchive());
    }

    public function testGetNumProcessedWebsitesGetNextSiteId()
    {
        $this->assertEquals(0, $this->sharedSiteIds->getNumProcessedWebsites());

        $this->assertEquals(1, $this->sharedSiteIds->getNextSiteId());
        $this->assertEquals(1, $this->sharedSiteIds->getNumProcessedWebsites());

        $this->assertEquals(2, $this->sharedSiteIds->getNextSiteId());
        $this->assertEquals(2, $this->sharedSiteIds->getNumProcessedWebsites());

        $this->assertEquals(5, $this->sharedSiteIds->getNextSiteId());
        $this->assertEquals(3, $this->sharedSiteIds->getNumProcessedWebsites());

        $this->assertEquals(9, $this->sharedSiteIds->getNextSiteId());
        $this->assertEquals(4, $this->sharedSiteIds->getNumProcessedWebsites());

        $this->assertNull($this->sharedSiteIds->getNextSiteId());
        $this->assertEquals(4, $this->sharedSiteIds->getNumProcessedWebsites());
    }

    public function testGetNextSiteIdShouldDetectWhenTheQueueHasBeenResetMeanwhile()
    {
        $this->assertEquals(1, $this->sharedSiteIds->getNextSiteId());
        $this->assertEquals(2, $this->sharedSiteIds->getNextSiteId());

        // we fake to reset the sharedSiteIds by another process
        $this->sharedSiteIds->setQueueWasReset();

        // it detects that sites must have been processed by now
        $this->assertNull($this->sharedSiteIds->getNextSiteId());
    }

    public function testGetNextSiteIdQueueWithOnlyOneSite()
    {
        $sharedSiteIds = $this->makeSharedSiteIds(array(1), 'mytest');

        $this->assertEquals(1, $sharedSiteIds->getNextSiteId());
        $this->assertNull($sharedSiteIds->getNextSiteId());

        // still returns null even when calling it again
        $this->assertNull($sharedSiteIds->getNextSiteId());
    }

    public function testUsingMultipleSharedSiteIds()
    {
        $second = new SharedSiteIds(array(7,9,11,6,1,2));

        // should ignore his queue and help processing the existing queue
        $this->assertEquals(4, $second->getNumSites());
        $this->assertEquals(4, $this->sharedSiteIds->getNumSites());

        $this->assertEquals(array(1,2,5,9), $second->getAllSiteIdsToArchive());
        $this->assertEquals(1, $second->getNextSiteId());
        $this->assertEquals(1, $second->getNumProcessedWebsites());

        $this->assertEquals(array(2,5,9), $this->sharedSiteIds->getAllSiteIdsToArchive());
        $this->assertEquals(2, $this->sharedSiteIds->getNextSiteId());
        $this->assertEquals(2, $this->sharedSiteIds->getNumProcessedWebsites());

        $this->assertEquals(array(5,9), $second->getAllSiteIdsToArchive());
        $this->assertEquals(5, $second->getNextSiteId());
        $this->assertEquals(3, $second->getNumProcessedWebsites());

        $this->assertEquals(array(9), $this->sharedSiteIds->getAllSiteIdsToArchive());
        $this->assertEquals(9, $this->sharedSiteIds->getNextSiteId());
        $this->assertEquals(4, $this->sharedSiteIds->getNumProcessedWebsites());

        $this->assertNull($second->getNextSiteId());
        $this->assertEquals(4, $second->getNumProcessedWebsites());
        $this->assertEquals(array(), $second->getAllSiteIdsToArchive());

        $this->assertNull($this->sharedSiteIds->getNextSiteId());
        $this->assertEquals(4, $this->sharedSiteIds->getNumProcessedWebsites());
        $this->assertEquals(array(), $this->sharedSiteIds->getAllSiteIdsToArchive());
    }

    public function testUsingMultipleSharedSiteIdsDetectsFinishedAlready()
    {
        $this->sharedSiteIds = $this->makeSharedSiteIds(array(1), 'test');

        // should ignore his queue and help processing the existing queue
        $this->assertEquals(1, $this->sharedSiteIds->getNumSites());

        // process the first and only site, the queue should be empty afterwards and will be reset next time
        $this->assertEquals(1, $this->sharedSiteIds->getNextSiteId());

        $second = $this->makeSharedSiteIds(array(1), 'test');
        $this->assertEquals(1, $second->getNumSites()); // now the second will init the sites back

        // should return null as it already processed site 1 before meaning there must have been a "reset" of sites
        // within one archive run we do not want to process same siteID twice as we prefer the archiver to exit and then
        // the next archiver works on that site again. Otherwise there could be race conditions where a core:archive
        // process basically never ends
        $this->assertNull($this->sharedSiteIds->getNextSiteId());
    }
}
