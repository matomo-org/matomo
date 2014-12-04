<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Log\Formatter;

use Piwik\Common;

/**
 * Formats the message into `<pre></pre>` HTML tags.
 */
class HtmlPreFormatter extends Formatter
{
    public function format(array $record)
    {
        $record = $this->next($record);

        if (! is_string($record['message'])) {
            return $record;
        }

        if (!Common::isPhpCliMode()) {
            $record['message'] = '<pre>' . Common::sanitizeInputValue($record['message']) . '</pre>';
        }

        return $record;
    }
}
