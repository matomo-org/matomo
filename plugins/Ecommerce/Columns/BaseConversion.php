<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Ecommerce\Columns;

use Piwik\Plugin\Dimension\ConversionDimension;
use Piwik\Tracker\GoalManager;

abstract class BaseConversion extends ConversionDimension
{
    /**
     * Returns rounded decimal revenue, or if revenue is integer, then returns as is.
     *
     * @param int|float $revenue
     * @return int|float
     */
    protected function roundRevenueIfNeeded($revenue)
    {
        if (false === $revenue) {
            return false;
        }

        if (round($revenue) == $revenue) {
            return $revenue;
        }

        $value = round($revenue, GoalManager::REVENUE_PRECISION);

        return $value;
    }
}