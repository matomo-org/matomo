<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
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
    $icon = 'plugins/Morpheus/icons/dist/plugins/' . $label . '.png';

    if (file_exists(PIWIK_INCLUDE_PATH . '/' . $icon)) {
        return $icon;
    }

    // try to use column icon defined in Column class
    $columns = DevicePlugins::getAllPluginColumns();

    foreach ($columns as $column) {
        if (strtolower($label) == substr($column->getColumnName(), 7) && $column->columnIcon) {
            return $column->columnIcon;
        }
    }

    return false;
}
