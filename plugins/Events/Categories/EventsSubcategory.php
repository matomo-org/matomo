<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Events\Categories;

use Piwik\Category\Subcategory;

class EventsSubcategory extends Subcategory
{
    protected $categoryId = 'General_Actions';
    protected $id = 'Events_Events';
    protected $order = 40;

}
