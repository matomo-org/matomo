<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UserCountryMap\tests\Unit;

/**
 * @group Plugins
 * @group UserCountryMap
 */
class CountryLabelPositionTest extends \PHPUnit\Framework\TestCase
{
    private const PATH_TOKEN = '/[MLZ]|[-\d.]+,[-\d.]+/';

    private int $gridSteps;

    private int $refinePasses;

    private int $refineFactor;

    private int $minLabelArea;

    public function testEveryNeighbourLabelIsPlacedInsideTheCountryItNames()
    {
        $this->readSearchSettings();

        $mapsChecked = 0;
        $labelsChecked = 0;
        $withoutPosition = [];
        $outsideNeighbour = [];
        $insideSelectedCountry = [];
        $offCanvas = [];

        foreach (glob(PIWIK_INCLUDE_PATH . '/plugins/UserCountryMap/svg/*.svg') as $svgFile) {
            $name = basename($svgFile, '.svg');
            $svg = file_get_contents($svgFile);
            $paths = $this->parseContextPaths($svg);
            if (!isset($paths[$name])) {
                continue;
            }

            $mapsChecked++;
            $selected = $paths[$name];
            $bounds = $this->parseCanvas($svg);
            $this->assertNotNull($bounds, "$name.svg has no viewBox");

            foreach ($paths as $iso => $rings) {
                if ($iso === $name || $this->pathArea($rings) <= $this->minLabelArea) {
                    continue;
                }

                $labelsChecked++;
                $position = $this->labelPosition($rings, $selected, $bounds);
                if ($position === null) {
                    $withoutPosition[] = "$name => $iso";
                    continue;
                }

                [$x, $y] = $position;
                $where = sprintf('%s => %s at %.1f,%.1f', $name, $iso, $x, $y);
                if (!$this->covers($rings, $x, $y)) {
                    $outsideNeighbour[] = $where;
                }
                if ($this->covers($selected, $x, $y)) {
                    $insideSelectedCountry[] = $where;
                }
                if ($x < $bounds[0] || $x > $bounds[2] || $y < $bounds[1] || $y > $bounds[3]) {
                    $offCanvas[] = $where;
                }
            }
        }

        $this->assertGreaterThan(200, $mapsChecked, 'Almost no region map could be validated, '
            . 'the SVGs no longer expose a data-iso path matching the file name');

        $this->assertGreaterThan(700, $labelsChecked, 'Almost no neighbour label could be '
            . 'validated, the SVG path data can no longer be parsed by this test');

        $this->assertSame([], $withoutPosition, "Neighbour countries for which "
            . "UserCountryMap.countryLabelPosition() cannot find any placeable point, so their "
            . "label falls back to the kartograph centroid:\n  " . implode("\n  ", $withoutPosition));

        $this->assertSame([], $outsideNeighbour, "Neighbour labels placed outside the country "
            . "they name:\n  " . implode("\n  ", $outsideNeighbour));

        $this->assertSame([], $insideSelectedCountry, "Neighbour labels placed inside the selected "
            . "country, where they read as a label for one of its regions:\n  "
            . implode("\n  ", $insideSelectedCountry));

        $this->assertSame([], $offCanvas, "Neighbour labels placed outside the visible map "
            . "canvas:\n  " . implode("\n  ", $offCanvas));
    }

    private function parseCanvas(string $svg): ?array
    {
        if (!preg_match('/viewBox="([-\d.]+) ([-\d.]+) ([\d.]+) ([\d.]+)"/', $svg, $m)) {
            return null;
        }

        return [(float) $m[1], (float) $m[2], (float) $m[1] + (float) $m[3], (float) $m[2] + (float) $m[4]];
    }

    private function readSearchSettings(): void
    {
        $js = file_get_contents(PIWIK_INCLUDE_PATH . '/plugins/UserCountryMap/javascripts/visitor-map.js');

        $this->gridSteps = $this->readJsSetting($js, 'countryLabelGridSteps');
        $this->refinePasses = $this->readJsSetting($js, 'countryLabelRefinePasses');
        $this->refineFactor = $this->readJsSetting($js, 'countryLabelRefineFactor');
        $this->minLabelArea = $this->readJsSetting($js, 'countryLabelMinArea');
    }

    private function readJsSetting(string $js, string $name): int
    {
        $this->assertSame(1, preg_match('/' . $name . ':\s*(\d+),/', $js, $m), "Could not locate "
            . "$name as a whole number in visitor-map.js");

        return (int) $m[1];
    }

    private function parseContextPaths(string $svg): array
    {
        if (!preg_match('#<g[^>]*id="context"[^>]*>(.*?)</g>#s', $svg, $group)) {
            return [];
        }

        $paths = [];
        preg_match_all('#<path\b([^>]*)>#s', $group[1], $matches);
        foreach ($matches[1] as $attributes) {
            if (
                !preg_match('/data-iso="([^"]*)"/', $attributes, $iso)
                || !preg_match('/\sd="([^"]*)"/', $attributes, $d)
            ) {
                continue;
            }
            $paths[$iso[1]] = $this->contours($d[1]);
        }

        return $paths;
    }

    private function contours(string $d): array
    {
        $rings = [];
        $ring = [];
        preg_match_all(self::PATH_TOKEN, $d, $tokens);

        $this->assertSame('', trim(preg_replace(self::PATH_TOKEN, '', $d)), 'A path uses syntax '
            . 'contours() cannot parse, so its geometry would be validated against garbage');

        foreach ($tokens[0] as $token) {
            if ($token === 'M' || $token === 'Z') {
                if (count($ring) > 2) {
                    $rings[] = $ring;
                    $ring = [];
                }
            } elseif ($token !== 'L') {
                [$x, $y] = explode(',', $token);
                $ring[] = [(float) $x, (float) $y];
            }
        }
        if (count($ring) >= 2) {
            $rings[] = $ring;
        }

        return $rings;
    }

