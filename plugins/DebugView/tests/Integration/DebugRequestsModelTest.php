<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DebugView\tests\Integration;

use Piwik\Option;
use Piwik\Plugins\DebugView\Dao\RawRequestLog;
use Piwik\Plugins\DebugView\Model\DebugRequests;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group DebugView
 * @group DebugViewDebugRequestsModelTest
 * @group Plugins
 */
class DebugRequestsModelTest extends IntegrationTestCase
{
    /**
     * @var DebugRequests
     */
    private $model;

    public function setUp(): void
    {
        parent::setUp();

        if (class_exists('\Piwik\Plugins\TagManager\TagManager')) {
            \Piwik\Plugins\TagManager\TagManager::$enableAutoContainerCreation = false;
        }

        Fixture::createSuperUser();
        FakeAccess::$superUser = true;

        $this->model = new DebugRequests(new RawRequestLog());
    }

    public function testMarkSiteActiveArmsTheSiteForTheActiveWindow()
    {
        $this->model->markSiteActive(5);

        $until = $this->model->getActiveUntilTimestamp(5);
        $this->assertEqualsWithDelta(time() + DebugRequests::ACTIVE_SECONDS, $until, 10);
    }

    public function testMarkSiteActiveDoesNotRewriteTheOptionWhileFreshlyArmed()
    {
        $this->model->markSiteActive(5);
        $stored = Option::get(DebugRequests::OPTION_ACTIVE_PREFIX . '5');

        // a second poll seconds later must not change the stored value at all
        $this->model->markSiteActive(5);

        $this->assertSame($stored, Option::get(DebugRequests::OPTION_ACTIVE_PREFIX . '5'));
    }

    public function testMarkSiteActiveNeverTouchesOtherSitesState()
    {
        // one option row per site: arming site 5 must not read, rewrite or
        // prune site 7's state, so concurrent viewers cannot lose updates
        $otherUntil = time() + 5000;
        Option::set(DebugRequests::OPTION_ACTIVE_PREFIX . '7', (string) $otherUntil);

        $this->model->markSiteActive(5);

        $this->assertSame($otherUntil, $this->model->getActiveUntilTimestamp(7));
        $this->assertGreaterThan(time(), $this->model->getActiveUntilTimestamp(5));
    }

    public function testGetActiveUntilTimestampIsZeroWhenNeverArmed()
    {
        $this->assertSame(0, $this->model->getActiveUntilTimestamp(5));
    }

    public function testTrimAllSitesDropsExpiredArmingOptionsButKeepsActiveOnes()
    {
        Option::set(DebugRequests::OPTION_ACTIVE_PREFIX . '7', (string) (time() - 100));
        $this->model->markSiteActive(5);

        $this->model->trimAllSites();

        $this->assertSame(0, $this->model->getActiveUntilTimestamp(7));
        $this->assertGreaterThan(time(), $this->model->getActiveUntilTimestamp(5));
    }

    public function testIsSiteActiveForTrackerReflectsTheArmedState()
    {
        $this->assertFalse($this->model->isSiteActiveForTracker(5));

        $this->model->markSiteActive(5);

        $this->assertTrue($this->model->isSiteActiveForTracker(5));
        $this->assertFalse($this->model->isSiteActiveForTracker(6), 'other sites stay inactive');
    }

    public function testIsSiteActiveForTrackerIsFalseOnceTheWindowExpired()
    {
        Option::set(DebugRequests::OPTION_ACTIVE_PREFIX . '5', (string) (time() - 1));

        $this->assertFalse($this->model->isSiteActiveForTracker(5));
    }

    public function testInsertFromTrackerStoresAllGroupsAndTheActionType()
    {
        $now = time();
        $this->model->insertFromTracker(1, 11, 101, $now, ['a' => 'b'], ['ua' => 'x'], ['auth' => false], 94);

        $rows = $this->model->getForSite(1, $now - 60);
        $this->assertCount(1, $rows);

        $decoded = $this->model->decodeStoredParameters($rows[0]['parameters']);
        $this->assertSame(['a' => 'b'], $decoded['query']);
        $this->assertSame(['ua' => 'x'], $decoded['defaults']);
        $this->assertSame(['auth' => false], $decoded['other']);
        $this->assertSame(94, $decoded['actionType']);
        $this->assertNull($decoded['bot']);
    }

