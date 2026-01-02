<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Goals\Categories;

use Piwik\Category\Subcategory;
use Piwik\Piwik;
use Piwik\Url;

class ManageGoalsSubcategory extends Subcategory
{
    protected $categoryId = 'Goals_Goals';
    protected $id = 'Goals_ManageGoals';
    protected $order = 9999;

    public function getHelp()
    {
        return '<p>' . Piwik::translate('Goals_ManageGoalsSubcategoryHelp1') . '</p>'
            . '<p>' . Url::getExternalLinkTag('https://matomo.org/docs/tracking-goals-web-analytics/') . Piwik::translate('Goals_ManageGoalsSubcategoryHelp2') . '</a></p>';
    }
}
