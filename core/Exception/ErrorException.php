<?php

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
