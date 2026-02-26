<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\PrivacyManager\tests\System;

use Piwik\API\DocumentationGenerator;
use Piwik\API\Proxy;
use Piwik\API\Request;
use Piwik\Config;
use Piwik\Plugins\PrivacyManager\FeatureFlags\PrivacyCompliance;
use Piwik\Plugins\PrivacyManager\tests\Fixtures\MultipleSitesMultipleVisitsFixture;
use Piwik\Policy\CnilPolicy;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group PrivacyManager
 * @group Plugins
 */
class DataRoundingCoverageTest extends SystemTestCase
{
    private const EXPECTED_PATH = '/plugins/PrivacyManager/tests/System/expected/';

    private const ENDPOINT_INVENTORY_BASELINE_FILE = 'segment_endpoint_inventory_baseline.json';

    private const COVERED_ENDPOINT_FILE = 'segment_endpoint_covered.json';

    private const DEFAULT_SEGMENT = 'countryCode==GB';

    /**
     * @var array<string, array<string, mixed>>
     */
    private const COVERED_ENDPOINT_CASES = [
        'Actions.getPageUrls' => [
            'countKeys' => ['nb_hits', 'nb_visits'],
        ],
        'MultiSites.getAllWithGroups' => [
            'params' => [
                'idSite' => 'all',
            ],
            'countKeys' => ['nb_visits', 'nb_actions'],
        ],
        'Referrers.getAll' => [
            'countKeys' => ['nb_visits', 'nb_actions'],
        ],
        'VisitsSummary.get' => [
            'countKeys' => ['nb_visits', 'nb_actions'],
            'nonCountKeys' => ['bounce_rate'],
        ],
    ];

    /**
     * @var string[]
     */
    private const DISCOVERY_SKIP_ENDPOINTS = [
        'API.getMatomoVersion',
        'API.getPiwikVersion',
        'API.getPhpVersion',
        'SegmentEditor.getSegmentData',
        'UserCountry.getLocationFromIP',
        'UserCountry.getCountryCodeMapping',
    ];

    /**
     * @var array<string, array<string, mixed>>
     */
    private const INVENTORY_ENDPOINT_PARAM_OVERRIDES = [
        'MultiSites.getAllWithGroups' => [
            'idSite' => 'all',
        ],
    ];

    /**
     * @var MultipleSitesMultipleVisitsFixture
     */
    public static $fixture = null; // initialized below class definition

    public function setUp(): void
    {
        parent::setUp();
        $this->setComplianceFeatureFlag(true);
        CnilPolicy::setActiveStatus(null, false);
    }

    public function tearDown(): void
    {
        CnilPolicy::setActiveStatus(null, false);
        $this->setComplianceFeatureFlag(false);
        parent::tearDown();
    }

    public function testSegmentCapableEndpointsAreExplicitlyClassified(): void
    {
        $discovered = $this->discoverSegmentCapableEndpoints();
        $baseline = $this->loadEndpointList(self::ENDPOINT_INVENTORY_BASELINE_FILE);
        $covered = $this->loadEndpointList(self::COVERED_ENDPOINT_FILE);

        $newEndpoints = array_values(array_diff($discovered, $baseline));
        $stale = array_values(array_diff($baseline, $discovered));
        $notInInventory = array_values(array_diff($covered, $baseline));

        $this->assertEmptyDiff(
            $newEndpoints,
            'New segment-capable endpoints detected (inspect it and add it to the segment_endpoint_inventory_baseline.json file)'
        );
        $this->assertEmptyDiff(
            $stale,
            'Segment endpoint inventory contains stale endpoints'
        );
        $this->assertEmptyDiff(
            $notInInventory,
            'Covered endpoints must exist in segment inventory'
        );
    }

    public function testCoveredEndpointCasesMatchCoveredEndpointList(): void
    {
        $covered = $this->loadEndpointList(self::COVERED_ENDPOINT_FILE);
        $configured = array_keys(self::COVERED_ENDPOINT_CASES);
        sort($configured);

        $this->assertSame($covered, $configured);
    }

    public function testAllInventoryEndpointsReturnJsonWithAndWithoutCnilEnabled(): void
    {
        $inventoryEndpoints = $this->loadEndpointList(self::ENDPOINT_INVENTORY_BASELINE_FILE);

        foreach ($inventoryEndpoints as $endpoint) {
            $params = $this->getInventoryEndpointParams($endpoint);
            [$rawResponse, $roundedResponse] = $this->fetchRawAndRoundedResponses($endpoint, $params);

            $this->assertIsArray($rawResponse, sprintf('Endpoint "%s" raw response should be an array.', $endpoint));
            $this->assertIsArray($roundedResponse, sprintf('Endpoint "%s" rounded response should be an array.', $endpoint));
        }
    }

