<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TrackingSpamPrevention;

use Piwik\Config;
use Piwik\Container\StaticContainer;

class Configuration
{
    public const DEFAULT_RANGE_THROW_EXCEPTION = 0;

    /** @deprecated since 5.1.0, the allow list was moved to the `ip_allow_list` system setting */
    public const DEFAULT_RANGE_ALLOW_LIST = [''];
    public const DEFAULT_GEOIP_MATCH_PROVIDERS = [
        'alicloud',
        'alibaba cloud',
        'kwaifong group limited',
        'contabo',
        'digitalocean',
        'digital ocean',
        'leaseweb',
        'hostroyale technologies',
        'ucloud information',
        'secure internet llc',
        'inios oy',
        'fnk llc',
        'dedipath-llc',
        'mirholding b.v.',
        'datacamp limited',
        'voxility llp',
        'lucidacloud',
        'cloud computing corporation',
        'gigabit hosting',
        'hetzner online',
        'm247 europe srl',
        'incognet llc',
        'ponynet',
        'as40676',
        'nforce entertainment b.v.',
        'trabia srl',
        'latitude-sh',
        'dataforest gmbh',
        '31173 services ab',
        'advin services llc',
        'wholesale services provider',
        'netminders',
        'altushost b.v.',
        'amazon-02',
        'akamai connected cloud',
        'uab cherry servers',
        'datema bilisim ticaret anonim sirketi',
        'alibaba us technology co., ltd.',
        'netcup gmbh',
        'jinx co., limited',
        'fd-298-8796',
        'cloudsingularity',
        'weserve b.v.',
        'amarutu technology ltd',
        'limestonenetworks',
        'versija sia',
        'oy crea nova hosting solution ltd',
        'owl limited',
        'tzulo',
        'serverastra kft',
        'ionos se',
        'as-globaltelehost',
        'estnoc oy',
        'as-colocrossing',
        'greenhost bv',
        'keminet shpk',
        'worldstream b.v.',
        'aeza international ltd',
        'asline limited',
        'packethub s.a.',
        'gorillaservers',
        'as-vultr',
        'nl-811-40021',
        'cheapy-host',
        'jsc selectel',
        'gb network solutions sdn. bhd.',
        'latitude.sh',
        'limestone networks, inc',
        'scaleway',
        'm247 europe',
        'tencent',
        'fdcservers',
        'ace data centers',
        'egihosting',
        'hangzhou alibaba advertising',
        'sharktech',
        'dmit cloud services',
    ];

    public const KEY_RANGE_THROW_EXCEPTION = 'block_cloud_sync_throw_exception_on_error';

    /** @deprecated since 5.1.0, the allow list was moved to the `ip_allow_list` system setting */
    public const KEY_RANGE_ALLOW_LIST = 'iprange_allowlist';

    /** @deprecated since 5.2.0, the list was moved to the `organisation_block_list` system setting */
    public const KEY_GEOIP_MATCH_PROVIDERS = 'block_geoip_organisations';

    public function install()
    {
        $config = $this->getConfig();

        $default = $config->TrackingSpamPrevention;
        if (empty($default)) {
            $default = array();
        }

        if (empty($default[self::KEY_RANGE_THROW_EXCEPTION])) {
            $default[self::KEY_RANGE_THROW_EXCEPTION] = self::DEFAULT_RANGE_THROW_EXCEPTION;
        }

        $config->TrackingSpamPrevention = $default;

        $config->forceSave();
    }

    public function uninstall()
    {
        $config = $this->getConfig();
        $config->TrackingSpamPrevention = array();
        $config->forceSave();
    }

    /**
     * @return bool
     */
    public function shouldThrowExceptionOnIpRangeSync()
    {
        $value = $this->getConfigValue(self::KEY_RANGE_THROW_EXCEPTION, self::DEFAULT_RANGE_THROW_EXCEPTION);

        if ($value === false || $value === '' || $value === null) {
            $value = self::KEY_RANGE_THROW_EXCEPTION;
        }

        return (bool) $value;
    }

    /**
     * @deprecated since 5.1.0, use SystemSettings::getAllowedIpRanges() instead. The allow list was moved from the
     *             config file to the `ip_allow_list` system setting.
     * @return array
     */
    public function getIpRangesAlwaysAllowed()
    {
        return StaticContainer::get(SystemSettings::class)->getAllowedIpRanges();
    }

    private function getConfig()
    {
        return Config::getInstance();
    }

    private function getConfigValue($name, $default)
    {
        $config = $this->getConfig();
        $attribution = $config->TrackingSpamPrevention;
        if (isset($attribution[$name])) {
            return $attribution[$name];
        }
        return $default;
    }
}
