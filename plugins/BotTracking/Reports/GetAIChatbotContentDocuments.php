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
}
