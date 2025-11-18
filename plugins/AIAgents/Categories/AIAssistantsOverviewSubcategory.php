<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIAgents\Categories;

use Piwik\Category\Subcategory;
use Piwik\Piwik;

class AIAssistantsOverviewSubcategory extends Subcategory
{
    protected $categoryId = 'AIAgents_AIAssistants';
    protected $id = 'General_Overview';
    protected $order = 10;

    public function getHelp()
    {
        return sprintf(
            '<p>%1$s</p><p>%2$s</p><p>%3$s</p>',
            Piwik::translate('AIAgents_AIAssistantsOverviewHelp1'),
            Piwik::translate('AIAgents_AIAssistantsOverviewHelp2'),
            Piwik::translate('AIAgents_AIAssistantsOverviewHelp3')
        );
    }
}
