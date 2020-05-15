<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Exception;

/**
 * Exception thrown when the requested plugin requires internet connection, but internet features are disabled in the config file
 */
class PluginRequiresInternetException extends \Exception
{
    public function __construct($module)
    {
        parent::__construct("The plugin $module requires internet connection. Please check your config value for `enable_internet_features` if you want to use this feature.");
    }
}
