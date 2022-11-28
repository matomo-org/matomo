<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Exception;

use Piwik\Http\HttpCodeException;

class InvalidRequestParameterException extends Exception implements HttpCodeException
{
    public function __toString()
    {
        return $this->getMessage() . ' ' . $this->getFile() . ':' . $this->getLine();
    }
}
