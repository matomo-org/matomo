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
    public const BROWSER_SEPARATOR = ';';
    public const DEVICE_TYPE_RECORD_NAME = 'DevicesDetection_types';
    public const DEVICE_BRAND_RECORD_NAME = 'DevicesDetection_brands';
    public const DEVICE_MODEL_RECORD_NAME = 'DevicesDetection_models';
    public const OS_RECORD_NAME = 'DevicesDetection_os';
    public const OS_VERSION_RECORD_NAME = 'DevicesDetection_osVersions';
    public const BROWSER_RECORD_NAME = 'DevicesDetection_browsers';
    public const BROWSER_ENGINE_RECORD_NAME = 'DevicesDetection_browserEngines';
    public const BROWSER_VERSION_RECORD_NAME = 'DevicesDetection_browserVersions';

    public const DEVICE_TYPE_FIELD = "config_device_type";
    public const DEVICE_BRAND_FIELD = "config_device_brand";
    public const DEVICE_MODEL_FIELD = "CONCAT(log_visit.config_device_brand, ';', log_visit.config_device_model)";
    public const OS_FIELD = "config_os";
    public const OS_VERSION_FIELD = "CONCAT(log_visit.config_os, ';', COALESCE(log_visit.config_os_version, ''))";
    public const BROWSER_FIELD = "config_browser_name";
    public const BROWSER_ENGINE_FIELD = "config_browser_engine";
    public const BROWSER_VERSION_DIMENSION = "CONCAT(log_visit.config_browser_name, ';', log_visit.config_browser_version)";
}
