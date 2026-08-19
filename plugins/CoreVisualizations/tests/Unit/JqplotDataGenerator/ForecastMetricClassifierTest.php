<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreVisualizations\tests\Unit\JqplotDataGenerator;

use PHPUnit\Framework\TestCase;
use Piwik\Columns\Dimension;
use Piwik\Plugins\CoreVisualizations\JqplotDataGenerator\ForecastMetricClassifier;

/**
 * @group CoreVisualizations
 * @group Evolution
 * @group JqplotDataGenerator
 */
class ForecastMetricClassifierTest extends TestCase
{
    /**
     * @dataProvider getColumnMonotonicityTestData
     * @param string|false $columnUnit
     */
    public function testGetColumnMonotonicityFromName(string $columnName, $columnUnit, string $expected): void
    {
        $classifier = new ForecastMetricClassifier([]);

        self::assertSame($expected, $classifier->getColumnMonotonicity($columnName, $columnUnit));
    }

    /**
     * @return iterable<string, array{string, string|false, string}>
     */
    public function getColumnMonotonicityTestData(): iterable
    {
        yield 'percent unit' => ['custom_metric', '%', ForecastMetricClassifier::MONOTONICITY_FREE];
        yield 'rate metric' => ['bounce_rate', false, ForecastMetricClassifier::MONOTONICITY_FREE];
        yield 'percentage metric' => ['conversion_percentage', false, ForecastMetricClassifier::MONOTONICITY_FREE];
        yield 'average metric' => ['avg_time_on_site', false, ForecastMetricClassifier::MONOTONICITY_FREE];
        yield 'per metric containing nb prefix' => ['nb_actions_per_visit', false, ForecastMetricClassifier::MONOTONICITY_FREE];
        yield 'plain nb metric' => ['nb_visits', false, ForecastMetricClassifier::MONOTONICITY_UP];
        yield 'sum daily nb metric' => ['sum_daily_nb_users', false, ForecastMetricClassifier::MONOTONICITY_UP];
        yield 'exit nb metric' => ['exit_nb_visits', false, ForecastMetricClassifier::MONOTONICITY_UP];
        yield 'sum daily exit nb metric' => ['sum_daily_exit_nb_uniq_visitors', false, ForecastMetricClassifier::MONOTONICITY_UP];
        yield 'lower is better count metric' => ['bounce_count', false, ForecastMetricClassifier::MONOTONICITY_UP];
        yield 'unknown metric defaults to monotonic up' => ['custom_numeric_metric', false, ForecastMetricClassifier::MONOTONICITY_UP];
        yield 'revenue currency metric stays monotonic up' => ['revenue', false, ForecastMetricClassifier::MONOTONICITY_UP];
        yield 'duration sum metric stays monotonic up' => ['sum_time_spent', false, ForecastMetricClassifier::MONOTONICITY_UP];
        yield 'min_ prefix is monotonic down' => ['min_bandwidth', false, ForecastMetricClassifier::MONOTONICITY_DOWN];
        yield 'min_ prefix on event value is monotonic down' => ['min_event_value', false, ForecastMetricClassifier::MONOTONICITY_DOWN];
        yield 'max_ prefix is monotonic max' => ['max_actions', false, ForecastMetricClassifier::MONOTONICITY_MAX];
        yield 'max_ prefix on event value is monotonic max' => ['max_event_value', false, ForecastMetricClassifier::MONOTONICITY_MAX];
        yield 'unique visitors is a deduplicated count' => ['nb_uniq_visitors', false, ForecastMetricClassifier::MONOTONICITY_UNIQUE];
        yield 'users is a deduplicated count' => ['nb_users', false, ForecastMetricClassifier::MONOTONICITY_UNIQUE];
        yield 'entry unique visitors is a deduplicated count' => ['entry_nb_uniq_visitors', false, ForecastMetricClassifier::MONOTONICITY_UNIQUE];
        yield 'exit unique visitors is a deduplicated count' => ['exit_nb_uniq_visitors', false, ForecastMetricClassifier::MONOTONICITY_UNIQUE];
        yield 'fingerprints is a deduplicated count' => ['nb_uniq_fingerprints', false, ForecastMetricClassifier::MONOTONICITY_UNIQUE];
        yield 'new-visitor unique visitors is a deduplicated count' => ['nb_uniq_visitors_new', false, ForecastMetricClassifier::MONOTONICITY_UNIQUE];
        yield 'returning-visitor unique visitors is a deduplicated count' => ['nb_uniq_visitors_returning', false, ForecastMetricClassifier::MONOTONICITY_UNIQUE];
        yield 'ai-agent unique visitors is a deduplicated count' => ['nb_uniq_visitors_ai_agent', false, ForecastMetricClassifier::MONOTONICITY_UNIQUE];
        yield 'sum daily unique visitors stays additive' => ['sum_daily_nb_uniq_visitors', false, ForecastMetricClassifier::MONOTONICITY_UP];
        yield 'sum daily users stays additive' => ['sum_daily_nb_users', false, ForecastMetricClassifier::MONOTONICITY_UP];
        // Unique pageviews/downloads/outlinks count the visits that included an action, and a
        // visit belongs to a single day, so Matomo sums them across sub-periods like any count.
        yield 'unique pageviews stays additive' => ['nb_uniq_pageviews', false, ForecastMetricClassifier::MONOTONICITY_UP];
        yield 'unique downloads stays additive' => ['nb_uniq_downloads', false, ForecastMetricClassifier::MONOTONICITY_UP];
        yield 'unique outlinks stays additive' => ['nb_uniq_outlinks', false, ForecastMetricClassifier::MONOTONICITY_UP];
        yield 'average over unique visitors is a ratio' => ['avg_nb_uniq_visitors', false, ForecastMetricClassifier::MONOTONICITY_FREE];
        yield 'row percentage of unique visitors is a ratio' => ['nb_uniq_visitors_row_percentage', false, ForecastMetricClassifier::MONOTONICITY_FREE];
        // Blob-row counts: the period value is the row count of the re-aggregated sub-period
        // tables, so they are deduplicated counts under names the base-name list cannot see.
        yield 'site search keywords is a blob row count' => ['nb_keywords', false, ForecastMetricClassifier::MONOTONICITY_UNIQUE];
        yield 'distinct countries is a blob row count' => ['UserCountry_distinctCountries', false, ForecastMetricClassifier::MONOTONICITY_UNIQUE];
        yield 'distinct ai chatbots is a blob row count' => ['BotTracking_AIChatbotsUniqueChatbots', false, ForecastMetricClassifier::MONOTONICITY_UNIQUE];
        yield 'distinct search engines is a blob row count' => ['Referrers_distinctSearchEngines', false, ForecastMetricClassifier::MONOTONICITY_UNIQUE];
        yield 'distinct social networks is a blob row count' => ['Referrers_distinctSocialNetworks', false, ForecastMetricClassifier::MONOTONICITY_UNIQUE];
        yield 'distinct ai assistants is a blob row count' => ['Referrers_distinctAIAssistants', false, ForecastMetricClassifier::MONOTONICITY_UNIQUE];
        yield 'distinct referrer keywords is a blob row count' => ['Referrers_distinctKeywords', false, ForecastMetricClassifier::MONOTONICITY_UNIQUE];
        yield 'distinct campaigns is a blob row count' => ['Referrers_distinctCampaigns', false, ForecastMetricClassifier::MONOTONICITY_UNIQUE];
        yield 'distinct websites is a blob row count' => ['Referrers_distinctWebsites', false, ForecastMetricClassifier::MONOTONICITY_UNIQUE];
        yield 'distinct website urls is a blob row count' => ['Referrers_distinctWebsitesUrls', false, ForecastMetricClassifier::MONOTONICITY_UNIQUE];
        // Matched exactly, so neither an unrelated metric that merely reads like one of them nor
        // a ratio built on top of one falls into the deduplicated branch.
        yield 'searches alongside keywords stays additive' => ['nb_searches', false, ForecastMetricClassifier::MONOTONICITY_UP];
        yield 'unrelated distinct-shaped metric stays additive' => ['MyPlugin_distinctThings', false, ForecastMetricClassifier::MONOTONICITY_UP];
        yield 'row percentage of distinct countries is a ratio' => ['UserCountry_distinctCountries_row_percentage', false, ForecastMetricClassifier::MONOTONICITY_FREE];
        // The AI chatbot page/document url counts archive as 0 for non-day periods and are
        // stripped from the API output before a chart can request them, so they stay additive.
        yield 'ai chatbot page urls stays additive' => ['BotTracking_AIChatbotsUniquePageUrls', false, ForecastMetricClassifier::MONOTONICITY_UP];
    }

