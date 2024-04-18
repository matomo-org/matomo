<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Marketplace\Input;

/**
 */
class PurchaseType
{
    public const TYPE_FREE = 'free';
    public const TYPE_PAID = 'paid';
    public const TYPE_ALL  = '';

    public function getPurchaseType(string $type): string
    {
        return $this->isValidPurchaseType($type) ? $type : self::TYPE_ALL;
    }

    private function isValidPurchaseType(string $type): bool
    {
        $valid = [self::TYPE_ALL, self::TYPE_FREE, self::TYPE_PAID];

        return in_array($type, $valid, $strict = true);
    }
}
