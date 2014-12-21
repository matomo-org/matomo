<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\DevicePlugins;

use Piwik\Piwik;

function getPluginsLogo($label)
{
    if ($label == Piwik::translate('General_Others')) {
        return false;
    }
    return 'plugins/DevicePlugins/images/plugins/' . $label . '.gif';
}
