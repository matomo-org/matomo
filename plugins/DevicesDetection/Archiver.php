<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DevicesDetection;

class Archiver extends \Piwik\Plugin\Archiver
{
    const BROWSER_SEPARATOR = ';';
    const DEVICE_TYPE_RECORD_NAME = 'DevicesDetection_types';
    const DEVICE_BRAND_RECORD_NAME = 'DevicesDetection_brands';
    const DEVICE_MODEL_RECORD_NAME = 'DevicesDetection_models';
    const OS_RECORD_NAME = 'DevicesDetection_os';
    const OS_VERSION_RECORD_NAME = 'DevicesDetection_osVersions';
    const BROWSER_RECORD_NAME = 'DevicesDetection_browsers';
    const BROWSER_ENGINE_RECORD_NAME = 'DevicesDetection_browserEngines';
    const BROWSER_VERSION_RECORD_NAME = 'DevicesDetection_browserVersions';

    const DEVICE_TYPE_FIELD = "config_device_type";
    const DEVICE_BRAND_FIELD = "config_device_brand";
    const DEVICE_MODEL_FIELD = "CONCAT(log_visit.config_device_brand, ';', log_visit.config_device_model)";
    const OS_FIELD = "config_os";
    const OS_VERSION_FIELD = "CONCAT(log_visit.config_os, ';', COALESCE(log_visit.config_os_version, ''))";
    const BROWSER_FIELD = "config_browser_name";
    const BROWSER_ENGINE_FIELD = "config_browser_engine";
    const BROWSER_VERSION_DIMENSION = "CONCAT(log_visit.config_browser_name, ';', log_visit.config_browser_version)";
}
