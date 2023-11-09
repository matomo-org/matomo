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

class MarketplaceCategory extends Category
{
    protected $id = 'Marketplace_Marketplace';
    protected $widget = 'Marketplace.RichMenuButton';
    protected $order = 200;
}
