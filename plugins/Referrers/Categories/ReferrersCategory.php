<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers\Categories;

use Piwik\Category\Category;

class ReferrersCategory extends Category
{
    protected $id = 'Referrers_Referrers';
    protected $order = 15;
    protected $icon = 'icon-reporting-referer';
}
