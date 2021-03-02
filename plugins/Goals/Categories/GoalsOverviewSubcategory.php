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

class GoalsOverviewSubcategory extends Subcategory
{
    protected $categoryId = 'Goals_Goals';
    protected $id = 'General_Overview';
    protected $order = 2;

    public function getHelp()
    {
        return '<p>' . Piwik::translate('Goals_GoalsOverviewSubcategoryHelp1') . '</p>'
            . '<p>' . Piwik::translate('Goals_GoalsOverviewSubcategoryHelp2') . '</p>';
    }
}
