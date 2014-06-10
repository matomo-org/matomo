<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\ExampleCommand\Commands;

use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 */
class HelloWorld extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('examplecommand:helloworld');
        $this->setDescription('ExampleCommand');
        $this->addOption('name', null, InputOption::VALUE_REQUIRED, 'Your name:');
    }

    /**
     * Execute command like: ./console examplecommand:helloworld --name="The Piwik Team"
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name    = $input->getOption('name');

        $message = sprintf('<info>HelloWorld: %s</info>', $name);

        $output->writeln($message);
    }
}