    /**
     * @dataProvider coveredEndpointProvider
     *
     * @param string[] $countKeys
     * @param string[] $nonCountKeys
     * @param array<string, mixed> $params
     */
    public function testCoveredEndpointsRoundCountMetricsWhenCnilEnabled(
        string $endpoint,
        array $countKeys,
        array $nonCountKeys,
        array $params
    ): void {
        $this->assertNotEmpty(
            $countKeys,
            sprintf('Endpoint "%s" must define at least one count key for assertions.', $endpoint)
        );

        [$rawResponse, $roundedResponse] = $this->fetchRawAndRoundedResponses($endpoint, $params);
        $this->assertCountKeysRoundedForEndpoint($endpoint, $countKeys, $rawResponse, $roundedResponse);
        $this->assertNonCountKeysUnchangedForEndpoint($endpoint, $nonCountKeys, $rawResponse, $roundedResponse);
    }

    public function testRoundingStillAppliesWhenRootPostProcessorDisabled(): void
    {
        $params = ['segment' => 'countryCode==GB'];

        CnilPolicy::setActiveStatus(null, false);
        $raw = $this->callApiJson('VisitsSummary.get', $params);

        CnilPolicy::setActiveStatus(null, true);
        $roundedDefault = $this->callApiJson('VisitsSummary.get', $params);
        $roundedFallback = $this->callApiJson('VisitsSummary.get', array_merge($params, [
            'disable_root_datatable_post_processor' => 1,
        ]));

        $rawValues = $this->collectNumericValuesForKey($raw, 'nb_visits');
        $roundedDefaultValues = $this->collectNumericValuesForKey($roundedDefault, 'nb_visits');
        $roundedFallbackValues = $this->collectNumericValuesForKey($roundedFallback, 'nb_visits');

        $this->assertNotEmpty($rawValues);
        $this->assertCount(count($rawValues), $roundedDefaultValues);
        $this->assertCount(count($roundedDefaultValues), $roundedFallbackValues);

        $this->assertRoundedValuesMatchExpected($rawValues, $roundedDefaultValues, 'VisitsSummary.get', 'nb_visits');
        $this->assertSame($roundedDefaultValues, $roundedFallbackValues);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function coveredEndpointProvider(): array
    {
        $cases = [];
        foreach (self::COVERED_ENDPOINT_CASES as $endpoint => $config) {
            $cases[$endpoint] = [
                $endpoint,
                $config['countKeys'] ?? [],
                $config['nonCountKeys'] ?? [],
                $config['params'] ?? [],
            ];
        }

        return $cases;
    }

    private function discoverSegmentCapableEndpoints(): array
    {
        new DocumentationGenerator();

        $endpoints = [];
        foreach (Proxy::getInstance()->getMetadata() as $class => $methods) {
            $module = Proxy::getInstance()->getModuleNameFromClassName($class);
            foreach ($methods as $methodName => $methodMetadata) {
                if ($methodName === '__documentation' || !is_array($methodMetadata)) {
                    continue;
                }
                if (
                    strpos($methodName, 'get') !== 0
                    && $methodName !== 'generateReport'
                ) {
                    continue;
                }
                if (!isset($methodMetadata['parameters']['segment'])) {
                    continue;
                }
                if ($this->shouldSkipEndpoint($module, $methodName)) {
                    continue;
                }

                $endpoints[] = $module . '.' . $methodName;
            }
        }

        $endpoints = array_values(array_unique($endpoints));
        sort($endpoints);
        return $endpoints;
    }

    private function shouldSkipEndpoint(string $module, string $method): bool
    {
        $id = $module . '.' . $method;
        return in_array($id, self::DISCOVERY_SKIP_ENDPOINTS, true);
    }

    private function callApiJson(string $endpoint, array $otherParams = []): array
    {
        $params = array_merge([
            'method' => $endpoint,
            'idSite' => 1,
            'date' => self::$fixture->dateTime,
            'period' => 'day',
            'segment' => self::DEFAULT_SEGMENT,
            'filter_limit' => '-1',
            'format' => 'JSON',
            'serialize' => 1,
        ], $otherParams);

        $response = (new Request($params))->process();
        $decoded = json_decode($response, true);

        $this->assertIsArray($decoded, sprintf('Expected JSON array response for endpoint "%s".', $endpoint));
        return $decoded;
    }

    /**
     * @return array<string, mixed>
     */
    private function getInventoryEndpointParams(string $endpoint): array
    {
        return self::INVENTORY_ENDPOINT_PARAM_OVERRIDES[$endpoint] ?? [];
    }

    /**
     * @param array<string, mixed> $params
     * @return array{0: array, 1: array}
     */
    private function fetchRawAndRoundedResponses(string $endpoint, array $params): array
    {
        CnilPolicy::setActiveStatus(null, false);
        $rawResponse = $this->callApiJson($endpoint, $params);

        CnilPolicy::setActiveStatus(null, true);
        $roundedResponse = $this->callApiJson($endpoint, $params);

        return [$rawResponse, $roundedResponse];
    }

    /**
     * @param string[] $countKeys
     * @param array<string, mixed> $rawResponse
     * @param array<string, mixed> $roundedResponse
     */
    private function assertCountKeysRoundedForEndpoint(
        string $endpoint,
        array $countKeys,
        array $rawResponse,
        array $roundedResponse
    ): void {
        foreach ($countKeys as $key) {
            $rawValues = $this->collectNumericValuesForKey($rawResponse, $key);
            $roundedValues = $this->collectNumericValuesForKey($roundedResponse, $key);

            $this->assertNotEmpty(
                $rawValues,
                sprintf('No values found for count key "%s" in endpoint "%s".', $key, $endpoint)
            );
            $this->assertCount(
                count($rawValues),
                $roundedValues,
                sprintf('Rounded value count mismatch for key "%s" in endpoint "%s".', $key, $endpoint)
            );

            $this->assertRoundedValuesMatchExpected($rawValues, $roundedValues, $endpoint, $key);
        }
    }

    /**
     * @param string[] $nonCountKeys
     * @param array<string, mixed> $rawResponse
     * @param array<string, mixed> $roundedResponse
     */
    private function assertNonCountKeysUnchangedForEndpoint(
        string $endpoint,
        array $nonCountKeys,
        array $rawResponse,
        array $roundedResponse
    ): void {
        foreach ($nonCountKeys as $key) {
            $rawValues = $this->collectScalarValuesForKey($rawResponse, $key);
            $roundedValues = $this->collectScalarValuesForKey($roundedResponse, $key);

            if (empty($rawValues) && empty($roundedValues)) {
                continue;
            }

            $this->assertSame(
                $rawValues,
                $roundedValues,
                sprintf('Non-count key "%s" changed unexpectedly for endpoint "%s".', $key, $endpoint)
            );
        }
    }

    /**
     * @param int[] $rawValues
     * @param int[] $roundedValues
     */
    private function assertRoundedValuesMatchExpected(
        array $rawValues,
        array $roundedValues,
        string $endpoint,
        string $key
    ): void {
        foreach ($rawValues as $i => $rawValue) {
            $expected = $this->roundToNearestTen($rawValue);
            $this->assertSame(
                $expected,
                (int) $roundedValues[$i],
                sprintf('Unexpected rounded value for key "%s" in endpoint "%s" at index %d.', $key, $endpoint, $i)
            );
        }
    }

    /**
     * @param array<int, mixed> $diff
     */
    private function assertEmptyDiff(array $diff, string $messagePrefix): void
    {
        $this->assertSame(
            [],
            $diff,
            $messagePrefix . ":\n" . json_encode($diff, JSON_PRETTY_PRINT)
        );
    }

    /**
     * @return int[]
     */
    private function collectNumericValuesForKey($value, string $targetKey): array
    {
        $collected = [];
        $this->collectValuesForKeyRecursive($value, $targetKey, static function ($v): bool {
            return is_numeric($v);
        }, $collected);

        return array_map('intval', $collected);
    }

    /**
     * @return array<int, mixed>
     */
    private function collectScalarValuesForKey($value, string $targetKey): array
    {
        $collected = [];
        $this->collectValuesForKeyRecursive($value, $targetKey, static function ($v): bool {
            return is_scalar($v) || $v === null;
        }, $collected);

        return $collected;
    }

    /**
     * @param array<int, mixed> $collected
     */
    private function collectValuesForKeyRecursive($value, string $targetKey, callable $acceptValue, array &$collected): void
    {
        if (!is_array($value)) {
            return;
        }

        foreach ($value as $key => $child) {
            if ((string) $key === $targetKey && $acceptValue($child)) {
                $collected[] = $child;
            }
            $this->collectValuesForKeyRecursive($child, $targetKey, $acceptValue, $collected);
        }
    }

    private function roundToNearestTen(int $value): int
    {
        if ($value === 0) {
            return 0;
        }

        return max(10, (int) (floor(($value + 5) / 10) * 10));
    }

    /**
     * @return string[]
     */
    private function loadEndpointList(string $fileName): array
    {
        $path = $this->getExpectedPath($fileName);
        $decoded = json_decode(file_get_contents($path), true);

        if (!is_array($decoded)) {
            $this->fail(sprintf('Invalid endpoint list JSON in "%s".', $path));
        }

        $values = array_values($decoded);
        sort($values);
        return $values;
    }

    private function getExpectedPath(string $fileName): string
    {
        return PIWIK_DOCUMENT_ROOT . self::EXPECTED_PATH . $fileName;
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
}

DataRoundingCoverageTest::$fixture = new MultipleSitesMultipleVisitsFixture();
