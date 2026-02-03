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

class ClickThroughRate extends ProcessedMetric
{
    public function getName()
    {
        return Metrics::METRIC_AI_ASSISTANTS_CLICK_THROUGH_RATE;
    }

    public function getTranslatedName()
    {
        return Piwik::translate('BotTracking_ColumnClickThroughRate');
    }

    public function getDocumentation()
    {
        return Piwik::translate('BotTracking_ColumnClickThroughRateDocumentation');
    }

    public function getDependentMetrics()
    {
        return [
            Metrics::METRIC_AI_ASSISTANTS_REQUESTS,
            Metrics::METRIC_AI_ASSISTANTS_ACQUIRED_VISITS,
        ];
    }

    public function compute(Row $row)
    {
        $requests = (int)$this->getMetric($row, Metrics::METRIC_AI_ASSISTANTS_REQUESTS);
        $visits   = (int)$this->getMetric($row, Metrics::METRIC_AI_ASSISTANTS_ACQUIRED_VISITS);

        return Piwik::getQuotientSafe($visits, $requests, 4);
    }

    /**
     * @param number $value
     * @return string
     */
    public function format($value, Formatter $formatter)
    {
        return $formatter->getPrettyPercentFromQuotient($value);
    }

    public function getSemanticType(): ?string
    {
        return Dimension::TYPE_PERCENT;
    }
}
