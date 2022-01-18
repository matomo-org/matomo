<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Contents\Categories;

use Piwik\Category\Subcategory;
use Piwik\Piwik;

class ContentsSubcategory extends Subcategory
{
    protected $categoryId = 'General_Actions';
    protected $id = 'Contents_Contents';
    protected $order = 45;

    public function getHelp()
    {
        return '<p>' . Piwik::translate('Contents_ContentsSubcategoryHelp1') . '</p>'
            . '<p><a href="https://matomo.org/docs/content-tracking/?mtm_campaign=App_Help&mtm_source=Matomo_App&&mtm_keyword=UserGuides" rel="noreferrer noopener" target="_blank">' . Piwik::translate('Contents_ContentsSubcategoryHelp2') . '</a></p>';
    }
}
