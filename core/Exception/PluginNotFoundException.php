<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Exception;

use Piwik\Http\HttpCodeException;

/**
 * Exception thrown when the requested plugin is not activated in the config file
 */
class PluginNotFoundException extends \Exception implements HttpCodeException
{
    public function __construct($module)
    {
        parent::__construct("The plugin $module was not found.", 404);
    }
}
