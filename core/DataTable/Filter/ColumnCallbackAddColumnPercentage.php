<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\DataTable\Filter;

use Piwik\Piwik;

/**
 * Calculates a percentage value for each row of a {@link DataTable} and adds the result
 * to each row.
 *
 * See {@link ColumnCallbackAddColumnQuotient} for more information.
 *
 * **Basic usage example**
 *
 *     $nbVisits = // ... get the visits for a period ...
 *     $dataTable->queueFilter('ColumnCallbackAddColumnPercentage', array('nb_visits', 'nb_visits_percentage', $nbVisits, 1));
 *
 * @api
 */
class ColumnCallbackAddColumnPercentage extends ColumnCallbackAddColumnQuotient
{
    /**
     * Formats the given value as a percentage.
     *
     * @param number $value
     * @param number $divisor
     * @return string
     */
    protected function formatValue($value, $divisor)
    {
        return Piwik::getPercentageSafe($value, $divisor, $this->quotientPrecision) . '%';
    }
}
