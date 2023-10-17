<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\ProfessionalServices\Widgets;

use Piwik\Container\StaticContainer;
use Piwik\Widget\Widget;
use Piwik\Widget\WidgetConfig;

class PromoCustomReports extends Widget
{
    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('ProfessionalServices_PromoCustomReports');
        $config->setSubcategoryId('ProfessionalServices_PromoManage');
        $config->setName('ProfessionalServices_PromoCustomReportsManage');
        $config->setIsNotWidgetizable();

        $promoWidgetApplicable = StaticContainer::get('Piwik\Plugins\ProfessionalServices\PromoWidgetApplicable');

        $isEnabled = $promoWidgetApplicable->check('CustomReports');
        $isEnabled = false;
        $config->setIsEnabled($isEnabled);
    }

    public function render()
    {
        return 'content';
    }
}
