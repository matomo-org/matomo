<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Exception;

class NotSupportedBrowserException extends \Piwik\Exception\Exception
{
    public function __construct($message)
    {
        // Use HTTP code 400 to avoid server-provided 403 error page.
        parent::__construct($message, $code = 400);
    }
}