    public function testInsertFromTrackerStoresTheBotGroupWhenGiven()
    {
        $now = time();
        $this->model->insertFromTracker(1, null, null, $now, ['a' => 'b'], [], [], null, ['name' => 'Googlebot']);

        $rows = $this->model->getForSite(1, $now - 60);
        $this->assertCount(1, $rows);

        $decoded = $this->model->decodeStoredParameters($rows[0]['parameters']);
        $this->assertSame(['name' => 'Googlebot'], $decoded['bot']);
    }

    public function testInsertFromTrackerTruncatesOversizedParameterValuesInAllGroups()
    {
        $now = time();
        $longValue = str_repeat('v', DebugRequests::MAX_PARAM_VALUE_LENGTH + 50);

        $this->model->insertFromTracker(
            1,
            null,
            null,
            $now,
            ['url' => $longValue],
            ['userAgent' => $longValue],
            [],
            null,
            ['name' => $longValue]
        );

        $rows = $this->model->getForSite(1, $now - 60);
        $this->assertCount(1, $rows);

        // what is actually stored ends at the limit plus the marker — for the
        // query, default and bot groups alike
        $decoded = $this->model->decodeStoredParameters($rows[0]['parameters']);
        $expected = str_repeat('v', DebugRequests::MAX_PARAM_VALUE_LENGTH) . DebugRequests::TRUNCATION_MARKER;
        $this->assertSame($expected, $decoded['query']['url']);
        $this->assertSame($expected, $decoded['defaults']['userAgent']);
        $this->assertSame($expected, $decoded['bot']['name']);
    }

    public function testInsertFromTrackerDropsPayloadsOverTheTotalSizeLimit()
    {
        // 100 keys of maximum allowed value length exceed MAX_PARAMS_LENGTH even
        // after per-value truncation, so the whole row is dropped
        $params = [];
        for ($i = 0; $i < 100; $i++) {
            $params['k' . $i] = str_repeat('v', DebugRequests::MAX_PARAM_VALUE_LENGTH);
        }

        $this->model->insertFromTracker(1, null, null, time(), $params);

        $this->assertSame([], $this->model->getForSite(1, 0));
    }

    public function testGetForSiteNeverReturnsMoreThanThePerSiteCap()
    {
        $now = time();
        for ($i = 0; $i < DebugRequests::MAX_ROWS_PER_SITE + 20; $i++) {
            $this->model->insertFromTracker(1, null, null, $now, ['n' => (string) $i]);
        }

        $rows = $this->model->getForSite(1, 0);

        $this->assertCount(DebugRequests::MAX_ROWS_PER_SITE, $rows);
        // the newest rows win, returned chronologically
        $first = $this->model->decodeStoredParameters($rows[0]['parameters']);
        $last = $this->model->decodeStoredParameters($rows[count($rows) - 1]['parameters']);
        $this->assertSame('20', $first['query']['n']);
        $this->assertSame((string) (DebugRequests::MAX_ROWS_PER_SITE + 19), $last['query']['n']);
    }

    public function testTrimSiteCapsTheGivenSiteAndLeavesOthersAlone()
    {
        $now = time();
        for ($i = 0; $i < DebugRequests::MAX_ROWS_PER_SITE + 5; $i++) {
            $this->model->insertFromTracker(1, null, null, $now, ['n' => (string) $i]);
        }
        $this->model->insertFromTracker(2, null, null, $now, ['n' => 'other']);

        $deleted = $this->model->trimSite(1);

        $this->assertSame(5, $deleted);
        $this->assertCount(DebugRequests::MAX_ROWS_PER_SITE, $this->model->getForSite(1, 0));
        $this->assertCount(1, $this->model->getForSite(2, 0));
    }

    public function testTrimAllSitesAppliesAgePurgeAndPerSiteCap()
    {
        $now = time();
        $tooOld = $now - ((\Piwik\Plugins\DebugView\API::MAX_LAST_MINUTES + 5) * 60);

        $this->model->insertFromTracker(1, null, null, $tooOld, ['n' => 'old']);
        for ($i = 0; $i < DebugRequests::MAX_ROWS_PER_SITE + 3; $i++) {
            $this->model->insertFromTracker(1, null, null, $now, ['n' => (string) $i]);
        }

        $deleted = $this->model->trimAllSites();

        $this->assertSame(4, $deleted); // 1 too old + 3 over the cap
        $this->assertCount(DebugRequests::MAX_ROWS_PER_SITE, $this->model->getForSite(1, 0));
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }
}
