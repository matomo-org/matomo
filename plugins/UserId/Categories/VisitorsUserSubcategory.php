<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserId\Categories;

use Piwik\Category\Subcategory;
use Piwik\Piwik;
use Piwik\Url;

class VisitorsUserSubcategory extends Subcategory
{
    protected $categoryId = 'General_Visitors';
    protected $id = 'UserId_UserReportTitle';
    protected $order = 40;


    public function getHelp()
    {
        return '<p>' . Piwik::translate('UserId_VisitorsUserSubcategoryHelp') . '</p>'.
            '<p><a target="_blank" rel="noopener noreferrer" href="' . Url::addCampaignParametersToMatomoLink('https://matomo.org/docs/user-id') . '"><span class="icon-info"></span> ' . Piwik::translate('CoreAdminHome_LearnMore') . '</a></p>';
    }
}
