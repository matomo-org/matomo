<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\VisitTime\Categories;

use Piwik\Category\Subcategory;

class TimesSubcategory extends Subcategory
{
    protected $categoryId = 'General_Visitors';
    protected $id = 'VisitTime_SubmenuTimes';
    protected $order = 35;

}
