<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Common;
use Piwik\Plugin\Manager;

class GenerateSystemCheck extends GeneratePluginBase
{
    protected function configure()
    {
        $this->setName('generate:system-check')
            ->setDescription('Adds a new system check to an existing plugin')
            ->addRequiredValueOption('pluginname', null, 'The name of an existing plugin which does not have a menu defined yet')
            ->addRequiredValueOption('checkname', null, 'The name of the system check you want to create');
    }

    protected function doExecute(): int
    {
        $pluginName = $this->getPluginName();
        $this->checkAndUpdateRequiredPiwikVersion($pluginName);

        $checkName = $this->getSystemCheckName();
        $className = ucfirst(str_replace(' ', '', $checkName));
        if (!Common::stringEndsWith($className, 'check') && !Common::stringEndsWith($className, 'Check')) {
            $className .= 'Check';
        }

        $exampleFolder  = Manager::getPluginDirectory('ExamplePlugin');
        $replace        = array('ExampleCheck'  => $className,
                                'Example Check'  => $checkName,
                                'ExamplePlugin'  => $pluginName,
        );

        $whitelistFiles = array('/Diagnostic', '/Diagnostic/ExampleCheck.php');

        $this->copyTemplateToPlugin($exampleFolder, $pluginName, $replace, $whitelistFiles);

        $this->writeSuccessMessage(array(
            sprintf('plugins/%s/Diagnostic/%s.php for %s generated.', $pluginName, $className, $pluginName),
            sprintf('You should now implement the method called <comment>execute</comment> in %s.php', $className),
            'You also need to make the diagnostic check known to Matomo in your "plugins/' . $pluginName . '/config/config.php".',
            'Read more about this here: https://developer.matomo.org/guides/system-check',
            'Enjoy!'
        ));

        return self::SUCCESS;
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    protected function getSystemCheckName()
    {
        $input = $this->getInput();
        $validate = function ($checkName) {
            if (empty($checkName)) {
                throw new \InvalidArgumentException('Please enter the name of your system check');
            }

            if (preg_match("/[^A-Za-z0-9 ]/", $checkName)) {
                throw new \InvalidArgumentException('Only alpha numerical characters and whitespaces are allowed');
            }

            return $checkName;
        };

        $checkName = $input->getOption('checkname');

        if (empty($checkName)) {
            $checkName = $this->askAndValidate(
                'Enter the name of your system check, for example "PDF PHP Extension Check": ',
                $validate
            );
        } else {
            $validate($checkName);
        }

        $checkName = ucfirst($checkName);

        return $checkName;
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    protected function getPluginName()
    {
        $pluginNames = $this->getPluginNames();
        $invalidName = 'You have to enter a name of an existing plugin.';

        return $this->askPluginNameAndValidate($pluginNames, $invalidName);
    }
}
