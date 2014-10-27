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
 * TODO
 */
class HtmlMessageException extends Exception
{
    /**
     * TODO
     */
    public function getHtmlMessage()
    {
        return $this->getMessage();
    }
}