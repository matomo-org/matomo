<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIAgents\Categories;

use Piwik\Category\Category;

class AIAssistantsCategory extends Category
{
    protected $id    = 'AIAgents_AIAssistants';
    protected $order = 80;
    protected $icon  = 'icon-admin-platform';
}
