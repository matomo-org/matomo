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

class GetTopPageUrlsRealTime extends AbstractAIChatbotsRealTimeTopPageUrlsReport
{
    protected function init(): void
    {
        parent::init();

        $this->name          = Piwik::translate('BotTracking_TopPageUrlsLast30MinutesTitle');
        $this->documentation = Piwik::translate('BotTracking_TopPageUrlsLast30MinutesDocumentation');
        $this->order         = 30;
    }

    /**
     * @return array<int, array{name: string, documentation: string, lastMinutes: int, order: int}>
     */
    protected function getWidgetDefinitions(): array
    {
        return AIChatbotsRealTimeWidgets::getTopPageUrlWidgets();
    }
}
