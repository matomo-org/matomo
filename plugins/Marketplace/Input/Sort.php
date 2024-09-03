<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\Input;

/**
 */
class Sort
{
    public const METHOD_POPULAR = 'popular';
    public const METHOD_ALPHA = 'alpha';
    public const METHOD_LAST_UPDATED = 'lastupdated';
    public const METHOD_NEWEST = 'newest';
    public const DEFAULT_SORT = self::METHOD_LAST_UPDATED;

    public function getSort(string $sort): string
    {
        if (!$this->isValidSortMethod($sort)) {
            $sort = self::DEFAULT_SORT;
        }

        return $sort;
    }

    private function isValidSortMethod(string $sortMethod): bool
    {
        $valid = array(self::METHOD_POPULAR, self::METHOD_NEWEST, self::METHOD_ALPHA);

        return in_array($sortMethod, $valid, $strict = true);
    }
}
