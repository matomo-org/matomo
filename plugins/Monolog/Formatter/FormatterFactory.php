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
            return self::getFormatter($container, $textFormatterEntry, $format);
        }

        /** @var array<string, string> $classes */
        $classes = $container->get('log.formatter.classes');

        if (!isset($classes[$format])) {
            throw new \Exception(sprintf(
                'Invalid log format "%s" configured for the "%s" log writer. Supported formats are: %s',
                $format,
                $writerName,
                implode(', ', array_merge([self::FORMAT_TEXT], array_keys($classes)))
            ));
        }

        return self::getFormatter($container, $classes[$format], $format);
    }

    private static function getFormatter(Container $container, string $entry, string $format): FormatterInterface
    {
        $formatter = $container->get($entry);

        if (!$formatter instanceof FormatterInterface) {
            throw new \Exception(sprintf('The log format "%s" does not resolve to a %s.', $format, FormatterInterface::class));
        }

        return $formatter;
    }

    private static function getConfiguredFormat(Container $container, string $writerName): string
    {
        foreach (['ini.log.log_format_' . $writerName, 'ini.log.log_format'] as $entry) {
            $value = $container->has($entry) ? $container->get($entry) : null;

            // a value that is not a string, e.g. from `log_format[] =`, is reported as invalid format by its type
            $format = is_string($value) ? strtolower(trim($value)) : ($value === null ? '' : get_debug_type($value));

            if ($format !== '') {
                return $format;
            }
        }

        return self::FORMAT_TEXT;
    }
}
