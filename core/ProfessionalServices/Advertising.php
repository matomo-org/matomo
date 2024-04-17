<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\ProfessionalServices;

use Piwik\Plugin;
use Piwik\Config;
use Piwik\Url;

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
        return self::isAdsEnabledInConfig($this->config->General);
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
        return Url::addCampaignParametersToMatomoLink(
            'https://matomo.org/support-plans/',
            self::CAMPAIGN_NAME_PROFESSIONAL_SERVICES,
            null,
            $campaignMedium
        );
    }

    /**
     * Appends campaign parameters to the given URL for promoting any Professional Support for Piwik service.
     *
     * @param string $url
     * @param string $campaignName
     * @param string $campaignMedium
     * @param string $campaignContent
     * @param string $campaignSource
     * @return string
     */
    public function addPromoCampaignParametersToUrl($url, $campaignName, $campaignMedium, $campaignContent = '', $campaignSource = null)
    {
        if (empty($url)) {
            return '';
        }

        return Url::addCampaignParametersToMatomoLink($url, $campaignName, $campaignSource, $campaignMedium .
            ($campaignContent !== '' ? '.' . $campaignContent : ''));
    }

    /**
     * @deprecated
     * Generates campaign URL parameters that can be used with promoting Professional Support service.
     *
     * @param string $campaignName
     * @param string $campaignMedium
     * @param string $campaignContent Optional
     * @return string URL parameters without a leading ? or &
     */
    private function getCampaignParametersForPromoUrl($campaignName, $campaignMedium, $campaignContent = '')
    {
        $campaignName = sprintf('pk_campaign=%s&pk_medium=%s&pk_source=Matomo_App', $campaignName, $campaignMedium);

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
        $oldSettingValue = false;
        if (isset($configGeneralSection['piwik_pro_ads_enabled'])) {
            $oldSettingValue = @$configGeneralSection['piwik_pro_ads_enabled'];
        }
        $newSettingValue = @$configGeneralSection['piwik_professional_support_ads_enabled'];
        return (bool) ($newSettingValue || $oldSettingValue);
    }
}
