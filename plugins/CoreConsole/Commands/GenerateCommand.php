<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Plugin\Manager;

/**
 */
class GenerateCommand extends GeneratePluginBase
{
    protected function configure()
    {
        $this->setName('generate:command')
            ->setDescription('Adds a command to an existing plugin')
            ->addRequiredValueOption('pluginname', null, 'The name of an existing plugin')
            ->addRequiredValueOption('command', null, 'The name of the command you want to create');
    }

    protected function doExecute(): int
    {
        $pluginName = $this->getPluginName();
        $this->checkAndUpdateRequiredPiwikVersion($pluginName);

        $commandName = $this->getCommandName();

        $exampleFolder = Manager::getPluginDirectory('ExampleCommand');
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

        $this->writeSuccessMessage(sprintf('Command %s for plugin %s generated', $commandName, $pluginName));

        return self::SUCCESS;
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
     * @return string
     * @throws \RuntimeException
     */
    private function getCommandName()
    {
        $testname = $this->getInput()->getOption('command');

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
            $testname = $this->askAndValidate('Enter the name of the command (CamelCase): ', $validate);
        } else {
            $validate($testname);
        }

        $testname = ucfirst($testname);

        return $testname;
    }

    protected function getPluginName()
    {
        $pluginNames = $this->getPluginNames();
        $invalidName = 'You have to enter the name of an existing plugin';

        return $this->askPluginNameAndValidate($pluginNames, $invalidName);
    }
}
