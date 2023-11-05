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
use Piwik\Url;

class ContentsSubcategory extends Subcategory
{
    protected $categoryId = 'General_Actions';
    protected $id = 'Contents_Contents';
    protected $order = 45;

    public function getHelp()
    {
        return '<p>' . Piwik::translate('Contents_ContentsSubcategoryHelp1') . '</p>'
            . '<p><a href="' . Url::addCampaignParametersToMatomoLink('https://matomo.org/docs/content-tracking', null, null, 'Contents.getContentNames')
            . '" rel="noreferrer noopener" target="_blank">' . Piwik::translate('Contents_ContentsSubcategoryHelp2') . '</a></p>';
    }
}