    private function ringArea(array $ring): float
    {
        $area = 0.0;
        $count = count($ring);
        for ($i = 0; $i < $count; $i++) {
            $p = $ring[$i];
            $q = $ring[($i + 1) % $count];
            $area += $p[0] * $q[1] - $q[0] * $p[1];
        }

        return $area / 2;
    }

    private function pathArea(array $rings): float
    {
        $area = 0.0;
        foreach ($rings as $ring) {
            $area += abs($this->ringArea($ring));
        }

        return $area;
    }

    private function ringBox(array $ring): array
    {
        $box = [$ring[0][0], $ring[0][1], $ring[0][0], $ring[0][1]];
        foreach ($ring as $point) {
            $box[0] = min($box[0], $point[0]);
            $box[1] = min($box[1], $point[1]);
            $box[2] = max($box[2], $point[0]);
            $box[3] = max($box[3], $point[1]);
        }

        return $box;
    }

    private function covers(array $rings, float $x, float $y): bool
    {
        $crossings = 0;
        foreach ($rings as $ring) {
            $count = count($ring);
            for ($i = 0; $i < $count; $i++) {
                $p = $ring[$i];
                $q = $ring[($i + 1) % $count];
                if (
                    ($p[1] > $y) !== ($q[1] > $y)
                    && $x < $p[0] + ($y - $p[1]) / ($q[1] - $p[1]) * ($q[0] - $p[0])
                ) {
                    $crossings++;
                }
            }
        }

        return $crossings % 2 === 1;
    }

    private function boxDistance(array $box, float $x, float $y): float
    {
        $dx = max($box[0] - $x, 0, $x - $box[2]);
        $dy = max($box[1] - $y, 0, $y - $box[3]);

        return sqrt($dx * $dx + $dy * $dy);
    }

    private function ringClearance(array $ring, float $x, float $y, float $min): float
    {
        $count = count($ring);
        for ($i = 0; $i < $count; $i++) {
            $p = $ring[$i];
            $q = $ring[($i + 1) % $count];
            $dx = $q[0] - $p[0];
            $dy = $q[1] - $p[1];
            $length = $dx * $dx + $dy * $dy;
            $t = $length ? max(0, min(1, (($x - $p[0]) * $dx + ($y - $p[1]) * $dy) / $length)) : 0;
            $ex = $x - ($p[0] + $t * $dx);
            $ey = $y - ($p[1] + $t * $dy);
            $distance = sqrt($ex * $ex + $ey * $ey);
            if ($distance < $min) {
                $min = $distance;
            }
        }

        return $min;
    }

    private function clearance(array $measured, float $x, float $y, float $floor): float
    {
        $min = INF;
        foreach ($measured as $entry) {
            if ($this->boxDistance($entry[1], $x, $y) >= $min) {
                continue;
            }
            $min = $this->ringClearance($entry[0], $x, $y, $min);
            if ($min <= $floor) {
                return $min;
            }
        }

        return $min;
    }

    private function labelPosition(array $rings, array $blocked, array $bounds): ?array
    {
        $measured = [];
        $largest = 0.0;
        $minX = $minY = $maxX = $maxY = 0.0;
        foreach (array_values($rings) as $index => $ring) {
            $largest = max($largest, abs($this->ringArea($ring)));
            $box = $this->ringBox($ring);
            $measured[] = [$ring, $box];
            if ($index === 0) {
                [$minX, $minY, $maxX, $maxY] = $box;
            } else {
                $minX = min($minX, $box[0]);
                $minY = min($minY, $box[1]);
                $maxX = max($maxX, $box[2]);
                $maxY = max($maxY, $box[3]);
            }
        }
        if (!$largest) {
            return null;
        }
        foreach ($blocked as $ring) {
            $measured[] = [$ring, $this->ringBox($ring)];
        }

        $minX = max($minX, $bounds[0]);
        $minY = max($minY, $bounds[1]);
        $maxX = min($maxX, $bounds[2]);
        $maxY = min($maxY, $bounds[3]);
        if ($maxX < $minX || $maxY < $minY) {
            return null;
        }

        $best = null;
        $bestClearance = 0.0;
        $scan = function (
            $x0,
            $x1,
            $y0,
            $y1,
            $step
        ) use (
            $rings,
            $measured,
            $blocked,
            $bounds,
            &$best,
            &$bestClearance
        ) {
            for ($y = $y0; $y <= $y1; $y += $step) {
                for ($x = $x0; $x <= $x1; $x += $step) {
                    if (
                        $x < $bounds[0] || $x > $bounds[2] || $y < $bounds[1] || $y > $bounds[3]
                        || !$this->covers($rings, $x, $y) || $this->covers($blocked, $x, $y)
                    ) {
                        continue;
                    }
                    $clearance = $this->clearance($measured, $x, $y, $bestClearance);
                    if ($clearance > $bestClearance) {
                        $bestClearance = $clearance;
                        $best = [$x, $y];
                    }
                }
            }
        };

        $step = max($maxX - $minX, $maxY - $minY) / $this->gridSteps;
        if (!$step) {
            return null;
        }

        $scan($minX, $maxX, $minY, $maxY, $step);
        for ($pass = 0; $pass < $this->refinePasses && $best !== null; $pass++) {
            $step /= $this->refineFactor;
            $scan(
                $best[0] - $step * $this->refineFactor,
                $best[0] + $step * $this->refineFactor,
                $best[1] - $step * $this->refineFactor,
                $best[1] + $step * $this->refineFactor,
                $step
            );
        }

        return $best;
    }
}
