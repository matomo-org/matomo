<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Exception\DI;

/**
 * Thrown when no container entry is registered under the requested name.
 *
 * @api
 */
class NotFoundException extends \DI\NotFoundException
{
}
