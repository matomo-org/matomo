<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Log\Processor;

use Piwik\Plugin;

/**
 * Records the name of the class that logged.
 */
class ClassNameProcessor
{
    private $skippedClasses = array(
        __CLASS__,
        'Piwik\Log',
        'Piwik\Piwik',
        'Piwik\CronArchive',
        'Monolog\Logger',
    );

    public function __invoke(array $record)
    {
        $record['extra']['class'] = $this->getLoggingClassName();

        return $record;
    }

    /**
     * Returns the name of the plugin/class that triggered the log.
     *
     * @return string
     */
    private function getLoggingClassName()
    {
        $backtrace = $this->getBacktrace();

        $name = Plugin::getPluginNameFromBacktrace($backtrace);

        // if we can't determine the plugin, use the name of the calling class
        if ($name == false) {
            $name = $this->getClassNameThatIsLogging($backtrace);
        }

        return $name;
    }

    private function getClassNameThatIsLogging($backtrace)
    {
        foreach ($backtrace as $line) {
            if (isset($line['class']) && !in_array($line['class'], $this->skippedClasses)) {
                return $line['class'];
            }
        }

        return '';
    }

    private function getBacktrace()
    {
        if (version_compare(phpversion(), '5.3.6', '>=')) {
            return debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS | DEBUG_BACKTRACE_PROVIDE_OBJECT);
        }

        return debug_backtrace();
    }
}
