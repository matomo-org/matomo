<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIProviders\Exception;

/**
 * Transient provider-side failure: a 5xx response, throttling, or exhausted
 * capacity. Thrown after the provider's automatic retries are used up, so an
 * immediate retry is unlikely to help, but a later retry of the same request
 * may succeed.
 */
class AIProviderServerException extends AIProviderException
{
}
