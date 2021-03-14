<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountryMap\Categories;

use Piwik\Category\Subcategory;
use Piwik\Piwik;

class RealTimeMapSubcategory extends Subcategory
{
    protected $categoryId = 'General_Visitors';
    protected $id = 'UserCountryMap_RealTimeMap';
    protected $order = 9;

    public function getHelp()
    {
        return '<p>' . Piwik::translate('UserCountryMap_RealTimeMapHelp') . '</p>';
    }
}
