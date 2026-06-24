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

class GetAIChatbotsLast8Hours extends AbstractAIChatbotsRealTimeChatbotsReport
{
    protected function init(): void
    {
        parent::init();

        $this->name          = Piwik::translate('BotTracking_AIChatbotsLast8HoursTitle');
        $this->documentation = Piwik::translate('BotTracking_AIChatbotsLast8HoursDocumentation');
        $this->order         = 20;
    }
}
