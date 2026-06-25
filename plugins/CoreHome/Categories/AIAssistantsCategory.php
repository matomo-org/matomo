<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\CoreHome\Categories;

use Piwik\Category\Category;

class AIAssistantsCategory extends Category
{
    protected $id = 'General_AIAssistants';
    protected $order = 80;
    protected $icon  = 'icon-ai-assistants';

    // Shown both in the main Analytics reporting menu (default group) and in the new "AI Insights"
    // section. To eventually move these reports out of Analytics entirely, drop Category::DEFAULT_GROUP
    // here so the category only appears under AI Insights.
    protected $groups = array(Category::DEFAULT_GROUP, 'CoreHome_AIInsights');
}
