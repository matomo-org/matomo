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
 * Permanent failure caused by the request or the configuration: missing or
 * rejected credentials, an unknown or incapable provider, a malformed
 * request, or a provider 4xx response. Retrying the same request will fail
 * again; the caller should surface the error instead.
 */
class AIProviderClientException extends AIProviderException
{
}
