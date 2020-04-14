<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik;

use Piwik\Exception\InvalidRequestParameterException;

/**
 * Exception thrown when a user doesn't have sufficient access to a resource.
 *
 * @api
 */
class NoAccessException extends InvalidRequestParameterException
{
}