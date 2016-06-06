<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Updates;

use Piwik\Config;
use Piwik\Plugins\CoreAdminHome\CustomLogo;
use Piwik\Plugins\PrivacyManager\Config as PrivacyManagerConfig;
use Piwik\UpdaterErrorException;
use Piwik\Updates;
use Piwik\Updater;

/**
 */
class Updates_2_0_4_b8 extends Updates
{
    public function getMigrationQueries(Updater $updater)
    {
        return array();
    }

    public function doUpdate(Updater $updater)
    {
        try {
            $config = Config::getInstance();

            self::migrateBrandingConfig($config);
            self::migratePrivacyManagerConfig($config, new PrivacyManagerConfig());

            $config->forceSave();
        } catch (\Exception $e) {
            throw new UpdaterErrorException($e->getMessage());
        }
    }

    private static function migrateBrandingConfig(Config $config)
    {
        $useCustomLogo = self::getValueAndDelete($config, 'branding', 'use_custom_logo');

        $customLogo = new CustomLogo();
        $useCustomLogo ? $customLogo->enable() : $customLogo->disable();
    }

    private static function migratePrivacyManagerConfig(Config $oldConfig, PrivacyManagerConfig $newConfig)
    {
        $ipVisitEnrichment   = self::getValueAndDelete($oldConfig, 'Tracker', 'use_anonymized_ip_for_visit_enrichment');
        $ipAddressMarkLength = self::getValueAndDelete($oldConfig, 'Tracker', 'ip_address_mask_length');

        if (null !== $ipVisitEnrichment) {
            $newConfig->useAnonymizedIpForVisitEnrichment = $ipVisitEnrichment;
        }
        if (null !== $ipAddressMarkLength) {
            $newConfig->ipAddressMaskLength = $ipAddressMarkLength;
        }
    }

    private static function getValueAndDelete(Config $config, $section, $key)
    {
        if (!$config->$section || !array_key_exists($key, $config->$section)) {
            return null;
        }

        $values = $config->$section;
        $value  = $values[$key];
        unset($values[$key]);

        $config->$section = $values;

        return $value;
    }
}
