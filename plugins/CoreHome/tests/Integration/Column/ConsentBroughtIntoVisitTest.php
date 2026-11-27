<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreHome\tests\Integration\Column;

use Piwik\Common;
use Piwik\Db;
use Piwik\Plugins\CoreHome\Columns\Consent;
use Piwik\Plugins\PrivacyManager\Settings\ConsentAwareAnalytics;
use Piwik\Policy\CnilPolicy;
use Piwik\Policy\PolicyManager;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Cache as TrackerCache;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visit\VisitProperties;
use Piwik\Tracker\Visitor;

/**
 * Whether a request is the one bringing consent into a visit measured without it, so that what
 * was withheld for the visit can be filled in from it - once, and only where consent lifts
 * something a policy enforced.
 *
 * Each case starts from a visit that arrived on the landing page, as stored by the tracker, and
 * asks about a request sent from that page or from a later one.
 *
 * @group CoreHome
 * @group Plugins
 * @group Column
 */
class ConsentBroughtIntoVisitTest extends IntegrationTestCase
{
    private const LANDING_URL = 'http://example.com/landing';

    /** @var int */
    private $idSite;

    /** @var int */
    private $entryIdActionUrl;

    public function setUp(): void
    {
        parent::setUp();

        FakeAccess::$superUser = true;

        $this->idSite = Fixture::createWebsite('2024-01-01 00:00:00', 0, 'Consent', 'http://example.com');

        $tracker = Fixture::getTracker($this->idSite, '2024-01-02 10:00:00', $defaultInit = true, $useLocal = true);
        $tracker->setUrl(self::LANDING_URL);
        Fixture::checkResponse($tracker->doTrackPageView('Landing'));

        $this->entryIdActionUrl = (int) Db::fetchOne(
            'SELECT visit_entry_idaction_url FROM ' . Common::prefixTable('log_visit') . ' WHERE idsite = ?',
            [$this->idSite]
        );
    }

    public function tearDown(): void
    {
        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, false, $this->idSite);
        TrackerCache::deleteTrackerCache();

        parent::tearDown();
    }

    /**
     * @return iterable<string, array{0: bool, 1: bool, 2: ?string, 3: ?int, 4: string, 5: int, 6: bool}>
     */
    public function getCases(): iterable
    {
        // policy, opted in, consent sent, consent stored on the visit, page, interactions, expected
        yield 'consent on the landing page of a visit measured without it' => [true, true, '1', null, self::LANDING_URL, 1, true];
        yield 'no consent sent' => [true, true, null, null, self::LANDING_URL, 1, false];
        yield 'consent=0 is not consent' => [true, true, '0', null, self::LANDING_URL, 1, false];
        yield 'consent=true is not consent' => [true, true, 'true', null, self::LANDING_URL, 1, false];
        yield 'a visit that already consented' => [true, true, '1', 1, self::LANDING_URL, 1, false];
        yield 'after the visitor moved on' => [true, true, '1', null, 'http://example.com/other', 2, false];
        yield 'no policy restriction for consent to lift' => [false, true, '1', null, self::LANDING_URL, 1, false];
        yield 'a site that does not measure consent-aware analytics' => [true, false, '1', null, self::LANDING_URL, 1, false];
    }

    /**
     * @dataProvider getCases
     */
    public function testIsBroughtIntoVisitByOnlyTheRequestThatBringsConsentToTheVisit(
        bool $policy,
        bool $optedIn,
        ?string $consent,
        ?int $storedConsent,
        string $url,
        int $interactions,
        bool $expected
    ): void {
        ConsentAwareAnalytics::setEnabled($optedIn, $this->idSite);
        PolicyManager::setPolicyActiveStatus(CnilPolicy::class, $policy, $this->idSite);
        TrackerCache::deleteTrackerCache();

        $params = ['idsite' => $this->idSite, 'rec' => 1, 'url' => $url, 'ping' => 1];
        if (null !== $consent) {
            $params['consent'] = $consent;
        }
        $request = new Request($params);

        $visitor = new Visitor(new VisitProperties([
            'consent' => $storedConsent,
            'visit_entry_idaction_url' => $this->entryIdActionUrl,
            'visit_total_interactions' => $interactions,
        ]), $isKnown = true);

        $this->assertSame($expected, Consent::isBroughtIntoVisitBy($request, $visitor, $action = null));
    }

    public function provideContainerConfig()
    {
        return [
            'Piwik\Access' => new FakeAccess(),
        ];
    }

    protected static function configureFixture($fixture)
    {
        parent::configureFixture($fixture);
        $fixture->createSuperUser = true;
    }
}
