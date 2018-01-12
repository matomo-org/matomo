<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\Marketplace\Api\Service;

/**
 */
class Exception extends \Exception
{
    const HTTP_ERROR = 100;
    const API_ERROR  = 101;

}
