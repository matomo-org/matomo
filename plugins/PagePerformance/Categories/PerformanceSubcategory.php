<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\PagePerformance\Categories;

use Piwik\Category\Subcategory;
use Piwik\Piwik;

class PerformanceSubcategory extends Subcategory
{
    protected $categoryId = 'General_Actions';
    protected $id = 'PagePerformance_Performance';
    protected $order = 47;

    public function getHelp()
    {
        return '<p>' . Piwik::translate('PagePerformance_PerformanceSubcategoryHelp1') . '</p>'
            . '<p>' . Piwik::translate('PagePerformance_PerformanceSubcategoryHelp2') . '</p>';
    }
}
