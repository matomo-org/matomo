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
     * Whether two goal definitions cover the same conversion: same match attribute
     * and equal patterns, or one pattern clearly containing the other.
     */
    public static function covers(string $attributeA, string $patternA, string $attributeB, string $patternB): bool
    {
        if ($attributeA !== $attributeB) {
            return false;
        }

        $a = self::normalizePattern($patternA, $attributeA);
        $b = self::normalizePattern($patternB, $attributeB);
        if ($a === '' || $b === '') {
            return false;
        }

        return $a === $b || strpos($a, $b) !== false || strpos($b, $a) !== false;
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
