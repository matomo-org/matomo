<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Referrers\Columns\Metrics;

use Piwik\DataTable\Row;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugin\ProcessedMetric;

class VisitorsFromReferrerPercent extends ProcessedMetric
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $referrerSourceColumn;

    /**
     * @var numeric
     */
    private $totalVisits;

    public function __construct(string $name, string $referrerSourceColumn, $totalVisits)
    {
        $this->name = $name;
        $this->referrerSourceColumn = $referrerSourceColumn;
        $this->totalVisits = $totalVisits;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getTranslatedName()
    {
        return null;
    }

    public function compute(Row $row)
    {
        $columnValue = self::getMetric($row, $this->referrerSourceColumn);
        $result = Piwik::getQuotientSafe($columnValue, $this->totalVisits, $precision = 4);
        return $result;
    }

    public function getDependentMetrics()
    {
        return [$this->referrerSourceColumn];
    }

    public function format($value, Formatter $formatter)
    {
        return $formatter->getPrettyPercentFromQuotient($value);
    }
}
