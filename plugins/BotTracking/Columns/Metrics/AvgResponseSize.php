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

class AvgResponseSize extends ProcessedMetric
{
    public function getName()
    {
        return Metrics::COLUMN_AVG_RESPONSE_SIZE;
    }

    public function getTranslatedName()
    {
        return Piwik::translate('BotTracking_ColumnAvgResponseSize');
    }

    public function getDocumentation()
    {
        return Piwik::translate('BotTracking_ColumnAvgResponseSizeDocumentation');
    }

    public function compute(Row $row)
    {
        $rawSum = $this->getMetric($row, Metrics::COLUMN_SUM_RESPONSE_SIZE);
        $rawNb  = $this->getMetric($row, Metrics::COLUMN_NB_RESPONSE_SIZE);

        $sum = is_numeric($rawSum) ? (int) $rawSum : 0;
        $nb  = is_numeric($rawNb)  ? (int) $rawNb  : 0;

        if (empty($nb)) {
            return false;
        }

        return (float)($sum / $nb);
    }

    public function getDependentMetrics()
    {
        return [
            Metrics::COLUMN_SUM_RESPONSE_SIZE,
            Metrics::COLUMN_NB_RESPONSE_SIZE,
        ];
    }

    /**
     * @param float|int $value
     * @return string
     */
    public function format($value, Formatter $formatter)
    {
        return $formatter->getPrettySizeFromBytes($value);
    }

    public function getSemanticType(): ?string
    {
        return Dimension::TYPE_BYTE;
    }
}
