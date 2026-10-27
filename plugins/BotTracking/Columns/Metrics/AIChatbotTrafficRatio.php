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
use Piwik\NumberFormatter;
use Piwik\Piwik;
use Piwik\Plugin\ProcessedMetric;
use Piwik\Plugins\Actions\Archiver as ActionsArchiver;
use Piwik\Plugins\BotTracking\Metrics;

class AIChatbotTrafficRatio extends ProcessedMetric
{
    public function getName()
    {
        return Metrics::METRIC_AI_CHATBOTS_TRAFFIC_RATIO;
    }

    public function getTranslatedName()
    {
        return Piwik::translate('BotTracking_ColumnAIChatbotTrafficRatio');
    }

    public function getDocumentation()
    {
        return Piwik::translate('BotTracking_ColumnAIChatbotTrafficRatioDocumentation');
    }

    public function getDependentMetrics()
    {
        return [
            Metrics::METRIC_AI_CHATBOTS_REQUESTS,
            ActionsArchiver::METRIC_PAGEVIEWS_RECORD_NAME,
        ];
    }

    public function compute(Row $row)
    {
        $rawRequests  = $this->getMetric($row, Metrics::METRIC_AI_CHATBOTS_REQUESTS);
        $rawPageviews = $this->getMetric($row, ActionsArchiver::METRIC_PAGEVIEWS_RECORD_NAME);

        $requests  = is_numeric($rawRequests) ? (int) $rawRequests : 0;
        $pageviews = is_numeric($rawPageviews) ? (int) $rawPageviews : 0;

        // Without page views the ratio is unavailable, not zero
        if (empty($pageviews)) {
            return false;
        }

        // Not rounded: a busy site's ratio can be far below any fixed precision
        return (float) ($requests / $pageviews);
    }

    /**
     * @param number $value
     * @return string
     */
    public function format($value, Formatter $formatter)
    {
        $value = (float) $value;
        $numberFormatter = NumberFormatter::getInstance();

        // Some chatbot traffic would otherwise read as none at all
        if ($value > 0 && $value < 0.005) {
            return Piwik::translate('BotTracking_AIChatbotTrafficRatioBelowValue', $numberFormatter->formatNumber(0.01, 2, 2));
        }

        // A ratio below 1 is common on a busy site, so it keeps a second decimal: 0.04× rather than 0.0×
        $number = $numberFormatter->formatNumber($value, $value < 1 ? 2 : 1, 1);

        return Piwik::translate('BotTracking_AIChatbotTrafficRatioValue', $number);
    }

    public function getSemanticType(): ?string
    {
        return Dimension::TYPE_FLOAT;
    }
}
