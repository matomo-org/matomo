<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Filesystem;
use Piwik\Plugins\ExamplePlugin\ExamplePlugin;
use Piwik\Plugin;
use Piwik\Version;

/**
 */
class GeneratePlugin extends GeneratePluginBase
{
    protected function configure()
    {
        $this->setName('generate:plugin')
            ->setAliases(array('generate:theme'))
            ->setDescription('Generates a new plugin/theme including all needed files')
            ->addRequiredValueOption('name', null, 'Plugin name ([a-Z0-9_-])')
            ->addRequiredValueOption('description', null, 'Plugin description, max 150 characters')
            ->addOptionalValueOption('pluginversion', null, 'Plugin version')
            ->addNoValueOption('overwrite', null, 'Generate even if plugin directory already exists.');
    }

    protected function doExecute(): int
    {
        $isTheme     = $this->isTheme();
        $pluginName  = $this->getPluginName();
        $description = $this->getPluginDescription();
        $version     = $this->getPluginVersion();

        $this->generatePluginFolder($pluginName);

        $plugin = new ExamplePlugin();
        $info   = $plugin->getInformation();
        $exampleDescription = $info['description'];

        if ($isTheme) {
            $exampleFolder = Plugin\Manager::getPluginDirectory('ExampleTheme');
            $replace       = array(
                'ExampleTheme'       => $pluginName,
                $exampleDescription  => $description,
                '0.1.0'              => $version,
                '3.0.0-b1'           => Version::VERSION
            );
            $whitelistFiles = array();
        } else {
            $exampleFolder = Plugin\Manager::getPluginDirectory('ExamplePlugin');
            $replace       = array(
                'ExamplePlugin'      => $pluginName,
                $exampleDescription  => $description,
                '0.1.0'              => $version,
                '3.0.0-b1'           => Version::VERSION
            );
            $whitelistFiles = array(
                '/ExamplePlugin.php',
                '/plugin.json',
                '/README.md',
                '/CHANGELOG.md',
                '/screenshots',
                '/screenshots/.gitkeep',
                '/docs',
                '/docs/faq.md',
                '/docs/index.md',
                '/config',
                '/config/config.php',
                '/config/tracker.php'
            );
        }

        $this->copyTemplateToPlugin($exampleFolder, $pluginName, $replace, $whitelistFiles);
        $this->checkAndUpdateRequiredPiwikVersion($pluginName);

        if ($isTheme) {
            $this->writeSuccessMessage(array(
                sprintf('Theme %s %s generated.', $pluginName, $version),
                'If you have not done yet check out our Theming guide <comment>https://developer.matomo.org/guides/theming</comment>',
                'Enjoy!'
            ));
        } else {
            $this->writeSuccessMessage(array(
                sprintf('Plugin %s %s generated.', $pluginName, $version),
                'Our developer guides will help you developing this plugin, check out <comment>https://developer.matomo.org/guides</comment>',
                'To see a list of available generators execute <comment>./console list generate</comment>',
                'Enjoy!'
            ));
        }

        return self::SUCCESS;
    }

    /**
     * @return bool
     */
    private function isTheme()
    {
        $commandName = $this->getInput()->getFirstArgument();

        return false !== strpos($commandName, 'theme');
    }

    protected function generatePluginFolder($pluginName)
    {
        $pluginPath = $this->getPluginPath($pluginName);
        Filesystem::mkdir($pluginPath);
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    protected function getPluginName()
    {
        $overwrite = $this->getInput()->getOption('overwrite');

        $self = $this;

        $validate = function ($pluginName) use ($self, $overwrite) {
            if (empty($pluginName)) {
                throw new \RuntimeException('You have to enter a plugin name');
            }

            if (!Plugin\Manager::getInstance()->isValidPluginName($pluginName)) {
                throw new \RuntimeException(sprintf('The plugin name %s is not valid. The name must be no longer than 60 characters and start with a letter and is only allowed to contain numbers and letters.', $pluginName));
            }

            $pluginPath = $self->getPluginPath($pluginName);

            if (
                file_exists($pluginPath)
                && !$overwrite
            ) {
                throw new \RuntimeException('A plugin with this name already exists');
            }

            return $pluginName;
        };

        $pluginName = $this->getInput()->getOption('name');

        if (empty($pluginName)) {
            $pluginName = $this->askAndValidate('Enter a plugin name: ', $validate);
        } else {
            $validate($pluginName);
        }

        $pluginName = ucfirst($pluginName);

        return $pluginName;
    }

    /**
     * @return mixed
     * @throws \RuntimeException
     */
    protected function getPluginDescription()
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

        $description = $this->getInput()->getOption('description');

        if (empty($description)) {
            $description = $this->askAndValidate('Enter a plugin description: ', $validate);
        } else {
            $validate($description);
        }

        return $description;
    }

    /**
     * @return string
     */
    protected function getPluginVersion()
    {
        $version = $this->getInput()->getOption('pluginversion');

        if (is_null($version)) {
            $version = $this->ask('Enter a plugin version number (default to 0.1.0): ', '0.1.0');
        }

        return $version;
    }
}
