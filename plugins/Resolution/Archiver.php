<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Resolution;

/**
 * Archiver for Resolution Plugin
 *
 * @see PluginsArchiver
 */
class Archiver extends \Piwik\Plugin\Archiver
{
    const RESOLUTION_RECORD_NAME = 'Resolution_resolution';
    const CONFIGURATION_RECORD_NAME = 'Resolution_configuration';
    const RESOLUTION_DIMENSION = "log_visit.config_resolution";
    const CONFIGURATION_DIMENSION = "CONCAT(log_visit.config_os, ';', log_visit.config_browser_name, ';', log_visit.config_resolution)";
}
