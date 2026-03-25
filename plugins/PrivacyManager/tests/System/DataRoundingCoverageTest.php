<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\PrivacyManager\tests\System;

use Piwik\API\Request;
use Piwik\Config;
use Piwik\DataTable;
use Piwik\DataTable\Row;
use Piwik\Plugins\PrivacyManager\FeatureFlags\PrivacyCompliance;
use Piwik\Policy\CnilPolicy;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Fixtures\UITestFixture;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Framework\TestRequest\ApiTestConfig;
use Piwik\Tests\Framework\TestRequest\Response;

/**
 * CNIL rounding integration coverage using the standard Matomo system test pattern.
 *
 * We intentionally use runApiTests('all', ...) so all discoverable `get*`/`generateReport`
 * API methods are snapshot-tested, and missing expected XML files fail when new APIs
 * are added to the set.
 *
 * @group PrivacyManager
 * @group Plugins
 */
class DataRoundingCoverageTest extends SystemTestCase
{
    /**
     * Intentionally high hitting segment so we mostly get data from the APIs so we can test the rounding.
     */
    private const DEFAULT_SEGMENT = 'visitCount>=1';

    /**
     * Request IDs that must be present in the `all` snapshot run so we always
     * cover totals metadata and ratio/percentage outputs.
     */
    private const REQUIRED_REQUEST_IDS = [
        'MultiSites.getAllWithGroups_year.xml',
        'VisitorInterest.getNumberOfVisitsByVisitCount_year.xml',
        'VisitsSummary.get_year.xml',
        'Goals.getMetrics_year.xml',
    ];

    /**
     * Curated report endpoints used for payload-level totals checks.
     *
     * API.getProcessedReport serializes totals as <reportTotal>, unlike regular DataTable XML
     * output where keep_totals_row affects internal DataTable state and is not rendered as an
     * extra XML row.
     */
    private const PROCESSED_REPORT_TOTALS_ENDPOINTS = [
        ['apiModule' => 'DevicesDetection', 'apiAction' => 'getBrowserEngines'],
        ['apiModule' => 'Actions', 'apiAction' => 'getPageUrls'],
        ['apiModule' => 'VisitorInterest', 'apiAction' => 'getNumberOfVisitsByVisitCount'],
    ];

    /**
     * Curated direct API endpoints used to verify internal DataTable totals rows exist when
     * keep_totals_row=1 and there is data.
     */
    private const INTERNAL_TOTALS_ROW_ENDPOINTS = [
        ['method' => 'DevicesDetection.getBrowserEngines'],
        ['method' => 'Actions.getPageUrls'],
        ['method' => 'VisitorInterest.getNumberOfVisitsByVisitCount'],
        ['method' => 'Referrers.getKeywords'],
        ['method' => 'UserCountry.getContinent'],
    ];

    /**
     * @var UITestFixture
     */
    public static $fixture = null;

    public function setUp(): void
    {
        parent::setUp();
        $this->setComplianceFeatureFlag(true);
        CnilPolicy::setActiveStatus(null, true);
    }

