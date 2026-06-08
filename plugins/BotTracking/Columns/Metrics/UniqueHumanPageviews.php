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
 * Column metric for the human-side pageview count on the Human/AI-Favoured Pages reports.
 *
 * Extends AggregatedMetric purely to supply the column's name/translation/format/semantic-type
 * metadata. Unlike a typical AggregatedMetric this value is NOT aggregated from an archive — it is
 * injected at API time by {@see \Piwik\Plugins\BotTracking\DataTable\FavouredPagesMerger} from the
 * Actions page-URLs report. AggregatedMetric is simply the closest base for "a plain column that
 * isn't a processed metric".
 */
class UniqueHumanPageviews extends AggregatedMetric
{
    public function getName()
    {
        return Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS;
    }

    public function getTranslatedName()
    {
        return Piwik::translate('BotTracking_ColumnUniqueHumanPageviews');
    }

    public function getDocumentation()
    {
        return Piwik::translate('BotTracking_ColumnUniqueHumanPageviewsDocumentation');
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
