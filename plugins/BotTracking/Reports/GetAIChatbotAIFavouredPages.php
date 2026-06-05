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

class GetAIChatbotAIFavouredPages extends AbstractAIChatbotFavouredPagesReport
{
    protected function init(): void
    {
        parent::init();

        $this->name              = Piwik::translate('BotTracking_AIChatbotsAIFavouredPagesTitle');
        $this->documentation     = Piwik::translate('BotTracking_AIChatbotsAIFavouredPagesDocumentation');
        $this->order             = 50;
        $this->defaultSortColumn = Metrics::COLUMN_AI_CHATBOT_REQUESTS;
    }

    protected function getDiscrepancyScoreVariant(): string
    {
        return DiscrepancyScore::VARIANT_AI_FAVOURED;
    }

    protected function getExcludeLowPopulationColumn(): string
    {
        return Metrics::COLUMN_AI_CHATBOT_REQUESTS;
    }
}