    /**
     * @dataProvider getColumnMonotonicitySemanticTypeTestData
     */
    public function testGetColumnMonotonicityUsesSemanticTypeForCustomMetrics(
        string $columnName,
        ?string $stubSemanticType,
        string $expected
    ): void {
        $semanticTypes = $stubSemanticType !== null ? [$columnName => $stubSemanticType] : [];
        $classifier = new ForecastMetricClassifier($semanticTypes);

        self::assertSame($expected, $classifier->getColumnMonotonicity($columnName, false));
    }

    /**
     * @return iterable<string, array{string, ?string, string}>
     */
    public function getColumnMonotonicitySemanticTypeTestData(): iterable
    {
        yield 'percent semantic type without name pattern' => ['engagement_score', Dimension::TYPE_PERCENT, ForecastMetricClassifier::MONOTONICITY_FREE];
        yield 'float semantic type without name pattern' => ['session_quality', Dimension::TYPE_FLOAT, ForecastMetricClassifier::MONOTONICITY_FREE];
        yield 'number semantic type without name pattern stays monotonic up' => ['custom_count', Dimension::TYPE_NUMBER, ForecastMetricClassifier::MONOTONICITY_UP];
        yield 'money semantic type stays monotonic up' => ['custom_revenue', Dimension::TYPE_MONEY, ForecastMetricClassifier::MONOTONICITY_UP];
        yield 'duration semantic type without avg_ prefix stays monotonic up' => ['custom_dwell', Dimension::TYPE_DURATION_S, ForecastMetricClassifier::MONOTONICITY_UP];
        yield 'byte semantic type without avg_ prefix stays monotonic up' => ['custom_bandwidth', Dimension::TYPE_BYTE, ForecastMetricClassifier::MONOTONICITY_UP];
        yield 'no semantic type defaults to monotonic up' => ['custom_metric_no_signal', null, ForecastMetricClassifier::MONOTONICITY_UP];
        yield 'min_ prefix wins even with TYPE_NUMBER semantic' => ['min_custom', Dimension::TYPE_NUMBER, ForecastMetricClassifier::MONOTONICITY_DOWN];
        yield 'min_ prefix yields to percent semantic type' => ['min_rate_custom', Dimension::TYPE_PERCENT, ForecastMetricClassifier::MONOTONICITY_FREE];
        yield 'max_ prefix wins even with TYPE_NUMBER semantic' => ['max_custom', Dimension::TYPE_NUMBER, ForecastMetricClassifier::MONOTONICITY_MAX];
        yield 'max_ prefix yields to percent semantic type' => ['max_rate_custom', Dimension::TYPE_PERCENT, ForecastMetricClassifier::MONOTONICITY_FREE];
        yield 'unique visitors wins even with TYPE_NUMBER semantic' => ['nb_uniq_visitors', Dimension::TYPE_NUMBER, ForecastMetricClassifier::MONOTONICITY_UNIQUE];
        yield 'unique visitors yields to percent semantic type' => ['share_nb_uniq_visitors', Dimension::TYPE_PERCENT, ForecastMetricClassifier::MONOTONICITY_FREE];
    }

