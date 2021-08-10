<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\CoreHome\Categories;

use Piwik\Category\Subcategory;
use Piwik\Piwik;

class SoftwareSubcategory extends Subcategory
{
    protected $categoryId = 'General_Visitors';
    protected $id = 'DevicesDetection_Software';
    protected $order = 20;

    public function getHelp()
    {
        return '<p>' . Piwik::translate('CoreHome_SoftwareSubcategoryHelp') . '</p>';
    }
}
