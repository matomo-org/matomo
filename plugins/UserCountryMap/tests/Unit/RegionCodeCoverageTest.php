<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UserCountryMap\tests\Unit;

/**
 * Guards the core invariant of the region maps: every `data-region` code drawn in
 * plugins/UserCountryMap/svg/*.svg must be a code Matomo actually knows about in
 * plugins/GeoIp2/data/isoRegionNames.php (or the whole-country __ALL__ shape).
 *
 * A shape whose code is not in isoRegionNames can never receive data -- it renders
 * permanently grey and logs region-mismatch warnings -- so this test fails fast if
 * a map regeneration reintroduces stale/abolished/foreign subdivision codes.
 *
 * This is deliberately a one-way membership check (map code => known code). The
 * reverse is NOT an invariant: region-map coverage is driven by what the geolocation
 * providers emit, so it is expected and fine that not every isoRegionNames code has
 * a shape, and that some countries are drawn as a single __ALL__ shape.
 *
 * @group Plugins
 * @group UserCountryMap
 */
class RegionCodeCoverageTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Map codes that are intentionally not in isoRegionNames.php. Keyed by SVG name
     * (ISO 3166-1 alpha-3) => list of allowed codes. Keep this empty; add an entry
     * only with a comment explaining why a non-ISO code is deliberately drawn.
     */
    private const ALLOWED_NON_ISO_CODES = [];

    public function testEveryMapRegionCodeIsKnownToMatomo()
    {
        $iso3ToIso2 = $this->buildIso3ToIso2Map();
        $regionData = include PIWIK_INCLUDE_PATH . '/plugins/GeoIp2/data/isoRegionNames.php';

        $violations = [];
        $unmappable = [];

        foreach (glob(PIWIK_INCLUDE_PATH . '/plugins/UserCountryMap/svg/*.svg') as $svgFile) {
            $name = basename($svgFile, '.svg');
            $svg = file_get_contents($svgFile);

            // world + continent maps carry data-iso, not data-region -- skip them.
            if (!preg_match_all('/data-region="([^"]*)"/', $svg, $m)) {
                continue;
            }

            $codes = array_values(array_filter(
                array_unique($m[1]),
                static fn($code) => $code !== '__ALL__'
            ));
            if (!$codes) {
                continue; // whole-country __ALL__ map, nothing to validate
            }

            preg_match('/data-iso3="([^"]*)"/', $svg, $i);
            $iso3 = $i[1] ?? $name;
            $iso2 = $iso3ToIso2[$iso3] ?? null;
            if ($iso2 === null) {
                $unmappable[$name] = $iso3;
                continue;
            }

            $known = $regionData[$iso2] ?? [];
            $allowed = self::ALLOWED_NON_ISO_CODES[$name] ?? [];
            foreach ($codes as $code) {
                if (!array_key_exists($code, $known) && !in_array($code, $allowed, true)) {
                    $violations[$name][] = $code;
                }
            }
        }

        $this->assertSame([], $unmappable, "SVG maps whose ISO 3166-1 alpha-3 code has no "
            . "ISO2toISO3 entry in visitor-map.js (cannot be validated):\n"
            . $this->format($unmappable));

        $this->assertSame([], $violations, "SVG maps drawing region codes that are not in "
            . "plugins/GeoIp2/data/isoRegionNames.php (these shapes can never receive data). "
            . "Fix the generator/reconcile config, or draw the country as __ALL__:\n"
            . $this->formatViolations($violations));
    }

    /**
     * Reuse the plugin's own authoritative ISO2toISO3 mapping (a JSON object literal
     * in visitor-map.js) as the single source of truth, inverted to iso3 => iso2.
     */
    private function buildIso3ToIso2Map(): array
    {
        $js = file_get_contents(PIWIK_INCLUDE_PATH . '/plugins/UserCountryMap/javascripts/visitor-map.js');
        $this->assertSame(1, preg_match('/ISO2toISO3:\s*(\{.*?\})/s', $js, $m),
            'Could not locate the ISO2toISO3 mapping in visitor-map.js');
        $iso2ToIso3 = json_decode($m[1], true);
        $this->assertIsArray($iso2ToIso3, 'ISO2toISO3 in visitor-map.js is not valid JSON');
        return array_flip($iso2ToIso3);
    }

    private function format(array $map): string
    {
        return implode("\n", array_map(static fn($k, $v) => "  $k => $v", array_keys($map), $map));
    }

    private function formatViolations(array $violations): string
    {
        $lines = [];
        foreach ($violations as $name => $codes) {
            $lines[] = "  $name: " . implode(', ', $codes);
        }
        return implode("\n", $lines);
    }
}
