<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\Categories;

use Piwik\Category\Subcategory;

class OutlinksSubcategory extends Subcategory
{
    protected $categoryId = 'General_Actions';
    protected $id = 'General_Outlinks';
    protected $order = 30;

}
