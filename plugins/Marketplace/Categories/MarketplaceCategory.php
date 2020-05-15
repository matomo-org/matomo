<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Marketplace\Categories;

use Piwik\Category\Category;
use Piwik\Piwik;

class MarketplaceCategory extends Category
{
    protected $id = 'Marketplace_Marketplace';
    protected $order = 200;
    protected $icon = ' icon-open-source';

    public function getDisplayName()
    {
        return Piwik::translate('Marketplace_Marketplace');
    }
}
