<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Log;

/**
 * Proxy interface for \Psr\Log\LoggerInterface.
 *
 * Get an instance via dependency injection or `StaticContainer::get(LoggerInterface::class)`.
 *
 * @see \Psr\Log\LoggerInterface
 * @api
 */
interface LoggerInterface extends \Psr\Log\LoggerInterface
{
}
