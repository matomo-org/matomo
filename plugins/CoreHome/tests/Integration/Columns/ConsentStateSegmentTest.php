<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreHome\tests\Integration\Columns;

use Piwik\API\Request;
use Piwik\Cache;
use Piwik\Columns\MetricsList;
use Piwik\Common;
use Piwik\Config;
use Piwik\DataTable;
use Piwik\Db;
use Piwik\Metrics\Formatter;
use Piwik\Plugin\ArchivedMetric;
use Piwik\Plugins\CoreHome\Columns\Consent;
use Piwik\Plugins\PrivacyManager\FeatureFlags\ConsentAwareAnalytics;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group CoreHome
 * @group ConsentStateSegmentTest
 * @group Plugins
 */
class ConsentStateSegmentTest extends IntegrationTestCase
{
    private const DATE = '2026-10-01';

    /**
     * @var int
     */
    private $idSite;

    public function setUp(): void
    {
        parent::setUp();

        $this->setConsentAwareAnalyticsFeatureFlag(true);

        Fixture::loadAllTranslations();
        Fixture::createSuperUser();
        $this->idSite = Fixture::createWebsite('2026-01-01 00:00:00');

        // nothing records consent yet, so the stored value is set directly: consented, refused, never reported
        $this->trackVisitsWithConsent($this->idSite, [1, 0, null]);
    }

    /**
     * @dataProvider getSegmentsAndMatchingConsent
     *
     * @param array<int|null> $expectedConsent
     */
    public function testSegmentMatchesOnlyVisitsWithTheSelectedConsentState(string $segment, array $expectedConsent): void
    {
        // archived reports count the matching visits
        $this->assertSame(count($expectedConsent), $this->getArchivedVisitCount($this->idSite, $segment));

        // the visits log shows which visits match, not only how many
        $this->assertSame($expectedConsent, $this->getConsentOfMatchingVisits($segment));
    }

    public function getSegmentsAndMatchingConsent(): iterable
    {
        yield 'consented' => ['consentState==consented', [1]];
        yield 'refused' => ['consentState==refused', [0]];
        yield 'unknown' => ['consentState==unknown', [null]];
        yield 'not consented' => ['consentState!=consented', [0, null]];
        yield 'not refused keeps unknown' => ['consentState!=refused', [1, null]];
        yield 'not unknown' => ['consentState!=unknown', [1, 0]];
        yield 'consented or unknown' => ['consentState==consented,consentState==unknown', [1, null]];
        yield 'combined with another segment' => ['visitorType==new;consentState!=refused', [1, null]];

        // an empty value means no state was reported, as for any other segment
        yield 'empty value' => ['consentState==', [null]];
        yield 'not empty value' => ['consentState!=', [1, 0]];

        // no other value may be read as one of the three states
        yield 'unrecognised value' => ['consentState==yes', []];
        yield 'stored value for consented' => ['consentState==1', []];
        yield 'stored value for refused' => ['consentState==0', []];
        yield 'internal value for unknown' => ['consentState==-1', []];
        yield 'different case' => ['consentState==Consented', []];
        yield 'leading space' => ['consentState==%20consented', []];
        yield 'not an unrecognised value' => ['consentState!=yes', [1, 0, null]];
    }

    /**
     * @dataProvider getUnsupportedOperators
     */
    public function testSegmentRejectsOperatorsOtherThanEqualsAndNotEquals(string $segment): void
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('is not available for the consentState segment');

