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

class AIChatbotsOverviewSubcategory extends Subcategory
{
    protected $categoryId = 'General_AIAssistants';
    protected $id = 'BotTracking_AIChatbotsOverview';
    protected $order = 10;

    public function getHelp()
    {
        return sprintf(
            '<p>%1$s</p><p>%2$s</p><p>%3$s</p>',
            Piwik::translate('BotTracking_AIChatbotsOverviewHelp1'),
            Piwik::translate('BotTracking_AIChatbotsOverviewHelp2'),
            Piwik::translate('BotTracking_AIChatbotsOverviewHelp3')
        );
    }
}
