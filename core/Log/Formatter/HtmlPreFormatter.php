<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Log\Formatter;

use Piwik\Common;
use Piwik\Log;

/**
 * Formats the message into `<pre></pre>` HTML tags.
 */
class HtmlPreFormatter extends Formatter
{
    public function format($message, $level, $tag, $datetime, Log $logger)
    {
        $message = $this->next($message, $level, $tag, $datetime, $logger);

        if (! is_string($message)) {
            return $message;
        }

        if (!Common::isPhpCliMode()) {
            $message = Common::sanitizeInputValue($message);
            $message = '<pre>' . $message . '</pre>';
        }

        return $message;
    }
}
