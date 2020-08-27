<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class GenerateAngularConstructBase extends GeneratePluginBase
{
    /**
     * Convert MyComponentName => my-component-name
     * @param  string $directiveCamelCase
     * @return string
     */
    protected function getSnakeCaseName($camelCase)
    {
        return strtolower(preg_replace('/([a-zA-Z])(?=[A-Z])/', '$1-', $camelCase));
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @param string $optionName the name of the option to use.
     * @param string $constructType 'directive', 'component', etc.
     * @return string
     * @throws \RuntimeException
     */
    protected function getConstructName(InputInterface $input, OutputInterface $output, $optionName, $constructType)
    {
        $testname = $input->getOption($optionName);

        $validate = function ($testname) use ($constructType) {
            if (empty($testname)) {
                throw new \InvalidArgumentException("You have to enter a name for the $constructType");
            }

            if (!ctype_alnum($testname)) {
                throw new \InvalidArgumentException("Only alphanumeric characters are allowed as a $constructType "
                    . "name. Use CamelCase if the name of your $constructType contains multiple words.");
            }

            return $testname;
        };

        if (empty($testname)) {
            $dialog   = $this->getHelperSet()->get('dialog');
            $testname = $dialog->askAndValidate($output, "Enter the name of the $constructType you want to create: ",
                $validate);
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