        $this->getArchivedVisitCount($this->idSite, $segment);
    }

    public function getUnsupportedOperators(): iterable
    {
        yield 'contains' => ['consentState=@consent'];
        yield 'does not contain' => ['consentState!@consent'];
        yield 'starts with' => ['consentState=^consent'];
        yield 'ends with' => ['consentState=$sented'];
        yield 'greater than' => ['consentState>unknown'];
        yield 'at most' => ['consentState<=refused'];
    }

    public function testSegmentKeepsAllVisitsOfASiteWhereEveryVisitConsented(): void
    {
        $idSite = Fixture::createWebsite('2026-01-01 00:00:00');
        $this->trackVisitsWithConsent($idSite, [1, 1, 1]);

        $this->assertSame(3, $this->getArchivedVisitCount($idSite, ''));
        $this->assertSame(3, $this->getArchivedVisitCount($idSite, 'consentState==consented'));
    }

    public function testSegmentIsListedWithItsNameAndValues(): void
    {
        $segments = Request::processRequest('API.getSegmentsMetadata', ['idSites' => [$this->idSite]]);
        $segment = array_values(array_filter($segments, function ($segment) {
            return $segment['segment'] === 'consentState';
        }));

        $this->assertCount(1, $segment);
        $this->assertSame('Consent state', $segment[0]['name']);
        $this->assertSame('consented, refused, unknown', $segment[0]['acceptedValues']);

        $suggestedValues = Request::processRequest('API.getSuggestedValuesForSegment', [
            'segmentName' => 'consentState',
            'idSite' => $this->idSite,
        ]);
        $this->assertSame(['consented', 'refused', 'unknown'], $suggestedValues);
    }

    public function testSegmentIsNotAvailableWhileTheFeatureFlagIsDisabled(): void
    {
        $this->setConsentAwareAnalyticsFeatureFlag(false);

        $segments = Request::processRequest('API.getSegmentsMetadata', ['idSites' => [$this->idSite]]);
        $this->assertNotContains('consentState', array_column($segments, 'segment'));

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Segment 'consentState' is not a supported segment.");

        $this->getArchivedVisitCount($this->idSite, 'consentState==consented');
    }

    public function testNoMetricIsDerivedFromConsent(): void
    {
        $dimensionId = (new Consent())->getId();
        $metricsOfDimension = array_filter(MetricsList::get()->getMetrics(), function ($metric) use ($dimensionId) {
            return $metric instanceof ArchivedMetric && $metric->getDimension() && $metric->getDimension()->getId() === $dimensionId;
        });

        $this->assertSame([], array_values($metricsOfDimension));
    }

    /**
     * @dataProvider getValuesAndLabels
     */
    public function testValuesAreShownWithTheirStateLabel($value, string $expectedLabel): void
    {
        $this->assertSame($expectedLabel, (new Consent())->formatValue($value, $this->idSite, new Formatter()));
    }

    public function getValuesAndLabels(): iterable
    {
        yield 'stored consented' => [1, 'Consented'];
        yield 'stored refused' => [0, 'Refused'];
        yield 'nothing stored' => [null, 'Unknown'];
        yield 'consented' => ['consented', 'Consented'];
        yield 'refused' => ['refused', 'Refused'];
        yield 'unknown' => ['', 'Unknown'];
    }

    private function setConsentAwareAnalyticsFeatureFlag(bool $enabled): void
    {
        $featureFlagConfig = (new ConsentAwareAnalytics())->getName() . '_feature';
        Config::getInstance()->FeatureFlags = [$featureFlagConfig => $enabled ? 'enabled' : 'disabled'];

        // the available segments are cached
        Cache::flushAll();
    }

    /**
     * @param array<int|null> $consentValues
     */
    private function trackVisitsWithConsent(int $idSite, array $consentValues): void
    {
        foreach ($consentValues as $index => $consent) {
            $tracker = Fixture::getTracker($idSite, self::DATE . ' ' . (10 + $index) . ':00:00');
            $tracker->setIp('10.0.0.' . ($index + 1));
            $tracker->setForceNewVisit();
            Fixture::checkResponse($tracker->doTrackPageView('Page ' . $index));

            $idVisit = Db::fetchOne(
                'SELECT MAX(idvisit) FROM ' . Common::prefixTable('log_visit') . ' WHERE idsite = ?',
                [$idSite]
            );
            Db::query(
                'UPDATE ' . Common::prefixTable('log_visit') . ' SET consent = ? WHERE idvisit = ?',
                [$consent, $idVisit]
            );
        }
    }

    private function getArchivedVisitCount(int $idSite, string $segment): int
    {
        /** @var DataTable $table */
        $table = Request::processRequest('VisitsSummary.get', [
            'idSite' => $idSite,
            'period' => 'day',
            'date' => self::DATE,
            'segment' => $segment,
        ]);

        $row = $table->getFirstRow();

        return $row ? (int) $row->getColumn('nb_visits') : 0;
    }

    /**
     * @return array<int|null>
     */
    private function getConsentOfMatchingVisits(string $segment): array
    {
        /** @var DataTable $table */
        $table = Request::processRequest('Live.getLastVisitsDetails', [
            'idSite' => $this->idSite,
            'segment' => $segment,
            'filter_sort_order' => 'asc',
            'filter_limit' => -1,
        ]);

        $idVisits = $table->getColumn('idVisit');
        if (empty($idVisits)) {
            return [];
        }

        $consent = Db::fetchAll(
            'SELECT consent FROM ' . Common::prefixTable('log_visit') . ' WHERE idvisit IN ('
            . implode(',', array_map('intval', $idVisits)) . ') ORDER BY idvisit ASC'
        );

        return array_map(function ($row) {
            return $row['consent'] === null ? null : (int) $row['consent'];
        }, $consent);
    }
}
