<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Monolog\Formatter;

use Monolog\Formatter\FormatterInterface;
use Piwik\Container\Container;

/**
 * Creates the formatter configured for a log writer via `[log] log_format_<writer>` or `[log] log_format`.
 */
class FormatterFactory
{
    public const FORMAT_TEXT = 'text';

    /**
     * @param string $textFormatterEntry DI entry of the line formatter to use for the `text` format
     */
    public static function createForWriter(Container $container, string $writerName, string $textFormatterEntry): FormatterInterface
    {
        $format = self::getConfiguredFormat($container, $writerName);

        if ($format === self::FORMAT_TEXT) {
            return $container->get($textFormatterEntry);
        }

        $classes = $container->get('log.formatter.classes');

        if (!isset($classes[$format])) {
            throw new \Exception(sprintf(
                'Invalid log format "%s" configured for the "%s" log writer. Supported formats are: %s',
                $format,
                $writerName,
                implode(', ', array_merge([self::FORMAT_TEXT], array_keys($classes)))
            ));
        }

        return $container->get($classes[$format]);
    }

    private static function getConfiguredFormat(Container $container, string $writerName): string
    {
        foreach (['ini.log.log_format_' . $writerName, 'ini.log.log_format'] as $entry) {
            $format = $container->has($entry) ? strtolower(trim((string) $container->get($entry))) : '';

            if ($format !== '') {
                return $format;
            }
        }

        return self::FORMAT_TEXT;
    }
}
