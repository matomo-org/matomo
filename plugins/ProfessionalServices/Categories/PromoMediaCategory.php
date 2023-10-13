<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ProfessionalServices\Categories;

use Piwik\Category\Category;

class PromoMediaCategory extends Category
{
    protected $id = 'ProfessionalServices_PromoMedia';
    protected $order = 50;
    protected $icon = 'icon-folder-charts';
}
