<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Exception;

use Exception as PhpException;

/**
 * An exception whose message has HTML content. When these exceptions are caught
 * the message will not be sanitized before being displayed to the user.
 */
abstract class Exception extends PhpException
{
    private $isHtmlMessage = false;

    public function setIsHtmlMessage()
    {
        $this->isHtmlMessage = true;
    }

    public function isHtmlMessage()
    {
        return $this->isHtmlMessage;
    }
}
