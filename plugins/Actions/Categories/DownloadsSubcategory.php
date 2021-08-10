<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\Categories;

use Piwik\Category\Subcategory;
use Piwik\Piwik;

class DownloadsSubcategory extends Subcategory
{
    protected $categoryId = 'General_Actions';
    protected $id = 'General_Downloads';
    protected $order = 35;

    public function getHelp()
    {
        return '<p>' . Piwik::translate('Actions_DownloadsSubcategoryHelp1') . '</p>'
            . '<p>' . Piwik::translate('Actions_DownloadsSubcategoryHelp2') . '</p>';
    }
}
