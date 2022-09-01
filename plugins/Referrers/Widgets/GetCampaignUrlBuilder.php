<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers\Widgets;

use Piwik\Common;
use Piwik\Piwik;
use Piwik\Plugin;
use Piwik\Widget\WidgetConfig;

class GetCampaignUrlBuilder extends \Piwik\Widget\Widget
{
    public static function configure(WidgetConfig $config)
    {
        $config->setCategoryId('Referrers_Referrers');
        $config->setSubcategoryId('Referrers_URLCampaignBuilder');
        $config->setName('Referrers_URLCampaignBuilder');

        $idSite = self::getIdSite();
        if (!Piwik::isUserHasViewAccess($idSite)) {
            $config->disable();
        }
    }

    private static function getIdSite()
    {
        return Common::getRequestVar('idSite', 0, 'int');
    }

    public function render()
    {
        $idSite = self::getIdSite();
        Piwik::checkUserHasViewAccess($idSite);

        $hasExtraPlugin = Plugin\Manager::getInstance()->isPluginActivated('MarketingCampaignsReporting');

        return $this->renderTemplate('campaignBuilder', array(
            'hasExtraPlugin' => $hasExtraPlugin,
        ));
    }

}
