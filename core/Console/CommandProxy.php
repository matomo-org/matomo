<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Console;

use Interop\Container\ContainerInterface;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Proxies console command to allow lazy-loading.
 */
class CommandProxy extends SymfonyCommand
{
    /**
     * Actual command class name.
     *
     * @var string
     */
    private $commandClass;

    /**
     * @var ContainerInterface
     */
    private $container;

    public function __construct($commandClass, ContainerInterface $container)
    {
        if (! class_exists($commandClass)) {
            throw new \InvalidArgumentException(sprintf('The class %s does not exist', $commandClass));
        }

        if (! is_subclass_of($commandClass, 'Piwik\Console\Command')) {
            throw new \InvalidArgumentException(sprintf('The class %s does not extend Piwik\Console\Command', $commandClass));
        }

        $this->commandClass = $commandClass;
        $this->container = $container;

        parent::__construct();
    }

    protected function configure()
    {
        $configuration = new CommandConfiguration();

        // Calls Piwik\Console\Command::configuration($configuration)
        call_user_func(array($this->commandClass, 'configuration'), $configuration);

        $configuration->apply($this);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $command = $this->container->get($this->commandClass);

        $command->execute($input, $output);
    }
}
