<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Exceptions;

use Exception;

/**
 * An exception whose message has HTML content. When these exceptions are caught
 * the message will not be sanitized before being displayed to the user.
 *
 * @api
 */
class HtmlMessageException extends Exception
{
    /**
     * Returns the exception message.
     *
     * @return string
     */
    public function getHtmlMessage()
    {
        return $this->getMessage();
    }
}