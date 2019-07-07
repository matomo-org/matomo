<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\CoreHome\Columns\Metrics;

use Piwik\DataTable\Row;
use Piwik\Metrics;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugin\ProcessedMetric;

/**
 * The percent of visits that result in a conversion. Calculated as:
 *
 *     nb_visits_converted / nb_visits
 *
 * nb_visits_converted & nb_visits are calculated by the archiving process.
 */
class ConversionRate extends ProcessedMetric
{
    public function getName()
    {
        return 'conversion_rate';
    }

    public function getTranslatedName()
    {
        return Piwik::translate('General_ColumnConversionRate');
    }

    public function getDependentMetrics()
    {
        return array('nb_visits_converted', 'nb_visits');
    }

    public function format($value, Formatter $formatter)
    {
        return $formatter->getPrettyPercentFromQuotient($value);
    }

    public function compute(Row $row)
    {
        $nbVisits = $this->getMetric($row, 'nb_visits');
        $nbVisitsConverted = $this->getMetric($row, 'nb_visits_converted');
        if ($nbVisitsConverted === false) {
            $goals = $this->getMetric($row, 'goals');

            if (!empty($goals)) {
                $nbVisitsConverted = 0;
                foreach ([0, '0', 'idgoal=0'] as $possibleKey) {
                    if (!isset($goals[$possibleKey])) {
                        continue;
                    }

                    $nbVisitsConverted = $this->getMetric($goals[$possibleKey], 'nb_visits_converted', Metrics::getMappingFromNameToIdGoal());
                }
            }
        }

        return Piwik::getQuotientSafe($nbVisitsConverted, $nbVisits, $precision = 4);
    }
}