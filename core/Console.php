<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;

use Piwik\Application\Environment;
use Piwik\Config\ConfigNotFoundException;
use Piwik\Container\StaticContainer;
use Piwik\Plugin\Manager as PluginManager;
use Symfony\Bridge\Monolog\Handler\ConsoleHandler;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Console extends Application
{
    /**
     * @var Environment
     */
    private $environment;

    public function __construct(Environment $environment = null)
    {
        $this->setServerArgsIfPhpCgi();

        parent::__construct();

        $this->environment = $environment;

        $option = new InputOption('piwik-domain',
            null,
            InputOption::VALUE_OPTIONAL,
            'Piwik URL (protocol and domain) eg. "http://piwik.example.org"'
        );

        $this->getDefinition()->addOption($option);

        $option = new InputOption('xhprof',
            null,
            InputOption::VALUE_NONE,
            'Enable profiling with XHProf'
        );

        $this->getDefinition()->addOption($option);
    }

    public function doRun(InputInterface $input, OutputInterface $output)
    {
        if ($input->hasParameterOption('--xhprof')) {
            Profiler::setupProfilerXHProf(true, true);
        }

        $this->initPiwikHost($input);
        $this->initEnvironment($output);
        $this->initLoggerOutput($output);

        try {
            self::initPlugins();
        } catch (ConfigNotFoundException $e) {
            // Piwik not installed yet, no config file?
            Log::warning($e->getMessage());
        }

        $commands = $this->getAvailableCommands();

        foreach ($commands as $command) {
            $this->addCommandIfExists($command);
        }

        $self = $this;
        return Access::doAsSuperUser(function () use ($input, $output, $self) {
            return call_user_func(array($self, 'Symfony\Component\Console\Application::doRun'), $input, $output);
        });
    }

    private function addCommandIfExists($command)
    {
        if (!class_exists($command)) {
            Log::warning(sprintf('Cannot add command %s, class does not exist', $command));
        } elseif (!is_subclass_of($command, 'Piwik\Plugin\ConsoleCommand')) {
            Log::warning(sprintf('Cannot add command %s, class does not extend Piwik\Plugin\ConsoleCommand', $command));
        } else {
            /** @var Command $commandInstance */
            $commandInstance = new $command;

            // do not add the command if it already exists; this way we can add the command ourselves in tests
            if (!$this->has($commandInstance->getName())) {
                $this->add($commandInstance);
            }
        }
    }

    /**
     * Returns a list of available command classnames.
     *
     * @return string[]
     */
    private function getAvailableCommands()
    {
        $commands = $this->getDefaultPiwikCommands();
        $detected = PluginManager::getInstance()->findMultipleComponents('Commands', 'Piwik\\Plugin\\ConsoleCommand');

        $commands = array_merge($commands, $detected);

        /**
         * Triggered to filter / restrict console commands. Plugins that want to restrict commands
         * should subscribe to this event and remove commands from the existing list.
         *
         * **Example**
         *
         *     public function filterConsoleCommands(&$commands)
         *     {
         *         $key = array_search('Piwik\Plugins\MyPlugin\Commands\MyCommand', $commands);
         *         if (false !== $key) {
         *             unset($commands[$key]);
         *         }
         *     }
         *
         * @param array &$commands An array containing a list of command class names.
         */
        Piwik::postEvent('Console.filterCommands', array(&$commands));

        $commands = array_values(array_unique($commands));

        return $commands;
    }

    private function setServerArgsIfPhpCgi()
    {
        if (Common::isPhpCgiType()) {
            $_SERVER['argv'] = array();
            foreach ($_GET as $name => $value) {
                $argument = $name;
                if (!empty($value)) {
                    $argument .= '=' . $value;
                }

                $_SERVER['argv'][] = $argument;
            }

            if (!defined('STDIN')) {
                define('STDIN', fopen('php://stdin', 'r'));
            }
        }
    }

    public static function isSupported()
    {
        return Common::isPhpCliMode() && !Common::isPhpCgiType();
    }

    protected function initPiwikHost(InputInterface $input)
    {
        $piwikHostname = $input->getParameterOption('--piwik-domain');

        if (empty($piwikHostname)) {
            $piwikHostname = $input->getParameterOption('--url');
        }

        $piwikHostname = UrlHelper::getHostFromUrl($piwikHostname);
        Url::setHost($piwikHostname);
    }

    protected function initEnvironment(OutputInterface $output)
    {
        try {
            if ($this->environment === null) {
                $this->environment = new Environment('cli');
                $this->environment->init();
            }

            $config = Config::getInstance();
            return $config;
        } catch (\Exception $e) {
            $output->writeln($e->getMessage() . "\n");
        }
    }

    /**
     * Register the console output into the logger.
     *
     * Ideally, this should be done automatically with events:
     * @see http://symfony.com/fr/doc/current/components/console/events.html
     * @see Symfony\Bridge\Monolog\Handler\ConsoleHandler::onCommand()
     * But it would require to install Symfony's Event Dispatcher.
     */
    private function initLoggerOutput(OutputInterface $output)
    {
        /** @var ConsoleHandler $consoleLogHandler */
        $consoleLogHandler = StaticContainer::get('Symfony\Bridge\Monolog\Handler\ConsoleHandler');
        $consoleLogHandler->setOutput($output);
    }

    public static function initPlugins()
    {
        Plugin\Manager::getInstance()->loadActivatedPlugins();
        Plugin\Manager::getInstance()->loadPluginTranslations();
    }

    private function getDefaultPiwikCommands()
    {
        $commands = array(
            'Piwik\CliMulti\RequestCommand'
        );

        if (class_exists('Piwik\Plugins\EnterpriseAdmin\EnterpriseAdmin')) {
            $extra = new \Piwik\Plugins\EnterpriseAdmin\EnterpriseAdmin();
            $extra->addConsoleCommands($commands);
        }

        return $commands;
    }
}
