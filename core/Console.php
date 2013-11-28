<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */
namespace Piwik;

use Symfony\Component\Console\Application;

class Console
{
    public function run()
    {
        $console  = new Application();
        $commands = $this->getAvailableCommands();

        foreach ($commands as $command) {

            if (!class_exists($command)) {

                Log::warning(sprintf('Cannot add command %s, class does not exist', $command));

            } elseif (!is_subclass_of($command, 'Piwik\Plugin\ConsoleCommand')) {

                Log::warning(sprintf('Cannot add command %s, class does not extend Piwik\Plugin\ConsoleCommand', $command));

            } else {

                $console->add(new $command);
            }
        }

        $console->run();
    }

    /**
     * Returns a list of available command classnames.
     *
     * @return string[]
     */
    private function getAvailableCommands()
    {
        $commands = array();

        /**
         * Triggered when gathering all available console commands. Plugins that want to expose new console commands
         * should subscribe to this event and add commands to the incoming array.
         *
         * **Example**
         * ```
         * public function addConsoleCommands(&$commands)
         * {
         *     $commands[] = 'Piwik\Plugins\MyPlugin\Commands\MyCommand';
         * }
         * ```
         *
         * @param array &$commands An array containing a list of command classnames.
         */
        Piwik::postEvent('Console.addCommands', array(&$commands));

        return $commands;
    }
}
