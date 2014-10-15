<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 */
class GenerateCommand extends GeneratePluginBase
{
    protected function configure()
    {
        $this->setName('generate:command')
            ->setDescription('Adds a command to an existing plugin')
            ->addOption('pluginname', null, InputOption::VALUE_REQUIRED, 'The name of an existing plugin')
            ->addOption('command', null, InputOption::VALUE_REQUIRED, 'The name of the command you want to create');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pluginName = $this->getPluginName($input, $output);
        $this->checkAndUpdateRequiredPiwikVersion($pluginName, $output);

        $commandName = $this->getCommandName($input, $output);

        $exampleFolder = PIWIK_INCLUDE_PATH . '/plugins/ExampleCommand';
        $replace       = array(
            'ExampleCommandDescription' => $commandName,
            'ExampleCommand' => $pluginName,
            'examplecommand:helloworld' => strtolower($pluginName) . ':' . $this->buildCommandName($commandName),
            'examplecommand' => strtolower($pluginName),
            'HelloWorld'     => $commandName,
            'helloworld'     => strtolower($commandName)
         );

        $whitelistFiles = array('/Commands', '/Commands/HelloWorld.php');

        $this->copyTemplateToPlugin($exampleFolder, $pluginName, $replace, $whitelistFiles);

        $this->writeSuccessMessage($output, array(
            sprintf('Command %s for plugin %s generated', $commandName, $pluginName)
        ));
    }

    /**
     * Convert MyComponentName => my-component-name
     * @param  string $commandNameCamelCase
     * @return string
     */
    protected function buildCommandName($commandNameCamelCase)
    {
        return strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $commandNameCamelCase));
    }
    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return string
     * @throws \RuntimeException
     */
    private function getCommandName(InputInterface $input, OutputInterface $output)
    {
        $testname = $input->getOption('command');

        $validate = function ($testname) {
            if (empty($testname)) {
                throw new \InvalidArgumentException('You have to enter a command name');
            }

            if (!ctype_alnum($testname)) {
                throw new \InvalidArgumentException('Only alphanumeric characters are allowed as a command name. Use CamelCase if the name of your command contains multiple words.');
            }

            return $testname;
        };

        if (empty($testname)) {
            $dialog   = $this->getHelperSet()->get('dialog');
            $testname = $dialog->askAndValidate($output, 'Enter the name of the command (CamelCase): ', $validate);
        } else {
            $validate($testname);
        }

        $testname = ucfirst($testname);

        return $testname;
    }

    protected function getPluginName(InputInterface $input, OutputInterface $output)
    {
        $pluginNames = $this->getPluginNames();
        $invalidName = 'You have to enter the name of an existing plugin';

        return $this->askPluginNameAndValidate($input, $output, $pluginNames, $invalidName);
    }
}
