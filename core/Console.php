<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputOption;

class Console
{
    public function init()
    {
        $this->initPiwikHost();
        $this->initConfig();
        $this->initPlugins();
    }

    public function run()
    {
        $console = new Application();

        $option = new InputOption('piwik-domain',
            null,
            InputOption::VALUE_OPTIONAL,
            'Piwik URL (protocol and domain) eg. "http://piwik.example.org"'
        );

        $console->getDefinition()->addOption($option);

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
        $commands = $this->getDefaultCommands();

        /**
         * Triggered to gather all available console commands. Plugins that want to expose new console commands
         * should subscribe to this event and add commands to the incoming array.
         *
         * **Example**
         *
         *     public function addConsoleCommands(&$commands)
         *     {
         *         $commands[] = 'Piwik\Plugins\MyPlugin\Commands\MyCommand';
         *     }
         *
         * @param array &$commands An array containing a list of command class names.
         */
        Piwik::postEvent('Console.addCommands', array(&$commands));

        return $commands;
    }

    protected function initPiwikHost()
    {
        $piwikHostname = CronArchive::getParameterFromCli('piwik-domain', true);
        $piwikHostname = UrlHelper::getHostFromUrl($piwikHostname);
        Url::setHost($piwikHostname);
    }

    protected function initConfig()
    {
        $config = Config::getInstance();
        try {
            $config->checkLocalConfigFound();
            return $config;
        } catch (\Exception $e) {
            echo ($e->getMessage() . "\n\n");
        }
    }

    protected function initPlugins()
    {
        $pluginsToLoad = Config::getInstance()->Plugins['Plugins'];
        $pluginsManager = Plugin\Manager::getInstance();
        $pluginsManager->loadPlugins($pluginsToLoad);
    }

    private function getDefaultCommands()
    {
        $commands = array(
            'Piwik\CliMulti\RequestCommand'
        );

        if (class_exists('Piwik\Plugins\CloudAdmin\CloudAdmin')) {
            $extra = new \Piwik\Plugins\CloudAdmin\CloudAdmin();
            $extra->addConsoleCommands($commands);
        }
        return $commands;
    }
}
