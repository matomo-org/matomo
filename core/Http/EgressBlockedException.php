<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Http;

use Exception;

/**
 * Thrown when the SSRF-safe fetch path in {@see \Piwik\Http} refuses a request: a non-public target,
 * or an unmet precondition (curl, an http(s) scheme, no forward proxy). Distinct from a transient
 * network failure, so callers can surface it to the admin, who can act on it.
 */
class EgressBlockedException extends Exception
{
}
