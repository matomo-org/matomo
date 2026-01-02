<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Live\Categories;

use Piwik\Category\Subcategory;
use Piwik\Piwik;
use Piwik\Url;

class VisitorLogSubcategory extends Subcategory
{
    protected $categoryId = 'General_Visitors';
    protected $id = 'Live_VisitorLog';
    protected $order = 5;

    public function getHelp()
    {
        $help = '<p>' . Piwik::translate('Live_VisitorLogSubcategoryHelp1') . '</p>';
        $help .= '<p>' . Piwik::translate('Live_VisitorLogSubcategoryHelp2') . '</p>';
        $help .= '<p>' . Url::getExternalLinkTag('https://matomo.org/docs/real-time/', null, null, 'App.Live.getLastVisitsDetails')
            . Piwik::translate('Live_VisitorLogSubcategoryHelp3') . '</a></p>';
        return $help;
    }
}
