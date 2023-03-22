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
 * The percentage of visits that leave the site without visiting another page. Calculated
 * as:
 *
 *     bounce_count / nb_visits
 *
 * bounce_count & nb_visits are calculated by an Archiver.
 */
class BounceRate extends ProcessedMetric
{
    public function getName()
    {
        return 'bounce_rate';
    }

    public function getTranslatedName()
    {
        return Piwik::translate('General_ColumnBounceRate');
    }

    public function getDependentMetrics()
    {
        return array('bounce_count', 'nb_visits');
    }

    public function format($value, Formatter $formatter)
    {
        return $formatter->getPrettyPercentFromQuotient($value);
    }

    public function compute(Row $row)
    {
        $bounceCount = $this->getMetric($row, 'bounce_count');
        $visits = $this->getMetric($row, 'nb_visits');

        return Piwik::getQuotientSafe($bounceCount, $visits, $precision = 2);
    }

    public function getSemanticType(): ?string
    {
        return Dimension::TYPE_PERCENT;
    }
}