<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Monolog\Formatter;

use Symfony\Bridge\Monolog\Formatter\ConsoleFormatter as SymfonyConsoleFormatter;

class ConsoleFormatter extends SymfonyConsoleFormatter
{
    public function format(array $record)
    {
        $formatted = parent::format($record);

        foreach ($record['extra'] as $var => $val) {
            if (false !== strpos($formatted, '%extra.' . $var . '%')) {
                $formatted = str_replace('%extra.' . $var . '%', $val, $formatted);
                unset($record['extra'][$var]);
            }
        }

        return $formatted;
    }
}
