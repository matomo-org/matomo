<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\TrackingSpamPrevention\Settings;

use Piwik\Plugins\TrackingSpamPrevention\Configuration;
use Piwik\Settings\Plugin\SystemSetting;

/**
 * Shows the default organisation block list read-only while it is the list in use. The list itself
 * lives in {@see Configuration::DEFAULT_GEOIP_MATCH_PROVIDERS} and is never stored, so that adding a
 * provider to the constant reaches every install that follows the default list.
 */
class DefaultOrganisationListSetting extends SystemSetting
{
    public function getValue()
    {
        return Configuration::DEFAULT_GEOIP_MATCH_PROVIDERS;
    }

    public function setValue($value)
    {
        // the settings form posts every field, disabled ones included, so without this the list
        // would be persisted once and then stop tracking the constant
    }
}
