<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Live\Categories;

use Piwik\Category\Subcategory;
use Piwik\Piwik;

class RealTimeVisitorsSubcategory extends Subcategory
{
    protected $categoryId = 'General_Visitors';
    protected $id = 'General_RealTime';
    protected $order = 7;

    public function getHelp()
    {
        $result = '<p>' . Piwik::translate('Live_RealTimeHelp1') . '</p>';
        $result .= '<p>' . Piwik::translate('Live_RealTimeHelp2') . '</p>';
        return $result;
    }
}
