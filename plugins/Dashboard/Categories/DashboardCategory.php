<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Dashboard\Categories;

use Piwik\Category\Category;
use Piwik\Piwik;

class DashboardCategory extends Category
{
    protected $id = 'Dashboard_Dashboard';
    protected $order = 0;
    protected $icon = 'icon-reporting-dashboard';

    public function getHelp()
    {
        return '<p>' . Piwik::translate('Dashboard_DashboardCategoryHelp', ['<strong>', '</strong>']) . '</p>';
    }
}
