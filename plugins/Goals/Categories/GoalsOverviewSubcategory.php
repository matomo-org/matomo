<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Goals\Categories;

use Piwik\Category\Subcategory;
use Piwik\Piwik;
use Piwik\Url;

class GoalsOverviewSubcategory extends Subcategory
{
    protected $categoryId = 'Goals_Goals';
    protected $id = 'General_Overview';
    protected $order = 2;

    public function getHelp()
    {
        return '<p>' . Piwik::translate('Goals_GoalsOverviewSubcategoryHelp1') . '</p>'
            . '<p>' . Piwik::translate('Goals_GoalsOverviewSubcategoryHelp2') . '</p>'
            . '<p><a href="' . Url::addCampaignParametersToMatomoLink('https://matomo.org/docs/tracking-goals-web-analytics/', null, null, 'Goals.Overview')
            . '" rel="noreferrer noopener" target="_blank">' . Piwik::translate('Goals_ManageGoalsSubcategoryHelp2') . '</a></p>';
    }
}
