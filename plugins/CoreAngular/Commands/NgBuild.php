<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAngular\Commands;

use Piwik\Plugin\Manager;
use Piwik\Plugins\CoreConsole\Commands\GenerateAngularConstructBase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

// extending GenerateAngularConstructBase for convenience
class NgBuild extends GenerateAngularConstructBase
{
    protected function configure()
    {
        $this->setName('coreangular:ng-build')
            ->setDescription('Compile a plugin\'s Angular module.')
            ->addOption('pluginname', null, InputOption::VALUE_REQUIRED, 'The name of an existing plugin');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pluginName = $this->getPluginName($input, $output);

        $this->generateAngularJson();
        $this->generateTsConfigJson();

        $this->executeNgBuildCommand($pluginName);
    }

    private function generateAngularJson()
    {
        $angularJsonPath = PIWIK_INCLUDE_PATH . '/angular.json';

        $angularJsonBase = PIWIK_INCLUDE_PATH . '/angular.base.json';
        $angularJsonBase = file_get_contents($angularJsonBase);
        $angularJsonBase = json_decode($angularJsonBase, $isAssoc = true);

        $pluginAngularJsonSectionTemplate = <<<EOF
{
  "projectType": "library",
  "root": "plugins/{pluginName}/angular",
  "sourceRoot": "plugins/{pluginName}/angular/src",
  "prefix": "lib",
  "architect": {
    "build": {
      "builder": "@angular-devkit/build-angular:ng-packagr",
      "options": {
        "project": "plugins/{pluginName}/angular/ng-package.json"
      },
      "configurations": {
        "production": {
          "tsConfig": "plugins/{pluginName}/angular/tsconfig.lib.prod.json"
        },
        "development": {
          "tsConfig": "plugins/{pluginName}/angular/tsconfig.lib.json"
        }
      },
      "defaultConfiguration": "production"
    },
    "test": {
      "builder": "@angular-devkit/build-angular:karma",
      "options": {
        "main": "plugins/{pluginName}/angular/src/test.ts",
        "tsConfig": "plugins/{pluginName}/angular/tsconfig.spec.json",
        "karmaConfig": "plugins/{pluginName}/angular/karma.conf.js"
      }
    }
  }
}
EOF;

        foreach (Manager::getInstance()->getActivatedPlugins() as $pluginName) {
            $pluginAngularDir = PIWIK_INCLUDE_PATH . '/plugins/' . $pluginName . '/angular';
            if (!is_dir($pluginAngularDir)) {
                continue;
            }

            $pluginJsonSection = str_replace('{pluginName}', $pluginName, $pluginAngularJsonSectionTemplate);

            $angularJsonBase['projects'][$pluginName] = json_decode($pluginJsonSection, true);
        }

        file_put_contents($angularJsonPath, json_encode($angularJsonBase, JSON_PRETTY_PRINT));
    }

    private function executeNgBuildCommand($pluginName)
    {
        $command = 'ng build "' . $pluginName . '"';
        passthru($command);
    }

    private function generateTsConfigJson()
    {
        $tsConfigJson = PIWIK_INCLUDE_PATH . '/tsconfig.json';

        $tsConfigJsonBase = PIWIK_INCLUDE_PATH . '/tsconfig.base.json';
        $tsConfigJsonBase = file_get_contents($tsConfigJsonBase);
        $tsConfigJsonBase = json_decode($tsConfigJsonBase, $isAssoc = true);
        if (empty($tsConfigJsonBase)) {
            throw new \Exception('Invalid tsconfig.base.json file.');
        }

        foreach (Manager::getInstance()->getActivatedPlugins() as $pluginName) {
            $pluginAngularDir = PIWIK_INCLUDE_PATH . '/plugins/' . $pluginName . '/angular';
            if (!is_dir($pluginAngularDir)) {
                continue;
            }

            $packageName = '@matomo/' . $this->getSnakeCaseName($pluginName);
            $tsConfigJsonBase['compilerOptions']['paths'][$packageName] = [$pluginAngularDir . '/dist'];
        }

        file_put_contents($tsConfigJson, json_encode($tsConfigJsonBase, JSON_PRETTY_PRINT));
    }
}
