<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\Columns\Metrics;

use Piwik\Columns\Dimension;
use Piwik\DataTable\Row;
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugin\ProcessedMetric;
use Piwik\Plugins\BotTracking\Metrics;

class AvgServerTime extends ProcessedMetric
{
    public function getName()
    {
        return Metrics::COLUMN_AVG_SERVER_TIME;
    }

    public function getTranslatedName()
    {
        return Piwik::translate('BotTracking_ColumnAvgServerTime');
    }

    public function getDocumentation()
    {
        return Piwik::translate('BotTracking_ColumnAvgServerTimeDocumentation');
    }

    public function compute(Row $row)
    {
        $rawSum = $this->getMetric($row, Metrics::COLUMN_SUM_SERVER_TIME);
        $rawNb  = $this->getMetric($row, Metrics::COLUMN_NB_SERVER_TIME);

        $sum = is_numeric($rawSum) ? (int) $rawSum : 0;
        $nb  = is_numeric($rawNb)  ? (int) $rawNb  : 0;

        if (empty($nb)) {
            return false;
        }

        // Stored values are in milliseconds; divide by 1000 to return seconds.
        return (float)($sum / $nb / 1000);
    }

    public function getDependentMetrics()
    {
        return [
            Metrics::COLUMN_SUM_SERVER_TIME,
            Metrics::COLUMN_NB_SERVER_TIME,
        ];
    }

    /**
     * @param float|int $value
     * @return string
     */
    public function format($value, Formatter $formatter)
    {
        return $formatter->getPrettyTimeFromSeconds($value, true);
    }

    public function getSemanticType(): ?string
    {
        return Dimension::TYPE_DURATION_S;
    }
}
