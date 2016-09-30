<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Ecommerce\Widgets;

use Piwik\Common;
use Piwik\Widget\WidgetConfig;
use Piwik\Site;

class GetEcommerceLog extends \Piwik\Widget\Widget
{
    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('Goals_Ecommerce');
        $config->setSubcategoryId('Goals_EcommerceLog');
        $config->setName('Goals_EcommerceLog');

        $idSite = Common::getRequestVar('idSite', 0, 'int');
        if (empty($idSite)) {
            $config->disable();
            return;
        }

        $site  = new Site($idSite);
        $config->setIsEnabled($site->isEcommerceEnabled());
    }

}
