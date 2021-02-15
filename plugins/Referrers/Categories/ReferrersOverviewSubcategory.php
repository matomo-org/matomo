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

class ReferrersOverviewSubcategory extends Subcategory
{
    protected $categoryId = 'Referrers_Referrers';
    protected $id = 'General_Overview';
    protected $order = 2;

    public function getHelp()
    {
        return '<p>' . Piwik::translate('Referrers_ReferrersOverviewSubcategoryHelp1') . '</p>'
            . '<p>' . Piwik::translate('Referrers_ReferrersOverviewSubcategoryHelp2') . '</p>';
    }
}
