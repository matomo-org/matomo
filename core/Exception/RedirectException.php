<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Exception;

class RedirectException extends \Piwik\Exception\Exception implements IRedirectException
{
    private $redirectTo;
    private $countdown;

    /**
     * @param $message
     * @param string $redirectTo
     * @param int $countdown
     */
    public function __construct($message, $redirectTo, $countdown=5)
    {
        $this->redirectTo = $redirectTo;
        $this->countdown = $countdown;
        $this->message = $message;
    }

    public function getRedirectionUrl(): string
    {
        return $this->redirectTo;
    }


    public function getCountdown(): int
    {
        return $this->countdown;
    }
}