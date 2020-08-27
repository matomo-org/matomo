<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Events\Categories;

use Piwik\Category\Category;

// Needed for dimensions and metrics
class EventsCategory extends Category
{
    protected $id = 'Events_Events';
    protected $order = 12;

}
