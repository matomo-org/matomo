<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Http\OAuth;

/**
 * Thrown when no OAuth 2.0 access token could be obtained.
 *
 * @internal
 */
class TokenRequestFailedException extends \RuntimeException
{
    private string $detail;

    /**
     * @param string $detail why the request failed. It is kept out of the message, which can be shown to visitors
     *                       who are not logged in, for example on the password reset form.
     */
    public function __construct(string $message, string $detail = '')
    {
        parent::__construct($message);

        $this->detail = $detail;
    }

    public function getDetail(): string
    {
        return $this->detail;
    }
}
