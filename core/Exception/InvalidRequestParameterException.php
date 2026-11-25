<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Exception;

/**
 * Exception for a tracking request with an invalid parameter value. The tracker answers such a request with HTTP 400
 * instead of 500 and does not log it as an error; in controllers and API methods use {@link \Piwik\Http\BadRequestException}.
 *
 * @api
 */
class InvalidRequestParameterException extends Exception
{
    /**
     * @internal
     */
    public function __toString()
    {
        return $this->getMessage() . ' ' . $this->getFile() . ':' . $this->getLine();
    }
}
