<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Mail\OAuth;

/**
 * Thrown when no OAuth 2.0 access token could be obtained for sending emails.
 */
class TokenRequestFailedException extends \RuntimeException
{
}
