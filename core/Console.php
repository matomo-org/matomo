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
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Console extends Application
{
    public function __construct()
    {
        parent::__construct();

        $option = new InputOption('piwik-domain',
            null,
            InputOption::VALUE_OPTIONAL,
            'Piwik URL (protocol and domain) eg. "http://piwik.example.org"'
        );

        $this->getDefinition()->addOption($option);
    }

    /**
     * @deprecated
     */
    public function init()
    {
        // TODO: remove
    }

    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->initPiwikHost($input);
        $this->initConfig();
        try {
            self::initPlugins();
        } catch(\Exception $e) {
            // Piwik not installed yet, no config file?
        }

        $commands = $this->getAvailableCommands();

        foreach ($commands as $command) {
            if (!class_exists($command)) {
                Log::warning(sprintf('Cannot add command %s, class does not exist', $command));
            } elseif (!is_subclass_of($command, 'Piwik\Plugin\ConsoleCommand')) {
                Log::warning(sprintf('Cannot add command %s, class does not extend Piwik\Plugin\ConsoleCommand', $command));
            } else {
                $this->add(new $command);
            }
        }

        return parent::doRun($input, $output);
    }

    /**
     * Returns a list of available command classnames.
     *
     * @return string[]
     */
    private function getAvailableCommands()
    {
        $commands = $this->getDefaultPiwikCommands();

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

    protected function initPiwikHost(InputInterface $input)
    {
        $piwikHostname = $input->getParameterOption('--piwik-domain');
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

    public static function initPlugins()
    {
        $pluginsToLoad = Config::getInstance()->Plugins['Plugins'];
        $pluginsManager = Plugin\Manager::getInstance();
        $pluginsManager->loadPlugins($pluginsToLoad);
    }

    private function getDefaultPiwikCommands()
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
