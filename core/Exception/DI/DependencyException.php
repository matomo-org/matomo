<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Exception\DI;

/**
 * Thrown when a container entry exists but cannot be created, e.g. because a dependency fails to resolve.
 *
 * @api
 */
class DependencyException extends \DI\DependencyException
{
}
