<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Goals\Recommendations;

/**
 * Shared pattern normalization and matching for goal recommendations. Used to
 * build stable recommendation identifiers and to decide whether a recommendation
 * is already covered by an existing goal.
 */
class RecommendationMatcher
{
    /**
     * Returns a stable identifier for a goal definition, or an empty string when
     * the pattern normalizes to nothing.
     */
    public static function buildKey(string $matchAttribute, string $pattern): string
    {
        $normalized = self::normalizePattern($pattern, $matchAttribute);

        return $normalized === '' ? '' : $matchAttribute . ':' . $normalized;
    }

    /**
     * Whether an existing goal already covers a recommended one: equal patterns,
     * or for "contains" goals segment-bounded containment, so "shop" covers
     * "/shop/checkout" but not "/workshop-signup". "exact" and "regex" goals only
     * cover equal patterns.
     */
    public static function covers(
        string $candidateAttribute,
        string $candidatePattern,
        string $existingAttribute,
        string $existingPattern,
        string $existingPatternType = 'contains'
    ): bool {
        if ($candidateAttribute !== $existingAttribute) {
            return false;
        }

        $candidate = self::normalizePattern($candidatePattern, $candidateAttribute);
        $existing = self::normalizePattern($existingPattern, $existingAttribute);
        if ($candidate === '' || $existing === '') {
            return false;
        }

        if ($candidate === $existing) {
            return true;
        }

        if (in_array($existingPatternType, ['exact', 'regex'], true)) {
            return false;
        }

        return self::containsAtSegmentBoundary($candidate, $existing)
            || self::containsAtSegmentBoundary($existing, $candidate);
    }

    /**
     * Containment delimited by non-alphanumeric characters or string ends.
     */
    private static function containsAtSegmentBoundary(string $haystack, string $needle): bool
    {
        $offset = 0;
        while (($position = strpos($haystack, $needle, $offset)) !== false) {
            $before = $position > 0 ? $haystack[$position - 1] : '';
            $afterPosition = $position + strlen($needle);
            $after = $afterPosition < strlen($haystack) ? $haystack[$afterPosition] : '';
            if (($before === '' || !ctype_alnum($before)) && ($after === '' || !ctype_alnum($after))) {
                return true;
            }
            $offset = $position + 1;
        }

        return false;
    }

    public static function normalizePattern(string $pattern, string $matchAttribute): string
    {
        $pattern = strtolower(trim($pattern));

        if ($matchAttribute === 'url' && preg_match('#^https?://#', $pattern)) {
            $path = parse_url($pattern, PHP_URL_PATH);
            $pattern = is_string($path) ? $path : $pattern;
        }

        if ($matchAttribute === 'file' && preg_match('#^https?://#', $pattern)) {
            $path = parse_url($pattern, PHP_URL_PATH);
            $pattern = is_string($path) ? basename($path) : $pattern;
        }

        return trim(rtrim($pattern, '/'), '/');
    }
}
