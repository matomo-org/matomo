<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Exception;
/**
 * Interface for exceptions which have a countdown feature until it is redirected to a URL.
 */
interface IRedirectException
{

    public function getRedirectionUrl(): string;

    public function getCountdown(): int;
}
