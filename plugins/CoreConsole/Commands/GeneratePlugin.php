<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreConsole\Commands;


use Piwik\Filesystem;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 */
class GeneratePlugin extends GeneratePluginBase
{
    protected function configure()
    {
        $this->setName('generate:plugin')
            ->setAliases(array('generate:theme'))
            ->setDescription('Generates a new plugin/theme including all needed files')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Plugin name ([a-Z0-9_-])')
            ->addOption('description', null, InputOption::VALUE_REQUIRED, 'Plugin description, max 150 characters')
            ->addOption('pluginversion', null, InputOption::VALUE_OPTIONAL, 'Plugin version')
            ->addOption('full', null, InputOption::VALUE_OPTIONAL, 'If a value is set, an API and a Controller will be created as well. Option is only available for creating plugins, not for creating themes.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $isTheme          = $this->isTheme($input);
        $pluginName       = $this->getPluginName($input, $output);
        $description      = $this->getPluginDescription($input, $output);
        $version          = $this->getPluginVersion($input, $output);
        $createFullPlugin = !$isTheme && $this->getCreateFullPlugin($input, $output);

        $this->generatePluginFolder($pluginName);

        if ($isTheme) {
            $exampleFolder = PIWIK_INCLUDE_PATH . '/plugins/ExampleTheme';
            $replace       = array(
                'ExampleTheme'       => $pluginName,
                'ExampleDescription' => $description,
                '0.1.0'              => $version
            );
            $whitelistFiles = array();

        } else {

            $exampleFolder = PIWIK_INCLUDE_PATH . '/plugins/ExamplePlugin';
            $replace       = array(
                'ExamplePlugin'      => $pluginName,
                'ExampleDescription' => $description,
                '0.1.0'              => $version
            );
            $whitelistFiles = array(
                '/ExamplePlugin.php',
                '/plugin.json',
                '/README.md',
                '/.travis.yml',
                '/screenshots',
                '/screenshots/.gitkeep',
                '/javascripts',
                '/javascripts/plugin.js',
            );

        }

        $this->copyTemplateToPlugin($exampleFolder, $pluginName, $replace, $whitelistFiles);

        $this->writeSuccessMessage($output, array(
             sprintf('%s %s %s generated.', $isTheme ? 'Theme' : 'Plugin', $pluginName, $version),
             'Enjoy!'
        ));

        if ($createFullPlugin) {
            $this->executePluginCommand($output, 'generate:api', $pluginName);
            $this->executePluginCommand($output, 'generate:controller', $pluginName);
        }
    }

    private function executePluginCommand(OutputInterface $output, $commandName, $pluginName)
    {
        $command = $this->getApplication()->find($commandName);
        $arguments = array(
            'command'      => $commandName,
            '--pluginname' => $pluginName
        );

        $input = new ArrayInput($arguments);
        $command->run($input, $output);
    }

    /**
     * @param InputInterface $input
     * @return bool
     */
    private function isTheme(InputInterface $input)
    {
        $commandName = $input->getFirstArgument();

        return false !== strpos($commandName, 'theme');
    }

    protected function generatePluginFolder($pluginName)
    {
        $pluginPath = $this->getPluginPath($pluginName);
        Filesystem::mkdir($pluginPath);
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return array
     * @throws \RunTimeException
     */
    protected function getPluginName(InputInterface $input, OutputInterface $output)
    {
        $self = $this;

        $validate = function ($pluginName) use ($self) {
            if (empty($pluginName)) {
                throw new \RunTimeException('You have to enter a plugin name');
            }

            if (!Filesystem::isValidFilename($pluginName)) {
                throw new \RunTimeException(sprintf('The plugin name %s is not valid', $pluginName));
            }

            $pluginPath = $self->getPluginPath($pluginName);

            if (file_exists($pluginPath)) {
                throw new \RunTimeException('A plugin with this name already exists');
            }

            return $pluginName;
        };

        $pluginName = $input->getOption('name');

        if (empty($pluginName)) {
            $dialog = $this->getHelperSet()->get('dialog');
            $pluginName = $dialog->askAndValidate($output, 'Enter a plugin name: ', $validate);
        } else {
            $validate($pluginName);
        }

        $pluginName = ucfirst($pluginName);

        return $pluginName;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     * @throws \RunTimeException
     */
    protected function getPluginDescription(InputInterface $input, OutputInterface $output)
    {
        $validate = function ($description) {
            if (empty($description)) {
                throw new \RunTimeException('You have to enter a description');
            }
            if (150 < strlen($description)) {
                throw new \RunTimeException('Description is too long, max 150 characters allowed.');
            }

            return $description;
        };

        $description = $input->getOption('description');

        if (empty($description)) {
            $dialog = $this->getHelperSet()->get('dialog');
            $description = $dialog->askAndValidate($output, 'Enter a plugin description: ', $validate);
        } else {
            $validate($description);
        }

        return $description;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return string
     */
    protected function getPluginVersion(InputInterface $input, OutputInterface $output)
    {
        $version = $input->getOption('pluginversion');

        if (is_null($version)) {
            $dialog = $this->getHelperSet()->get('dialog');
            $version = $dialog->ask($output, 'Enter a plugin version number (default to 0.1.0): ', '0.1.0');
        }

        return $version;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    protected function getCreateFullPlugin(InputInterface $input, OutputInterface $output)
    {
        $full = $input->getOption('full');

        if (is_null($full)) {
            $dialog = $this->getHelperSet()->get('dialog');
            $full = $dialog->askConfirmation($output, 'Shall we also create an API and a Controller? (y/N)', false);
        }

        return !empty($full);
    }

}
