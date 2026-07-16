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

class GetAIChatbotAIFavouredPages extends AbstractAIChatbotFavouredPagesReport
{
    protected function init(): void
    {
        parent::init();

        $this->name          = Piwik::translate('BotTracking_AIChatbotsAIFavouredPagesTitle');
        $this->documentation = Piwik::translate('BotTracking_AIChatbotsAIFavouredPagesDocumentation');
        // Order 50 keeps this the last widget so it pairs with Human-Favoured (order 40) into the
        // side-by-side row — see the layout contract in
        // AbstractAIChatbotFavouredPagesReport::configureWidgets().
        $this->order         = 50;
    }

    protected function getDiscrepancyScoreVariant(): string
    {
        return DiscrepancyScore::VARIANT_AI_FAVOURED;
    }
}
