<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\CoreHome\Columns\Metrics;

use Piwik\Columns\Dimension;
use Piwik\DataTable\Row;
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
        $nbVisitsConverted = $this->getMetric($row, 'nb_visits_converted');
        $nbVisits = $this->getMetric($row, 'nb_visits');
        return Piwik::getQuotientSafe($nbVisitsConverted, $nbVisits, $precision = 4);
    }

    public function getSemanticType(): ?string
    {
        return Dimension::TYPE_PERCENT;
    }
}