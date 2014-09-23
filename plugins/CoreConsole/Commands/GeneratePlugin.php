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
use Piwik\Version;
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
            ->addOption('pluginversion', null, InputOption::VALUE_OPTIONAL, 'Plugin version');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $isTheme     = $this->isTheme($input);
        $pluginName  = $this->getPluginName($input, $output);
        $description = $this->getPluginDescription($input, $output);
        $version     = $this->getPluginVersion($input, $output);

        $this->generatePluginFolder($pluginName);

        if ($isTheme) {
            $exampleFolder = PIWIK_INCLUDE_PATH . '/plugins/ExampleTheme';
            $replace       = array(
                'ExampleTheme'       => $pluginName,
                'ExampleDescription' => $description,
                '0.1.0'              => $version,
                'PIWIK_VERSION'      => Version::VERSION
            );
            $whitelistFiles = array();

        } else {

            $exampleFolder = PIWIK_INCLUDE_PATH . '/plugins/ExamplePlugin';
            $replace       = array(
                'ExamplePlugin'      => $pluginName,
                'ExampleDescription' => $description,
                '0.1.0'              => $version,
                'PIWIK_VERSION'      => Version::VERSION
            );
            $whitelistFiles = array(
                '/ExamplePlugin.php',
                '/plugin.json',
                '/README.md',
                '/screenshots',
                '/screenshots/.gitkeep',
            );
        }

        $this->copyTemplateToPlugin($exampleFolder, $pluginName, $replace, $whitelistFiles);

        if ($isTheme) {
            $this->writeSuccessMessage($output, array(
                sprintf('Theme %s %s generated.', $pluginName, $version),
                'If you have not done yet check out our Theming guide <comment>http://developer.piwik.org/guides/theming</comment>',
                'Enjoy!'
            ));
        } else {
            $this->writeSuccessMessage($output, array(
                sprintf('Plugin %s %s generated.', $pluginName, $version),
                'Our developer guides will help you developing this plugin, check out <comment>http://developer.piwik.org/guides</comment>',
                'To see a list of available generators execute <comment>./console list generate</comment>',
                'Enjoy!'
            ));
        }
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
     * @throws \RuntimeException
     */
    protected function getPluginName(InputInterface $input, OutputInterface $output)
    {
        $self = $this;

        $validate = function ($pluginName) use ($self) {
            if (empty($pluginName)) {
                throw new \RuntimeException('You have to enter a plugin name');
            }

            if (!Filesystem::isValidFilename($pluginName)) {
                throw new \RuntimeException(sprintf('The plugin name %s is not valid', $pluginName));
            }

            $pluginPath = $self->getPluginPath($pluginName);

            if (file_exists($pluginPath)) {
                throw new \RuntimeException('A plugin with this name already exists');
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
     * @throws \RuntimeException
     */
    protected function getPluginDescription(InputInterface $input, OutputInterface $output)
    {
        $validate = function ($description) {
            if (empty($description)) {
                throw new \RuntimeException('You have to enter a description');
            }
            if (150 < strlen($description)) {
                throw new \RuntimeException('Description is too long, max 150 characters allowed.');
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

}