    /**
     * @dataProvider getForecastPrecisionTestData
     * @param string|false $columnUnit
     */
    public function testForecastPrecisionUsesMetricSemanticsAndNameFallbacks(
        string $columnName,
        $columnUnit,
        string $monotonicity,
        int $expected
    ): void {
        $classifier = new ForecastMetricClassifier([]);

        self::assertSame($expected, $classifier->getForecastPrecisionForColumn($columnName, $columnUnit, $monotonicity));
    }

    /**
     * @return iterable<string, array{string, string|false, string, int}>
     */
    public function getForecastPrecisionTestData(): iterable
    {
        yield 'plain nb metric' => ['nb_visits', false, ForecastMetricClassifier::MONOTONICITY_UP, 0];
        yield 'embedded nb metric' => ['exit_nb_visits', false, ForecastMetricClassifier::MONOTONICITY_UP, 0];
        yield 'count suffix metric' => ['bounce_count', false, ForecastMetricClassifier::MONOTONICITY_UP, 0];
        yield 'actions per visit is ratio' => ['nb_actions_per_visit', false, ForecastMetricClassifier::MONOTONICITY_FREE, 2];
        yield 'percent unit' => ['custom_metric', '%', ForecastMetricClassifier::MONOTONICITY_FREE, 2];
        yield 'duration name fallback' => ['sum_visit_length_returning', false, ForecastMetricClassifier::MONOTONICITY_UP, 2];
        yield 'unknown metric fallback' => ['custom_numeric_metric', false, ForecastMetricClassifier::MONOTONICITY_UP, 2];
    }

