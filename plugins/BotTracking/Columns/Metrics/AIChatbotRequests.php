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
use Piwik\Metrics\Formatter;
use Piwik\Piwik;
use Piwik\Plugin\AggregatedMetric;
use Piwik\Plugins\BotTracking\Metrics;

/**
 * Column metric for the AI-chatbot request count on the Human/AI-Favoured Pages reports.
 *
 * Extends AggregatedMetric purely to supply the column's name/translation/format/semantic-type
 * metadata. The value is materialised during archiving by
 * {@see \Piwik\Plugins\BotTracking\RecordBuilders\AIChatbotFavouredPages} (the AI chatbot
 * page-request count). AggregatedMetric is simply the closest base for "a plain column that
 * isn't a processed metric".
 */
class AIChatbotRequests extends AggregatedMetric
{
    public function getName()
    {
        return Metrics::COLUMN_AI_CHATBOT_REQUESTS;
    }

    public function getTranslatedName()
    {
        return Piwik::translate('BotTracking_ColumnAIChatbotRequests');
    }

    public function getDocumentation()
    {
        return Piwik::translate('BotTracking_ColumnAIChatbotRequestsDocumentation');
    }

    /**
     * @param int $value
     * @return string
     */
    public function format($value, Formatter $formatter)
    {
        return $formatter->getPrettyNumber($value);
    }

    public function getSemanticType(): ?string
    {
        return Dimension::TYPE_NUMBER;
    }
}
