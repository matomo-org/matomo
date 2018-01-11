<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Goals\Categories;

use Piwik\Category\Category;

class GoalsCategory extends Category
{
    protected $id = 'Goals_Goals';
    protected $order = 25;
    protected $icon = 'icon-reporting-goal';
}