    public function testForecastPrecisionForMonotonicDownIntegerMetricRoundsToZeroDecimals(): void
    {
        $classifier = new ForecastMetricClassifier(['min_event_value' => Dimension::TYPE_NUMBER]);

        self::assertSame(
            0,
            $classifier->getForecastPrecisionForColumn('min_event_value', false, ForecastMetricClassifier::MONOTONICITY_DOWN)
        );
    }

    public function testForecastPrecisionForMonotonicMaxIntegerMetricRoundsToZeroDecimals(): void
    {
        $classifier = new ForecastMetricClassifier(['max_actions' => Dimension::TYPE_NUMBER]);

        self::assertSame(
            0,
            $classifier->getForecastPrecisionForColumn('max_actions', false, ForecastMetricClassifier::MONOTONICITY_MAX)
        );
    }

    public function testForecastPrecisionForDeduplicatedCountRoundsToZeroDecimals(): void
    {
        $classifier = new ForecastMetricClassifier(['nb_uniq_visitors' => Dimension::TYPE_NUMBER]);

        self::assertSame(
            0,
            $classifier->getForecastPrecisionForColumn('nb_uniq_visitors', false, ForecastMetricClassifier::MONOTONICITY_UNIQUE)
        );
    }

    /**
     * @dataProvider getBlobRowCountPrecisionTestData
     */
    public function testForecastPrecisionForBlobRowCountRoundsToZeroDecimals(string $columnName): void
    {
        $classifier = new ForecastMetricClassifier([]);

        self::assertSame(
            0,
            $classifier->getForecastPrecisionForColumn($columnName, false, ForecastMetricClassifier::MONOTONICITY_UNIQUE)
        );
    }

    /**
     * Blob-row counts count whole things, so none of them may render a fractional forecast. The
     * classifier is built with an empty semantic-type map here on purpose: that pins the
     * name-based fallback, which is what carries these when the registry does not declare them.
     * nb_keywords is the one case the BLOB_ROW_COUNT_METRIC_NAMES list does not have to carry --
     * the nb_ prefix already reaches 0 for it -- so it pins the outcome, not the membership.
     *
     * @return iterable<string, array{string}>
     */
    public function getBlobRowCountPrecisionTestData(): iterable
    {
        yield 'site search keywords' => ['nb_keywords'];
        yield 'distinct countries' => ['UserCountry_distinctCountries'];
        yield 'distinct ai chatbots' => ['BotTracking_AIChatbotsUniqueChatbots'];
        yield 'distinct search engines' => ['Referrers_distinctSearchEngines'];
        yield 'distinct website urls' => ['Referrers_distinctWebsitesUrls'];
    }
}
