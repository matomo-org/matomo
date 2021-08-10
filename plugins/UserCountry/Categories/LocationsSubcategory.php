<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry\Categories;

use Piwik\Category\Subcategory;
use Piwik\Piwik;

class LocationsSubcategory extends Subcategory
{
    protected $categoryId = 'General_Visitors';
    protected $id = 'UserCountry_SubmenuLocations';
    protected $order = 10;

    public function getHelp()
    {
        return '<p>' . Piwik::translate('UserCountry_LocationsSubcategoryHelp') . '</p>';
    }
}
