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

class SocialsSubcategory extends Subcategory
{
    protected $categoryId = 'Referrers_Referrers';
    protected $id = 'Referrers_Socials';
    protected $order = 16;

    public function getHelp()
    {
        return '<p>' . Piwik::translate('Referrers_SocialsSubcategoryHelp') . '</p>'
            . '<p>' . Piwik::translate('Referrers_WebsitesSubcategoryHelp2') . '</p>';
    }
}
