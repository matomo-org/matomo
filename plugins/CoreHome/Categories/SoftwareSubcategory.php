<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreHome\Categories;

use Piwik\Category\Subcategory;

class SoftwareSubcategory extends Subcategory
{
    protected $categoryId = 'General_Visitors';
    protected $id = 'DevicesDetection_Software';
    protected $order = 20;

}
