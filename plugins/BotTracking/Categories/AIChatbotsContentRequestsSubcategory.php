<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\Categories;

use Piwik\Category\Subcategory;
use Piwik\Piwik;

class AIChatbotsContentRequestsSubcategory extends Subcategory
{
    protected $categoryId = 'General_AIAssistants';
    protected $id = 'BotTracking_AIChatbotsContentRequests';
    protected $order = 15;

    public function getHelp()
    {
        return sprintf(
            '<p>%1$s</p><p>%2$s</p><p>%3$s</p>',
            Piwik::translate('BotTracking_AIChatbotsContentRequestsHelp1'),
            Piwik::translate('BotTracking_AIChatbotsContentRequestsHelp2'),
            Piwik::translate('BotTracking_AIChatbotsContentRequestsHelp3')
        );
    }
}
