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

class AIAgentsOverviewSubcategory extends Subcategory
{
    protected $categoryId = 'General_AIAssistants';
    protected $id = 'AIAgents_AIAgentsOverview';
    protected $order = 10;

    public function getHelp()
    {
        return sprintf(
            '<p>%1$s</p>',
            Piwik::translate('AIAgents_AIAgentsOverviewSubcategoryDescription')
        );
    }
}
