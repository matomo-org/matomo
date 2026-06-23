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

class TotalBrokenRequests extends AggregatedMetric
{
    public function getName()
    {
        return Metrics::COLUMN_TOTAL_BROKEN_REQUESTS;
    }

    public function getTranslatedName()
    {
        return Piwik::translate('BotTracking_ColumnTotalBrokenRequests');
    }

    public function getDocumentation()
    {
        return Piwik::translate('BotTracking_ColumnTotalBrokenRequestsDocumentation');
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
