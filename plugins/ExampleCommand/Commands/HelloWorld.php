<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExampleCommand\Commands;

use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This class lets you define a new command. To read more about commands have a look at our Piwik Console guide on
 * http://developer.piwik.org/guides/piwik-on-the-command-line
 *
 * As Piwik Console is based on the Symfony Console you might also want to have a look at
 * http://symfony.com/doc/current/components/console/index.html
 */
class HelloWorld extends ConsoleCommand
{
    /**
     * This methods allows you to configure your command. Here you can define the name and description of your command
     * as well as all options and arguments you expect when executing it.
     */
    protected function configure()
    {
        $this->setName('examplecommand:helloworld');
        $this->setDescription('ExampleCommandDescription');
        $this->addOption('name', null, InputOption::VALUE_REQUIRED, 'Your name:');
    }

    /**
     * The actual task is defined in this method. Here you can access any option or argument that was defined on the
     * command line via $input and write anything to the console via $output argument.
     * In case anything went wrong during the execution you should throw an exception to make sure the user will get a
     * useful error message and to make sure the command does not exit with the status code 0.
     *
     * Ideally, the actual command is quite short as it acts like a controller. It should only receive the input values,
     * execute the task by calling a method of another class and output any useful information.
     *
     * Execute the command like: ./console examplecommand:helloworld --name="The Piwik Team"
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getOption('name');

        $message = sprintf('<info>HelloWorld: %s</info>', $name);

        $output->writeln($message);
    }
}
