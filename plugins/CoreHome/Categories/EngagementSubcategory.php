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

class EngagementSubcategory extends Subcategory
{
    protected $categoryId = 'General_Actions';
    protected $id = 'VisitorInterest_Engagement';
    protected $order = 46;

    public function getHelp()
    {
        return '<p>' . Piwik::translate('CoreHome_EngagementSubcategoryHelp1') . '</p>'
            . '<p>' . Piwik::translate('CoreHome_EngagementSubcategoryHelp2') . '</p>';
    }
}
