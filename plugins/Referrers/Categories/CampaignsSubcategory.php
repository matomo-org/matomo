<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers\Categories;

use Piwik\Category\Subcategory;
use Piwik\Piwik;

class CampaignsSubcategory extends Subcategory
{
    protected $categoryId = 'Referrers_Referrers';
    protected $id = 'Referrers_Campaigns';
    protected $order = 20;

    public function getHelp()
    {
        return '<p>' . Piwik::translate('Referrers_CampaignsSubcategoryHelp') . '</p>';
    }
}
