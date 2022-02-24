<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreVue\Commands;

use Piwik\Filesystem;
use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class BuildPolyfill extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('vue:build-polyfill');
        $this->setDescription('Builds the polyfill UMD.');
        $this->addOption('clear-webpack-cache', null, InputOption::VALUE_NONE);
    }

    public function isEnabled()
    {
        return \Piwik\Development::isEnabled();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        Build::checkVueCliServiceAvailable();

        $clearWebpackCache = $input->getOption('clear-webpack-cache');
        if ($clearWebpackCache) {
            $this->clearWebpackCache();
        }

        $this->createDummyPackageJson();

        $dir = PIWIK_INCLUDE_PATH . '/plugins/CoreVue/polyfills';
        foreach (['development', 'production'] as $env) {
            $command = "cd '$dir' && BROWSERSLIST_IGNORE_OLD_DATA=1 FORCE_COLOR=1 " . Build::getVueCliServiceBin()
                . ' build --target app --mode ' . $env . ' --name MatomoPolyfills ./src/index.ts --dest ./dist';
            if ($env == 'production') {
                $command .= ' --no-clean';
            }
            passthru($command);
        }

        $this->deleteExtraFiles();
    }

    private function createDummyPackageJson()
    {
        $packageJson = file_get_contents(PIWIK_INCLUDE_PATH . '/package.json');
        $packageJson = json_decode($packageJson, true);

        $dummyPackageJson = [
            'name' => '@matomo/polyfills',
            'version' => '1.0.0',
            'description' => 'dummy package.json required for vue compilation in subdirectory',
            'devDependencies' => $packageJson['devDependencies'],
        ];

        $dummyPackageJson = json_encode($dummyPackageJson, JSON_PRETTY_PRINT);

        file_put_contents(PIWIK_INCLUDE_PATH . '/plugins/CoreVue/polyfills/package.json', $dummyPackageJson);
    }

    private function deleteExtraFiles()
    {
        @unlink(PIWIK_INCLUDE_PATH . "/plugins/CoreVue/polyfills/dist/index.html");
    }

    private function clearWebpackCache()
    {
        $path = PIWIK_INCLUDE_PATH . '/plugins/CoreVue/polyfills/node_modules/.cache';
        Filesystem::unlinkRecursive($path, true);
    }
}