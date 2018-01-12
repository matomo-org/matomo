<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry\Categories;

use Piwik\Category\Category;

class LocationsCategory extends Category
{
    protected $id = 'UserCountry_VisitLocation';
    protected $order = 7;
}
