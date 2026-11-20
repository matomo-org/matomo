<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Exception;

use Piwik\Common;
use Piwik\Http\HttpCodeException;
use Piwik\Url;
use Piwik\UrlHelper;

class RedirectException extends \Piwik\Exception\Exception implements IRedirectException, HttpCodeException
{
    private $redirectTo;
    private $countdown;

    /**
     * @param $message
     * @param string $redirectTo
     * @param int $countdown
     * @param int $code the HTTP status code of the page that redirects
     */
    public function __construct($message, $redirectTo, $countdown = 5, $code = 400)
    {
        $this->redirectTo = $redirectTo;
        $this->countdown = $countdown;
        $this->message = $message;
        $this->code = $code;
    }

    public function getRedirectionUrl(): string
    {
        return $this->isValidRedirect() ? Common::sanitizeInputValue($this->redirectTo) : '';
    }


    public function getCountdown(): int
    {
        return $this->countdown;
    }

    private function isValidRedirect(): bool
    {
        $host = Url::getHostFromUrl($this->redirectTo);
        return $host !== null && UrlHelper::isLookLikeUrl($this->redirectTo) && Url::isValidHost($host);
    }
}
