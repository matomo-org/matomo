<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\Categories;

use Piwik\Category\Subcategory;

class PagesSubcategory extends Subcategory
{
    protected $categoryId = 'General_Actions';
    protected $id = 'General_Pages';
    protected $order = 5;

}
