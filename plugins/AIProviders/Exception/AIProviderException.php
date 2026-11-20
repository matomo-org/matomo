<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIProviders\Exception;

use RuntimeException;

/**
 * Base exception for AI provider failures.
 *
 * Extends RuntimeException so existing callers that catch RuntimeException
 * keep working. Use the subclasses to decide whether a failure is worth
 * retrying: {@link AIProviderClientException} is permanent (configuration,
 * authentication, invalid request), {@link AIProviderServerException} is
 * transient (provider-side errors, throttling). Failures that fit neither
 * bucket — transport errors, malformed provider responses — throw this base
 * class directly.
 */
class AIProviderException extends RuntimeException
{
}
