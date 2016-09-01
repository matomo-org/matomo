<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\ProfessionalServices;

use Piwik\Plugin;
use Piwik\Config;

/**
 * Advertising for providers of Professional Support for Piwik.
 *
 * Lets you for example check whether advertising is enabled, generate links for different landing pages etc.
 *
 * @since 2.16.0
 */
class Advertising
{
    const CAMPAIGN_NAME_PROFESSIONAL_SERVICES = 'App_ProfessionalServices';

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
     * Returns true if it is ok to show some advertising in the Piwik UI.
     * @return bool
     */
    public function areAdsForProfessionalServicesEnabled()
    {
        return $this->isAdsEnabledInConfig($this->config->General);
    }

    /**
     * Get URL for promoting Professional Services for Piwik
     *
     * @param string $campaignMedium
     * @param string $campaignContent
     * @return string
     */
    public function getPromoUrlForProfessionalServices($campaignMedium, $campaignContent = '')
    {
        $url = 'https://piwik.org/consulting/?';

        $campaign = $this->getCampaignParametersForPromoUrl(
            $name = self::CAMPAIGN_NAME_PROFESSIONAL_SERVICES,
            $campaignMedium,
            $campaignContent
        );

        return $url . $campaign;
    }

    /**
     * Get URL for letting people know about upgrade to On premises
     *
     * @return string
     */
    public function getPromoUrlForPiwikProUpgrade()
    {
        return 'https://piwik.org/recommends/piwik-pro-from-app';
    }

    /**
     * Appends campaign parameters to the given URL for promoting any Professional Support for Piwik service.
     *
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
     * Generates campaign URL parameters that can be used with promoting Professional Support service.
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

    /**
     * @param $configGeneralSection
     * @return bool
     */
    public static function isAdsEnabledInConfig($configGeneralSection)
    {
        $oldSettingValue = @$configGeneralSection['piwik_pro_ads_enabled'];
        $newSettingValue = @$configGeneralSection['piwik_professional_support_ads_enabled'];
        return (bool) ($newSettingValue || $oldSettingValue);
    }
}
