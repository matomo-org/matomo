<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Exception;

/**
 * ErrorException
 *
 * @author Matthieu Napoli <matthieu@mnapoli.fr>
 */
class ErrorException extends \ErrorException
{
    public function isHtmlMessage()
    {
        return true;
    }
}
