<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Ecommerce\Columns;

use Piwik\Container\StaticContainer;
use Piwik\Log\LoggerInterface;
use Piwik\Plugin\Dimension\ConversionDimension;
use Piwik\Tracker\GoalManager;

abstract class BaseConversion extends ConversionDimension
{
    /**
     * Returns rounded decimal revenue, or if revenue is integer, then returns as is.
     *
     * @param int|float $revenue
     * @return int|float|false
     */
    protected function roundRevenueIfNeeded($revenue)
    {
        if (false === $revenue) {
            return false;
        }

        // Reject out-of-range values (see GoalManager::MAX_ALLOWED_REVENUE); treat as not set.
        if (abs((float) $revenue) > GoalManager::MAX_ALLOWED_REVENUE) {
            StaticContainer::get(LoggerInterface::class)->debug(
                "Ecommerce value ({$revenue}) exceeds the allowed maximum of " . GoalManager::MAX_ALLOWED_REVENUE . " and was rejected (treated as not set)."
            );

            return false;
        }

        if (round($revenue) == $revenue) {
            return $revenue;
        }

        $value = round($revenue, GoalManager::REVENUE_PRECISION);

        return $value;
    }
}
