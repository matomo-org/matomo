<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\Commands;

abstract class GenerateVueConstructBase extends GeneratePluginBase
{
    /**
     * @param string $optionName    the name of the option to use.
     * @param string $constructType 'directive', 'component', etc.
     * @return string
     * @throws \RuntimeException
     */
    protected function getConstructName($optionName, $constructType)
    {
        $testname = $this->getInput()->getOption($optionName);

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
            $testname = $this->askAndValidate(
                "Enter the name of the $constructType you want to create: ",
                $validate
            );
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
