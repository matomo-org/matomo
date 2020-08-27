<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Ecommerce\Widgets;

use Piwik\Common;
use Piwik\Plugins\CoreHome\CoreHome;
use Piwik\Site;
use Piwik\Widget\WidgetContainerConfig;

class ProductsByDimension extends WidgetContainerConfig
{
    protected $layout = CoreHome::WIDGET_CONTAINER_LAYOUT_BY_DIMENSION;
    protected $id = 'Products';
    protected $categoryId = 'Goals_Ecommerce';
    protected $subcategoryId = 'Goals_Products';

    public function isEnabled()
    {
        $idSite = Common::getRequestVar('idSite', false, 'int');

        if (empty($idSite)) {
            return false;
        }

        $site = new Site($idSite);
        return $site->isEcommerceEnabled();
    }
}
