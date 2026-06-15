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
use Piwik\Plugins\BotTracking\Columns\DocumentUrl;
use Piwik\Widget\WidgetsList;
use Piwik\Report\ReportWidgetFactory;

class GetAIChatbotContentDocuments extends AbstractAIChatbotContentUrlReport
{
    protected function init(): void
    {
        parent::init();

        $this->name          = Piwik::translate('BotTracking_AIChatbotsContentDocumentsTitle');
        $this->documentation = Piwik::translate('BotTracking_AIChatbotsContentDocumentsDocumentation');
        $this->dimension     = new DocumentUrl();
        $this->order         = 20;
    }

    protected function getRequestsDocumentationKey(): string
    {
        // Scope the "Requests" tooltip to document URLs since this report covers only download actions.
        return 'BotTracking_ColumnDocumentRequestsDocumentation';
    }

    public function configureWidgets(WidgetsList $widgetsList, ReportWidgetFactory $factory): void
    {
        // Override the wide layout used by the shared base class (which keeps the Pages report
        // full width) so this report pairs side by side with the Broken Pages & Documents report
        // on the Content Requests page.
        $widgetsList->addWidgetConfig($factory->createWidget());
    }
}
