<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Dashboard\Categories;

use Piwik\Category\Category;

class DashboardCategory extends Category
{
    protected $id = 'Dashboard_Dashboard';
    protected $order = 0;
    protected $icon = 'icon-reporting-dashboard';
}
