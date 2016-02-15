<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\PiwikPro;

use Piwik\Plugin;
use Piwik\Config;

/**
 * Piwik PRO Advertising related methods. Lets you for example check whether advertising is enabled, generate
 * links for differnt landing pages etc.
 *
 * @api
 * @since 2.16.0
 */
class Advertising
{
    const CAMPAIGN_NAME_UPGRADE_TO_PRO = 'Upgrade_to_Pro';
    const CAMPAIGN_NAME_UPGRADE_TO_CLOUD = 'Upgrade_to_Cloud';

    /**
     * @var Plugin\Manager
     */
    private $pluginManager;

    /**
     * @var Config
     */
    private $config;

    public function __construct(Plugin\Manager $pluginManager, Config $config)
    {
        $this->pluginManager = $pluginManager;
        $this->config = $config;
    }

    /**
     * Returns true if it is ok to show some Piwik PRO advertising in the Piwik UI.
     * @return bool
     */
    public function arePiwikProAdsEnabled()
    {
        if ($this->pluginManager->isPluginActivated('EnterpriseAdmin')
            || $this->pluginManager->isPluginActivated('LoginAdmin')
            || $this->pluginManager->isPluginActivated('CloudAdmin')
            || $this->pluginManager->isPluginActivated('WhiteLabel')) {
            return false;
        }

        $showAds = $this->config->General['piwik_pro_ads_enabled'];

        return !empty($showAds);
    }

    /**
     * Get URL for promoting the Piwik Cloud.
     *
     * @param string $campaignMedium
     * @param string $campaignContent
     * @return string
     */
    public function getPromoUrlForCloud($campaignMedium, $campaignContent = '')
    {
        $url = 'https://piwik.pro/cloud/?';

        $campaign = $this->getCampaignParametersForPromoUrl(
            $name = self::CAMPAIGN_NAME_UPGRADE_TO_CLOUD,
            $campaignMedium,
            $campaignContent
        );

        return $url . $campaign;
    }

    /**
     * Get URL for promoting Piwik On Premises.
     * @param string $campaignMedium
     * @param string $campaignContent
     * @return string
     */
    public function getPromoUrlForOnPremises($campaignMedium, $campaignContent = '')
    {
        $url = 'https://piwik.pro/c/upgrade/?';

        $campaign = $this->getCampaignParametersForPromoUrl(
            $name = self::CAMPAIGN_NAME_UPGRADE_TO_PRO,
            $campaignMedium,
            $campaignContent
        );

        return $url . $campaign;
    }

    /**
     * Appends campaign parameters to the given URL for promoting any Piwik PRO service.
     * @param string $url
     * @param string $campaignName
     * @param string $campaignMedium
     * @param string $campaignContent
     * @return string
     */
    public function addPromoCampaignParametersToUrl($url, $campaignName, $campaignMedium, $campaignContent = '')
    {
        if (empty($url)) {
            return '';
        }

        if (strpos($url, '?') === false) {
            $url .= '?';
        } else {
            $url .= '&';
        }

        $url .= $this->getCampaignParametersForPromoUrl($campaignName, $campaignMedium, $campaignContent);

        return $url;
    }

    /**
     * Generates campaign URL parameters that can be used with any promotion link for Piwik PRO.
     *
     * @param string $campaignName
     * @param string $campaignMedium
     * @param string $campaignContent Optional
     * @return string URL parameters without a leading ? or &
     */
    private function getCampaignParametersForPromoUrl($campaignName, $campaignMedium, $campaignContent = '')
    {
        $campaignName = sprintf('pk_campaign=%s&pk_medium=%s&pk_source=Piwik_App', $campaignName, $campaignMedium);

        if (!empty($campaignContent)) {
            $campaignName .= '&pk_content=' . $campaignContent;
        }

        return $campaignName;
    }
}
