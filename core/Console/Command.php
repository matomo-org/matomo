<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Console;

use Symfony\Component\Console\Command\Command as SymfonyCommand;

/**
 * Alternate command class that allows lazy-loading by having the configuration in a static method.
 */
abstract class Command extends SymfonyCommand
{
    public static function configuration(CommandConfiguration $configuration)
    {
    }

    protected function configure()
    {
        $configuration = new CommandConfiguration();

        static::configuration($configuration);

        $configuration->apply($this);
    }
}
