<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Transitions\Categories;

use Piwik\Category\Subcategory;
use Piwik\Piwik;
use Piwik\Url;

class TransitionsSubcategory extends Subcategory
{
    protected $categoryId = 'General_Actions';
    protected $id = 'Transitions_Transitions';
    protected $order = 46;

    public function getHelp()
    {
        return '<p>' . Piwik::translate('Transitions_TransitionsSubcategoryHelp1') . '</p>'
            . '<p><a href="' . Url::addCampaignParametersToMatomoLink('https://matomo.org/docs/transitions/') . '" rel="noreferrer noopener" target="_blank">' . Piwik::translate('Transitions_TransitionsSubcategoryHelp2') . '</a></p>';
    }
}
