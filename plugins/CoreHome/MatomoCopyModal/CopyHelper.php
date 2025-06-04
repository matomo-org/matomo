<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreHome\MatomoCopyModal;

/**
 *
 */
class CopyHelper
{
    /**
     * Update the provided name with a number suffix. It will either add a suffix or increment the number in the suffix.
     *
     * @param string $name The name that needs to be updated with a number suffix. If no suffix exists, one will be
     * added. If one already exists, the number in the suffix will be incremented.
     * @param int $maxLength Optional number indicates the maximum allowed length. If adding the suffix exceeds the max,
     * the string will be truncated just enough to allow the suffix. Default is -1 allowing infinite length.
     * @return string Name with the updated number suffix
     * @throws \Exception If the maximum length is too small to accommodate a suffix like " (1)".
     */
    public static function incrementNameWithNumericalSuffix(string $name, int $maxLength = -1): string
    {
        $modifiedName = $name;

        // First check if the name already has a number suffix
        $matches = [];
        $number = 1;
        if (preg_match('/ \(\d+\)$/', $name, $matches)) {
            // Increment the number in the name suffix
            $number = intval(str_replace(['(', ')'], '', $matches[0]));
            ++$number;
            $modifiedName = str_replace($matches[0], '', $name);
        }

        // Make sure that the maximum length isn't too small and won't result in the suffix replacing the whole name
        $newNumberLength = strlen((string) $number);
        if ($maxLength !== -1 && $maxLength <= $newNumberLength + 3) {
            throw new \Exception('The maximum name length cannot be less than the length of the suffix.');
        }

        // Make sure that we don't exceed the max length the name fields
        if ($maxLength !== -1 && strlen($modifiedName . " ($number)") > $maxLength) {
            $modifiedName = substr($modifiedName, 0, ($maxLength - 3) - strlen((string) $number));
        }

        $modifiedName .= " ($number)";

        return $modifiedName;
    }
}
