<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreReact\Commands;

use Piwik\Plugin\Manager;
use Piwik\Plugins\CoreConsole\Commands\GenerateAngularConstructBase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateReact extends GenerateAngularConstructBase
{
    protected function configure()
    {
        $this->setName('generate:react')
            ->setDescription('Generates an react module for a plugin')
            ->addOption('pluginname', null, InputOption::VALUE_REQUIRED, 'The name of an existing plugin');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pluginName = $this->getPluginName($input, $output);
        $pluginPath = $this->getPluginPath($pluginName);
        $pluginLower = $this->getSnakeCaseName($pluginName);

        $targetDir = $pluginPath . '/react/';

        if (is_dir($targetDir) || file_exists($targetDir)) {
            throw new \Exception('The React project for ' . $pluginName . ' already exists.');
        }

        $exampleFolder = Manager::getPluginDirectory('ExampleReact');
        $replace       = array(
            'ExampleReact'      => $pluginName,
            'ExampleReactComponent' => $pluginName . 'Component',
            'example-react' => $pluginLower,
        );

        $whitelistFiles = [
            '/react',
            '/react/src',
            '/react/.gitignore',
            '/react/package.json',
            '/react/src/index.js',
        ];

        $this->copyTemplateToPlugin($exampleFolder, $pluginName, $replace, $whitelistFiles);
    }
}