    public function tearDown(): void
    {
        CnilPolicy::setActiveStatus(null, false);
        $this->setComplianceFeatureFlag(false);

        parent::tearDown();
    }

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params): void
    {
        $this->runApiTests($api, $params);
    }

    public function testAllScenarioIncludesTotalsAndRatioCarrierApis(): void
    {
        $scenario = $this->getPrimaryScenario();
        $api = $scenario[0];
        $params = $scenario[1];
        $testConfig = new ApiTestConfig($params);
        $requests = $this->getTestRequestsCollection($api, $testConfig, $api)->getRequestUrls();

        foreach (self::REQUIRED_REQUEST_IDS as $requiredRequestId) {
            $this->assertArrayHasKey(
                $requiredRequestId,
                $requests,
                sprintf(
                    'Required request "%s" is missing from runApiTests(\'all\'). Totals/ratio coverage may have regressed.',
                    $requiredRequestId
                )
            );
        }
    }

    public function testAllScenarioResponsesContainNoApiErrors(): void
    {
        foreach ($this->getPrimaryRequests() as $requestId => $requestUrl) {
            $response = $this->loadApiResponse($requestUrl);

            $this->assertStringNotContainsString(
                '<error>',
                strtolower($response),
                sprintf('API error payload detected in "%s".', $requestId)
            );
            $this->assertStringNotContainsString(
                'exception',
                strtolower($response),
                sprintf('API exception payload detected in "%s".', $requestId)
            );
        }
    }

    public function testAllScenarioCountMetricsAreRounded(): void
    {
        $violations = [];
        foreach ($this->getPrimaryRequests() as $requestId => $requestUrl) {
            $response = $this->loadApiResponse($requestUrl);
            $requestViolations = $this->findUnroundedCountFieldValues($response);
            foreach ($requestViolations as $violation) {
                $violations[] = $requestId . ': ' . $violation;
            }
        }

        $violationsPreview = array_slice($violations, 0, 20);
        $this->assertSame(
            [],
            $violations,
            "Found non-rounded count values:\n" . implode("\n", $violationsPreview)
        );
    }

    public function testProcessedReportPayloadContainsTotalsForCuratedEndpoints(): void
    {
        foreach (self::PROCESSED_REPORT_TOTALS_ENDPOINTS as $endpoint) {
            $response = $this->loadApiResponse([
                'module' => 'API',
                'method' => 'API.getProcessedReport',
                'format' => 'xml',
                'idSite' => 1,
                'period' => 'day',
                'date' => '2012-08-09',
                'filter_limit' => '-1',
                'keep_totals_row' => '1',
                'keep_totals_row_label' => 'Totals',
                'apiModule' => $endpoint['apiModule'],
                'apiAction' => $endpoint['apiAction'],
            ]);

            $requestId = $endpoint['apiModule'] . '.' . $endpoint['apiAction'];

            $this->assertStringContainsString(
                '<reportTotal>',
                $response,
                sprintf('Expected reportTotal in API.getProcessedReport response for "%s".', $requestId)
            );
            $this->assertRegExp(
                '/<reportTotal>[\s\S]*<nb_[a-z0-9_]+>/i',
                $response,
                sprintf('Expected count metrics inside reportTotal for "%s".', $requestId)
            );
        }
    }

    public function testInternalDataTableHasTotalsRowForCuratedEndpoints(): void
    {
        foreach (self::INTERNAL_TOTALS_ROW_ENDPOINTS as $endpoint) {
            $request = new Request([
                'module' => 'API',
                'method' => $endpoint['method'],
                'format' => 'original',
                'idSite' => 1,
                'period' => 'day',
                'date' => '2012-08-09',
                'segment' => self::DEFAULT_SEGMENT,
                'filter_limit' => '-1',
                'keep_totals_row' => '1',
                'keep_totals_row_label' => 'Totals',
                'token_auth' => Fixture::getTokenAuth(),
            ]);

            $result = $request->process();
            $this->assertTotalsRowPresenceAndRounding($result, $endpoint['method']);
        }
    }

    public function getApiForTesting(): array
    {
        return [$this->getPrimaryScenario()];
    }

    public function testVisitsSummaryDayOnlyMetricsAreRounded(): void
    {
        [$api, $params] = $this->getDayMetricScenario();
        $this->runApiTests($api, $params);
    }

    public static function getOutputPrefix(): string
    {
        return 'DataRoundingCoverage';
    }

    public static function getPathToTestDirectory(): string
    {
        return __DIR__;
    }

    private function setComplianceFeatureFlag(bool $enabled): void
    {
        $config = Config::getInstance();
        $featureFlag = new PrivacyCompliance();
        $featureFlagConfig = $featureFlag->getName() . '_feature';
        $config->FeatureFlags = [
            $featureFlagConfig => $enabled ? 'enabled' : 'disabled',
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function getPrimaryRequests(): array
    {
        $scenario = $this->getPrimaryScenario();
        $api = $scenario[0];
        $params = $scenario[1];
        $testConfig = new ApiTestConfig($params);
        return $this->getTestRequestsCollection($api, $testConfig, $api)->getRequestUrls();
    }

    /**
     * @param array<string, mixed> $requestUrl
     * @return array<string, mixed>
     */
    private function withTokenAuth(array $requestUrl): array
    {
        if (!isset($requestUrl['token_auth'])) {
            $requestUrl['token_auth'] = UITestFixture::getTokenAuth();
        }

        return $requestUrl;
    }

    /**
     * Executes generated API requests through the internal request path used by runApiTests().
     * This avoids rebuilding an HTTP URL from decoded request parameters, which can produce
     * malformed curl URLs for values like full page URLs or labels containing spaces.
     */
    private function loadApiResponse(array $requestUrl): string
    {
        return Response::loadFromApi([], $this->withTokenAuth($requestUrl), false)->getResponseText();
    }

    /**
     * @return string[]
     */
    private function findUnroundedCountFieldValues(string $xml): array
    {
        preg_match_all('/<([A-Za-z0-9_]+)>([^<]+)<\/\\1>/', $xml, $matches, PREG_SET_ORDER);
        $violations = [];

        foreach ($matches as $match) {
            $tag = $match[1];
            $value = trim($match[2]);

            if (!$this->shouldAuditTagAsCountMetric($tag)) {
                continue;
            }

            $normalized = str_replace([',', ' '], '', $value);
            if (!preg_match('/^-?\\d+$/', $normalized)) {
                continue;
            }

            $intValue = (int) $normalized;
            if ($intValue === 0) {
                continue;
            }

            $expectedRounded = $this->roundToNearestTen($intValue);
            if ($intValue !== $expectedRounded) {
                $violations[] = sprintf('%s=%s', $tag, $value);
            }
        }

        return $violations;
    }

    private function shouldAuditTagAsCountMetric(string $tag): bool
    {
        if (strpos($tag, 'nb_') !== 0) {
            return false;
        }

        if (strpos($tag, '_rate') !== false || strpos($tag, '_percentage') !== false || strpos($tag, '_per_') !== false) {
            return false;
        }

        return true;
    }

    private function roundToNearestTen(int $value): int
    {
        if ($value === 0) {
            return 0;
        }

        return max(10, (int) (floor(($value + 5) / 10) * 10));
    }

    /**
     * @param mixed $result
     */
    private function assertTotalsRowPresenceAndRounding($result, string $requestId): void
    {
        if ($result instanceof DataTable\Map) {
            foreach ($result->getDataTables() as $key => $table) {
                $this->assertTotalsRowPresenceAndRounding($table, $requestId . '[' . $key . ']');
            }
            return;
        }

        if (!$result instanceof DataTable) {
            $this->fail(sprintf('Expected DataTable/DataTable\\Map for "%s", got "%s".', $requestId, gettype($result)));
        }

        if ($result->getRowsCount() === 0) {
            return;
        }

        $totalsRow = $result->getTotalsRow();
        $this->assertNotEmpty($totalsRow, sprintf('Expected totals row for "%s".', $requestId));
        $this->assertSame('Totals', $totalsRow->getColumn('label'), sprintf('Unexpected totals row label for "%s".', $requestId));

        $violations = $this->findNonRoundedCountValuesInRow($totalsRow);
        $this->assertSame([], $violations, sprintf(
            'Found non-rounded totals row count values for "%s": %s',
            $requestId,
            implode(', ', $violations)
        ));
    }

    /**
     * @return string[]
     */
    private function findNonRoundedCountValuesInRow(Row $row): array
    {
        $violations = [];

        foreach ($row->getColumns() as $columnName => $value) {
            $columnName = (string) $columnName;

            if (!$this->shouldAuditTagAsCountMetric($columnName) || !is_numeric($value)) {
                continue;
            }

            $intValue = (int) $value;
            if ($intValue === 0) {
                continue;
            }

            if ($this->roundToNearestTen($intValue) !== $intValue) {
                $violations[] = sprintf('%s=%s', $columnName, (string) $value);
            }
        }

        return $violations;
    }

    /**
     * @return array{0: string, 1: array<string, mixed>}
     */
    private function getPrimaryScenario(): array
    {
        return [
            'all',
            [
                'idSite' => 1,
                'date' => '2012-08-09',
                'periods' => ['year'],
                'format' => 'xml',
                'language' => 'en',
                'segment' => self::DEFAULT_SEGMENT,
                'otherRequestParameters' => [
                    'filter_limit' => '-1',
                    'keep_totals_row' => '1',
                    'keep_totals_row_label' => 'Totals',
                ],
                'apiNotToCall' => [
                    'CustomVariables.getUsagesOfSlots',
                    // These metrics are not available for the year period in this fixture/setup.
                    // Cover them separately with period=day so we get real payloads and can still verify rounding.
                    'VisitsSummary.getUniqueVisitors',
                    'VisitsSummary.getUsers',
                ],
                'testSuffix' => '_cnil_enabled_segmented',
            ],
        ];
    }

    /**
     * @return array{0: string[], 1: array<string, mixed>}
     */
    private function getDayMetricScenario(): array
    {
        return [
            [
                'VisitsSummary.getUniqueVisitors',
                'VisitsSummary.getUsers',
            ],
            [
                'idSite' => 1,
                'date' => '2012-08-09',
                'periods' => ['day'],
                'format' => 'xml',
                'language' => 'en',
                'segment' => self::DEFAULT_SEGMENT,
                'otherRequestParameters' => [
                    'filter_limit' => '-1',
                    'keep_totals_row' => '1',
                    'keep_totals_row_label' => 'Totals',
                ],
                'testSuffix' => '_cnil_enabled_segmented_day_metrics',
            ],
        ];
    }
}

DataRoundingCoverageTest::$fixture = new UITestFixture();
