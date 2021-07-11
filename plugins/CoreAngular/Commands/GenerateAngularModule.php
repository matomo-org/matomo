<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAngular\Commands;

use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugin\Manager;
use Piwik\Plugins\CoreConsole\Commands\GenerateAngularConstructBase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateAngularModule extends GenerateAngularConstructBase
{
    protected function configure()
    {
        $this->setName('coreangular:generate-angular-module')
            ->setDescription('Generates an angular module for a plugin')
            ->addOption('pluginname', null, InputOption::VALUE_REQUIRED, 'The name of an existing plugin');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pluginName = $this->getPluginName($input, $output);
        $pluginPath = $this->getPluginPath($pluginName);
        $pluginLower = $this->getSnakeCaseName($pluginName);

        $targetDir = $pluginPath . '/angular/';

        if (is_dir($targetDir) || file_exists($targetDir)) {
            throw new \Exception('The Angular module for ' . $pluginName . ' already exists.');
        }

        $exampleFolder = Manager::getPluginDirectory('ExampleAngular');
        $replace       = array(
            'ExampleAngular'      => $pluginName,
            'ExampleAngularComponent' => $pluginName . 'Component',
            'example-angular'     => $pluginLower,
            'example-angular-component' => $pluginLower . '-component',
            'exampleAngularComponent' => lcfirst($pluginName) . 'Component',
        );

        $whitelistFiles = [
            '/angular',
            '/angular/src',
            '/angular/src/lib',
            '/angular/src/lib/example.component.ts',
            '/angular/src/lib/example-angular.module.ts',
            '/angular/src/ExampleAngular.ts',
            '/angular/ng-package.json',
            '/angular/package.json',
            '/angular/tsconfig.lib.json',
            '/angular/tsconfig.lib.prod.json',
        ];

        $this->copyTemplateToPlugin($exampleFolder, $pluginName, $replace, $whitelistFiles);
    }
}
