<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreVue\Commands;

use Piwik\Filesystem;
use Piwik\Plugin\ConsoleCommand;

class BuildPolyfill extends ConsoleCommand
{
    protected function configure()
    {
        $this->setName('vue:build-polyfill');
        $this->setDescription('Builds the polyfill UMD.');
        $this->addNoValueOption('clear-cache');
    }

    public function isEnabled()
    {
        return \Piwik\Development::isEnabled();
    }

    protected function doExecute(): int
    {
        Build::checkViteAvailable();

        if ($this->getInput()->getOption('clear-cache')) {
            $this->clearViteCache();
        }

        $configFile = PIWIK_INCLUDE_PATH . '/plugins/CoreVue/polyfills/vite.config.ts';

        // Regenerate the list of core-js polyfills the bundle needs, based on actual usage across
        // plugins/*/vue/src and the browsers in .browserslistrc, before building.
        $generator = PIWIK_INCLUDE_PATH . '/plugins/CoreVue/polyfills/scripts/generate-corejs-imports.mjs';
        passthru('cd ' . PIWIK_INCLUDE_PATH . ' && FORCE_COLOR=1 node ' . $generator, $generatorResult);
        if ($generatorResult !== 0) {
            return self::FAILURE;
        }

        // Two passes: unminified MatomoPolyfills.js, then minified MatomoPolyfills.min.js. The phase
        // is communicated to the Vite config through MATOMO_VUE_PHASE.
        foreach (['dev', 'min'] as $phase) {
            $command = 'cd ' . PIWIK_INCLUDE_PATH . " && FORCE_COLOR=1 MATOMO_VUE_PHASE=$phase "
                . 'node ' . Build::getViteBin() . " build --config $configFile";
            passthru($command, $buildResult);
            if ($buildResult !== 0) {
                return self::FAILURE;
            }
        }

        return self::SUCCESS;
    }

    private function clearViteCache()
    {
        $path = PIWIK_INCLUDE_PATH . '/node_modules/.vite';
        Filesystem::unlinkRecursive($path, true);
    }
}
