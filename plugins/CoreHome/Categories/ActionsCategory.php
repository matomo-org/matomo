<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreHome\Categories;

use Piwik\Category\Category;

class ActionsCategory extends Category
{
    protected $id = 'General_Actions';
    protected $order = 10;
    protected $icon = 'icon-reporting-actions';
}
