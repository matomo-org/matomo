<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CustomDimensions\tests\Integration;

use Piwik\DataTable;
use Piwik\Metrics;
use Piwik\Plugins\CustomDimensions\API as CustomDimensionsApi;
use Piwik\Plugins\CustomDimensions\tests\Fixtures\VisitDimensionSetAfterConversion;
use Piwik\Plugins\Goals\API as GoalsApi;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * A visit-scoped custom dimension keeps the last value recorded during the visit, while a conversion
 * keeps the value the visit carried at the moment it converted. When the dimension is only sent
 * after the goal converts, those two are not merely different, they are "the tagged value" and
 * "nothing at all".
 *
 * @group CustomDimensions
 * @group Plugins
 */
class VisitDimensionSetAfterConversionTest extends IntegrationTestCase
{
    /**
     * @var VisitDimensionSetAfterConversion
     */
    public static $fixture;

    private const DAY = '2026-08-17';

    /**
     * The fixture tracks at a fixed date weeks in the past, which the tracker only accepts from an
     * authenticated request. IntegrationTestCase turns the super user off by default, and without one
     * Fixture::getTokenAuth() hands the tracker an empty token and every request is rejected.
     *
     * @param \Piwik\Tests\Framework\Fixture $fixture
     */
    protected static function configureFixture($fixture): void
    {
        parent::configureFixture($fixture);

        $fixture->createSuperUser = true;
    }

    /**
     * All three visits end under the tagged value, whichever order the two events happened in.
     */
    public function testEveryVisitIsCountedUnderTheValueItEndedWith(): void
    {
        $row = $this->taggedRow();

        self::assertNotNull($row, 'no row for ' . VisitDimensionSetAfterConversion::DIMENSION_VALUE);
        self::assertSame(3, (int) $row->getColumn(Metrics::INDEX_NB_VISITS));
    }

    /**
     * Only the two conversions that happened after the visit was tagged are credited to the value.
     */
    public function testOnlyConversionsThatHappenedAfterTaggingAreCreditedToTheValue(): void
    {
        self::assertSame(2, $this->conversionsOf($this->taggedRow()));
    }

    /**
     * The conversion that happened before the dimension was ever sent is not moved to another row,
     * it is absent from the report: aggregateFromConversions() filters on
     * `log_conversion.custom_dimension_N is not null`, and that conversion's snapshot is null.
     *
     * So the report accounts for fewer conversions than the goal actually recorded, and no row in it
     * can be summed to recover the difference. This is the part a reader cannot reconcile, and the
     * assertion to flip once dev-20706 is fixed.
     */
    public function testTheConversionTrackedBeforeTheDimensionIsMissingFromTheReport(): void
    {
        $inReport = 0;
        foreach ($this->report()->getRows() as $row) {
            $inReport += $this->conversionsOf($row);
        }

        self::assertSame(3, $this->conversionsTheGoalRecorded());
        self::assertSame(2, $inReport, 'the report no longer drops a conversion, dev-20706 is fixed');
    }

    private function report(): DataTable
    {
        // the report is archived on demand for this test's day
        $_GET['trigger'] = 'archivephp';

        try {
            $table = CustomDimensionsApi::getInstance()->getCustomDimension(
                self::$fixture->idDimension,
                self::$fixture->idSite,
                'day',
                self::DAY
            );
        } finally {
            unset($_GET['trigger']);
        }

        self::assertInstanceOf(DataTable::class, $table);

        return $table;
    }

    private function taggedRow(): ?DataTable\Row
    {
        $row = $this->report()->getRowFromLabel(VisitDimensionSetAfterConversion::DIMENSION_VALUE);

        return false === $row ? null : $row;
    }

    private function conversionsOf(?DataTable\Row $row): int
    {
        if (null === $row) {
            return 0;
        }

        // the API hands back raw metric indexes here: the report is read straight from the archive,
        // without the processed-report filters that would rename the columns
        $goals = $row->getColumn(Metrics::INDEX_GOALS);

        if (empty($goals)) {
            return 0;
        }

        $conversions = 0;
        foreach ($goals as $goalMetrics) {
            $conversions += (int) ($goalMetrics[Metrics::INDEX_GOAL_NB_CONVERSIONS] ?? 0);
        }

        return $conversions;
    }

    private function conversionsTheGoalRecorded(): int
    {
        $_GET['trigger'] = 'archivephp';

        try {
            $goals = GoalsApi::getInstance()->get(
                self::$fixture->idSite,
                'day',
                self::DAY,
                false,
                self::$fixture->idGoal
            );
        } finally {
            unset($_GET['trigger']);
        }

        return (int) $goals->getFirstRow()->getColumn('nb_conversions');
    }
}

VisitDimensionSetAfterConversionTest::$fixture = new VisitDimensionSetAfterConversion();
