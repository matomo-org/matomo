<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Plugin;
use Piwik\Updater;
use Piwik\Version;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateUpdate extends GeneratePluginBase
{
    protected function configure()
    {
        $this->setName('generate:update')
            ->setDescription('Adds a new update to an existing plugin or "core"')
            ->addOption('component', null, InputOption::VALUE_REQUIRED, 'The name of an existing plugin or "core"');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $component = $this->getComponent($input, $output);

        $version   = $this->getVersion($component);
        $namespace = $this->getNamespace($component);
        $className = $this->getUpdateClassName($component, $version);

        $exampleFolder = PIWIK_INCLUDE_PATH . '/plugins/ExamplePlugin';
        $replace       = array('Piwik\Plugins\ExamplePlugin\Updates' => $namespace,
                               'ExamplePlugin' => $component,
                               'Updates_0_0_2' => $className,
                               '0.0.2'         => $version);
        $whitelistFiles = array('/Updates', '/Updates/0.0.2.php');

        $this->copyTemplateToPlugin($exampleFolder, $component, $replace, $whitelistFiles);

        $this->writeSuccessMessage($output, array(
            sprintf('Updates/%s.php for %s generated.', $version, $component),
            'You should have a look at the method update() or getSql() now.',
            'Enjoy!'
        ));
    }

    private function getUpdateClassName($component, $version)
    {
        $updater   = new Updater();
        $className = $updater->getUpdateClassName($component, $version);
        $parts     = explode('\\', $className);

        return end($parts);
    }

    private function getVersion($component)
    {
        if ($component === 'core') {
            return Version::VERSION;
        }

        $pluginManager = Plugin\Manager::getInstance();

        if ($pluginManager->isPluginLoaded($component)) {
            $plugin = $pluginManager->getLoadedPlugin($component);
        } else {
            $plugin = new Plugin($component);
        }

        return $plugin->getVersion();
    }

    private function getNamespace($component)
    {
        $updater   = new Updater();
        $className = $updater->getUpdateClassName($component, 'xx');
        $className = str_replace('Updates_xx', '', $className);
        $className = trim($className, '\\');

        if ($component !== 'core') {
            $className .= '\Updates';
        }

        return $className;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return array
     * @throws \RuntimeException
     */
    private function getComponent(InputInterface $input, OutputInterface $output)
    {
        $components   = $this->getPluginNames();
        $components[] = 'core';

        $validate = function ($component) use ($components) {
            if (!in_array($component, $components)) {
                throw new \InvalidArgumentException('You have to enter a name of an existing plugin or "core".');
            }

            return $component;
        };

        $component = $input->getOption('component');

        if (empty($component)) {
            $dialog    = $this->getHelperSet()->get('dialog');
            $component = $dialog->askAndValidate($output, 'Enter the name of your plugin or "core": ', $validate, false, null, $components);
        } else {
            $validate($component);
        }

        return $component;
    }
}
