<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik;

use Piwik\Http\HttpCodeException;

/**
 * Exception thrown when a user doesn't have sufficient access to a resource.
 *
 * @api
 */
class NoAccessException extends HttpCodeException
{
    private $isHtmlMessage = false;

    public function __construct($message)
    {
        parent::__construct($message, $code = 401);
    }

    public function setIsHtmlMessage()
    {
        $this->isHtmlMessage = true;
    }

    public function isHtmlMessage()
    {
        return $this->isHtmlMessage;
    }

    public function __toString()
    {
        return $this->getMessage() . ' ' . $this->getFile() . ':' . $this->getLine();
    }
}
