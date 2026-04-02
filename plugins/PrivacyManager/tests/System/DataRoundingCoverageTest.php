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
use Piwik\DataTable;
use Piwik\DataTable\Row;
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
    private const CHANGE_COLUMN_PATTERN = '/_change$/i';

    private const EXCLUDED_BY_NAME_PATTERN = '/(rate|percent|percentage|evolution|duration|visit_length|bandwidth|byte)/';

    private const EXCLUDED_SPECIFIC_MAX_METRIC_PATTERN =
        '/^(max_actions(?:_(?:returning|new|ai_agent|human))?|max_time_(?:network|server|transfer|dom_processing|dom_completion|on_load|generation)|max_bandwidth)$/';

    private const INCLUDED_COUNT_BY_NAME_PATTERN = '/(^nb_|_nb_|_count$|^count_|^items$|^orders$|^quantity$|^hits$)/';

    private const IDENTIFIER_BY_NAME_PATTERN = '/(^id_|_id$)/';

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
        CnilPolicy::setActiveStatus(null, true);
    }

    public function tearDown(): void
    {
        CnilPolicy::setActiveStatus(null, false);

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

    public function testMultiSiteMixedPolicyPayloadRoundsOnlyEnabledSite(): void
    {
        try {
            CnilPolicy::setActiveStatus(null, false);
            CnilPolicy::setActiveStatus(1, true);
            CnilPolicy::setActiveStatus(2, false);

            [$api, $params] = $this->getMultiSiteMixedPolicyScenario();
            $this->runApiTests($api, $params);

            $request = [
                'module' => 'API',
                'method' => 'Actions.getPageUrls',
                'format' => 'xml',
                'idSite' => '1,2',
                'period' => 'year',
                'date' => '2012-08-09',
                'language' => 'en',
                'segment' => self::DEFAULT_SEGMENT,
                'filter_limit' => '-1',
                'keep_totals_row' => '1',
                'keep_totals_row_label' => 'Totals',
            ];

            $response = $this->loadApiResponse($request);
            $site1Payload = $this->getMultiSiteResultXml($response, 1);
            $site2Payload = $this->getMultiSiteResultXml($response, 2);

            $this->assertNotSame('', $site1Payload, 'Expected a multi-site XML payload for site 1.');
            $this->assertNotSame('', $site2Payload, 'Expected a multi-site XML payload for site 2.');

            $site1Violations = $this->findUnroundedCountFieldValues($site1Payload);
            $site2Violations = $this->findUnroundedCountFieldValues($site2Payload);

            $this->assertSame(
                [],
                $site1Violations,
                sprintf('Expected rounded count metrics for site 1, found: %s', implode(', ', $site1Violations))
            );
            $this->assertNotSame(
                [],
                $site2Violations,
                'Expected at least one non-rounded count metric for site 2 when CNIL rounding is disabled.'
            );
        } finally {
            CnilPolicy::setActiveStatus(1, false);
            CnilPolicy::setActiveStatus(2, false);
            CnilPolicy::setActiveStatus(null, true);
        }
    }

    public function testMultiSitesSegmentedApiUsesActualSiteListForMixedPolicy(): void
    {
        try {
            CnilPolicy::setActiveStatus(null, false);
            CnilPolicy::setActiveStatus(1, true);
            CnilPolicy::setActiveStatus(2, false);

            [$api, $params] = $this->getMultiSitesApiMixedPolicyScenario();
            $this->runApiTests($api, $params);

            $requestWithSiteOne = [
                'module' => 'API',
                'method' => 'MultiSites.getAll',
                'format' => 'xml',
                'idSite' => 1,
                'period' => 'month',
                'date' => '2012-08-09',
                'language' => 'en',
                'segment' => self::DEFAULT_SEGMENT,
                'filter_limit' => '-1',
            ];
            $requestWithSiteTwo = $requestWithSiteOne;
            $requestWithSiteTwo['idSite'] = 2;

            $siteOneRequestResponse = $this->loadApiResponse($requestWithSiteOne);
            $siteTwoRequestResponse = $this->loadApiResponse($requestWithSiteTwo);

            $this->assertSame(
                $siteOneRequestResponse,
                $siteTwoRequestResponse,
                'Expected MultiSites.getAll segmented output to depend on the actual site list, not the ambient idSite request value.'
            );

            $siteOneRowPayload = $this->getSingleTableRowXmlBySiteId($siteOneRequestResponse, 1);
            $siteTwoRowPayload = $this->getSingleTableRowXmlBySiteId($siteOneRequestResponse, 2);

            $this->assertNotSame('', $siteOneRowPayload, 'Expected a MultiSites XML row for site 1.');
            $this->assertNotSame('', $siteTwoRowPayload, 'Expected a MultiSites XML row for site 2.');

            $siteOneViolations = $this->findUnroundedCountFieldValues($siteOneRowPayload);
            $this->assertSame(
                [],
                $siteOneViolations,
                sprintf('Expected rounded count metrics for site 1, found: %s', implode(', ', $siteOneViolations))
            );

            $siteTwoViolations = $this->findUnroundedCountFieldValues($siteTwoRowPayload);
            $this->assertNotSame(
                [],
                $siteTwoViolations,
                'Expected at least one non-rounded count metric for site 2 when CNIL rounding is disabled.'
            );
        } finally {
            CnilPolicy::setActiveStatus(1, false);
            CnilPolicy::setActiveStatus(2, false);
            CnilPolicy::setActiveStatus(null, true);
        }
    }

    public function testMultiSitesGetAllWithGroupsIgnoresAmbientRequestIdSiteForRounding(): void
    {
        try {
            CnilPolicy::setActiveStatus(null, false);
            CnilPolicy::setActiveStatus(1, true);
            CnilPolicy::setActiveStatus(2, false);

            $requestWithSiteOne = [
                'module' => 'API',
                'method' => 'MultiSites.getAllWithGroups',
                'format' => 'json',
                'idSite' => 1,
                'period' => 'month',
                'date' => '2012-08-09',
                'language' => 'en',
                'segment' => self::DEFAULT_SEGMENT,
                'filter_limit' => '50',
                'filter_offset' => '0',
                'filter_sort_column' => 'nb_visits',
                'filter_sort_order' => 'desc',
                'format_metrics' => '0',
            ];
            $requestWithSiteTwo = $requestWithSiteOne;
            $requestWithSiteTwo['idSite'] = 2;

            $responseWithSiteOne = $this->loadApiResponse($requestWithSiteOne);
            $responseWithSiteTwo = $this->loadApiResponse($requestWithSiteTwo);

            $this->assertSame(
                $responseWithSiteOne,
                $responseWithSiteTwo,
                'Expected MultiSites.getAllWithGroups segmented output to ignore the ambient idSite request value.'
            );

            $payload = json_decode($responseWithSiteOne, true);
            $this->assertIsArray($payload);
            $this->assertArrayHasKey('sites', $payload);
            $this->assertArrayHasKey('totals', $payload);

            $siteOneRow = $this->findSiteRowInArrayPayload($payload['sites'], 1);
            $siteTwoRow = $this->findSiteRowInArrayPayload($payload['sites'], 2);

            $this->assertNotEmpty($siteOneRow, 'Expected MultiSites.getAllWithGroups payload to include site 1.');
            $this->assertNotEmpty($siteTwoRow, 'Expected MultiSites.getAllWithGroups payload to include site 2.');

            $siteOneViolations = $this->findUnroundedCountValuesInArray($siteOneRow);
            $this->assertSame(
                [],
                $siteOneViolations,
                sprintf('Expected rounded site 1 values in getAllWithGroups, found: %s', implode(', ', $siteOneViolations))
            );

            $siteOneHits = $this->getArrayIntValue($siteOneRow, 'hits');
            $siteOnePreviousHits = $this->getArrayIntValue($siteOneRow, 'previous_hits');
            $this->assertSame($this->roundToNearestTen($siteOneHits), $siteOneHits);
            $this->assertSame($this->roundToNearestTen($siteOnePreviousHits), $siteOnePreviousHits);

            $this->getArrayIntValue($siteTwoRow, 'hits');
            $this->getArrayIntValue($siteTwoRow, 'previous_hits');
        } finally {
            CnilPolicy::setActiveStatus(1, false);
            CnilPolicy::setActiveStatus(2, false);
            CnilPolicy::setActiveStatus(null, true);
        }
    }

    public function testActionsPageReportsAreRoundedWithoutSnapshotComparison(): void
    {
        foreach ($this->getActionsPageReportRequests() as $requestId => $requestUrl) {
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

            $requestViolations = $this->findUnroundedCountFieldValues($response);
            $this->assertSame(
                [],
                $requestViolations,
                sprintf(
                    'Found non-rounded count values in "%s": %s',
                    $requestId,
                    implode(', ', $requestViolations)
                )
            );
        }
    }

    public static function getOutputPrefix(): string
    {
        return 'DataRoundingCoverage';
    }

    public static function getPathToTestDirectory(): string
    {
        return __DIR__;
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

    private function getMultiSiteResultXml(string $xml, int $siteId): string
    {
        $document = new \DOMDocument();
        $document->loadXML($xml);

        $xpath = new \DOMXPath($document);
        $nodes = $xpath->query(sprintf('/results/result[@idSite="%d"]', $siteId));
        if ($nodes === false || $nodes->length === 0) {
            return '';
        }

        return $document->saveXML($nodes->item(0)) ?: '';
    }

    private function getSingleTableRowXmlBySiteId(string $xml, int $siteId): string
    {
        $document = new \DOMDocument();
        $document->loadXML($xml);

        $xpath = new \DOMXPath($document);
        $nodes = $xpath->query(sprintf('/result/row[idsite="%d"]', $siteId));
        if ($nodes === false || $nodes->length === 0) {
            return '';
        }

        return $document->saveXML($nodes->item(0)) ?: '';
    }

    /**
     * @param mixed[] $rows
     * @return array<string, mixed>
     */
    private function findSiteRowInArrayPayload(array $rows, int $siteId): array
    {
        foreach ($rows as $row) {
            if (!is_array($row)) {
                continue;
            }

            if (isset($row['idsite']) && (int) $row['idsite'] === $siteId) {
                return $row;
            }
        }

        return [];
    }

    /**
     * @param array<string, mixed> $row
     */
    private function getArrayIntValue(array $row, string $fieldName): int
    {
        $this->assertArrayHasKey($fieldName, $row, sprintf('Expected field "%s" in array payload row.', $fieldName));
        $this->assertIsNumeric($row[$fieldName], sprintf('Expected field "%s" to be numeric.', $fieldName));

        return (int) $row[$fieldName];
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

    /**
     * @param array<string, mixed> $values
     * @return string[]
     */
    private function findUnroundedCountValuesInArray(array $values): array
    {
        $violations = [];

        foreach ($values as $key => $value) {
            $tag = (string) $key;
            if (!$this->shouldAuditTagAsCountMetric($tag) || !is_numeric($value)) {
                continue;
            }

            $intValue = (int) $value;
            if ($intValue === 0) {
                continue;
            }

            $expectedRounded = $this->roundToNearestTen($intValue);
            if ($intValue !== $expectedRounded) {
                $violations[] = sprintf('%s=%s', $tag, (string) $value);
            }
        }

        return $violations;
    }

    private function shouldAuditTagAsCountMetric(string $tag): bool
    {
        $tag = strtolower($tag);
        if (
            $tag === ''
            || $tag === 'label'
            || $tag === 'idsite'
            || $tag === 'idgoal'
            || $tag === 'idsubdatatable'
            || preg_match(self::IDENTIFIER_BY_NAME_PATTERN, $tag)
            || preg_match(self::CHANGE_COLUMN_PATTERN, $tag)
        ) {
            return false;
        }

        if (preg_match(self::EXCLUDED_BY_NAME_PATTERN, $tag)) {
            return false;
        }

        if (preg_match(self::EXCLUDED_SPECIFIC_MAX_METRIC_PATTERN, $tag)) {
            return false;
        }

        return (bool) preg_match(self::INCLUDED_COUNT_BY_NAME_PATTERN, $tag);
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
                'xmlFieldsToRemove' => [
                    'label',
                ],
                'otherRequestParameters' => [
                    'filter_limit' => '-1',
                    'keep_totals_row' => '1',
                    'keep_totals_row_label' => 'Totals',
                ],
                'apiNotToCall' => [
                    // These Actions page reports include PagePerformance averages that can drift
                    // slightly across PHP/DB runtimes. Cover them separately with direct assertions
                    // so this test still validates counts/totals/percentages without asserting
                    // those environment-sensitive fields.
                    'Actions.getEntryPageTitles',
                    'Actions.getEntryPageUrls',
                    'Actions.getExitPageTitles',
                    'Actions.getExitPageUrls',
                    'Actions.getPageTitle',
                    'Actions.getPageUrls',
                    'Actions.getPageUrlsFollowingSiteSearch',
                    'Actions.getPageTitles',
                    'Actions.getPageTitlesFollowingSiteSearch',
                    'Actions.getPageUrl',
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
                'xmlFieldsToRemove' => [
                    'label',
                ],
                'otherRequestParameters' => [
                    'filter_limit' => '-1',
                    'keep_totals_row' => '1',
                    'keep_totals_row_label' => 'Totals',
                ],
                'testSuffix' => '_cnil_enabled_segmented_day_metrics',
            ],
        ];
    }

    /**
     * API-only multi-site requests are not available in the UI, but they are a valid
     * request shape and should round site payloads selectively based on each site's
     * CNIL policy state.
     *
     * @return array{0: string[], 1: array<string, mixed>}
     */
    private function getMultiSiteMixedPolicyScenario(): array
    {
        return [
            [
                'Actions.getPageUrls',
            ],
            [
                'idSite' => '1,2',
                'date' => '2012-08-09',
                'periods' => ['year'],
                'format' => 'xml',
                'language' => 'en',
                'segment' => self::DEFAULT_SEGMENT,
                'xmlFieldsToRemove' => [
                    'label',
                ],
                'otherRequestParameters' => [
                    'filter_limit' => '-1',
                    'keep_totals_row' => '1',
                    'keep_totals_row_label' => 'Totals',
                ],
                'testSuffix' => '_cnil_enabled_segmented_multi_site_mixed_policy',
            ],
        ];
    }

    /**
     * MultiSites is API-only for this coverage. The request may still carry an ambient idSite,
     * but the payload must be rounded from the actual site list returned by MultiSites.getAll.
     *
     * @return array{0: string[], 1: array<string, mixed>}
     */
    private function getMultiSitesApiMixedPolicyScenario(): array
    {
        return [
            [
                'MultiSites.getAll',
            ],
            [
                'idSite' => 1,
                'date' => '2012-08-09',
                'periods' => ['month'],
                'format' => 'xml',
                'language' => 'en',
                'segment' => self::DEFAULT_SEGMENT,
                'xmlFieldsToRemove' => [
                    'label',
                ],
                'otherRequestParameters' => [
                    'filter_limit' => '-1',
                ],
                'testSuffix' => '_cnil_enabled_segmented_multi_sites_api_mixed_policy',
            ],
        ];
    }

    /**
     * These Actions page reports include unstable translated labels and PagePerformance
     * averages that can drift slightly across PHP/DB runtimes. Cover them with direct
     * assertions so this test still validates counts/totals/percentages without relying
     * on exact XML snapshots for those unrelated fields.
     *
     * @return array<string, array<string, mixed>>
     */
    private function getActionsPageReportRequests(): array
    {
        $api = [
            'Actions.getEntryPageTitles',
            'Actions.getEntryPageUrls',
            'Actions.getExitPageTitles',
            'Actions.getExitPageUrls',
            'Actions.getPageTitle',
            'Actions.getPageUrls',
            'Actions.getPageUrlsFollowingSiteSearch',
            'Actions.getPageTitles',
            'Actions.getPageTitlesFollowingSiteSearch',
            'Actions.getPageUrl',
        ];

        $params = [
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
        ];

        $testConfig = new ApiTestConfig($params);
        return $this->getTestRequestsCollection($api, $testConfig, $api)->getRequestUrls();
    }
}

DataRoundingCoverageTest::$fixture = new UITestFixture();
