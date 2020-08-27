<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Exception;

/**
 * Exception thrown when the requested plugin is not activated in the config file
 */
class PluginDeactivatedException extends \Exception
{
    public function __construct($module)
    {
        parent::__construct("The plugin $module is not enabled. You can activate the plugin on Settings > Plugins page in Matomo.");
    }
}
