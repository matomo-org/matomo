<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\Reports;

use Piwik\Piwik;
use Piwik\Plugins\BotTracking\Columns\Metrics\DiscrepancyScore;
use Piwik\Plugins\BotTracking\Metrics;

class GetAIChatbotHumanFavouredPages extends AbstractAIChatbotFavouredPagesReport
{
    protected function init(): void
    {
        parent::init();

        $this->name              = Piwik::translate('BotTracking_AIChatbotsHumanFavouredPagesTitle');
        $this->documentation     = Piwik::translate('BotTracking_AIChatbotsHumanFavouredPagesDocumentation');
        $this->order             = 40;
        $this->defaultSortColumn = Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS;
    }

    protected function getDiscrepancyScoreVariant(): string
    {
        return DiscrepancyScore::VARIANT_HUMAN_FAVOURED;
    }

    protected function getExcludeLowPopulationColumn(): string
    {
        return Metrics::COLUMN_UNIQUE_HUMAN_PAGEVIEWS;
    }
}
