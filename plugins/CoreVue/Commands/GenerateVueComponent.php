<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreVue\Commands;

use Piwik\Plugin\Manager;
use Piwik\Plugins\CoreConsole\Commands\GenerateVueConstructBase;

class GenerateVueComponent extends GenerateVueConstructBase
{
    protected function configure()
    {
        $this->setName('generate:vue-component')
            ->setDescription('Generates a vue component for a plugin.')
            ->addRequiredValueOption('pluginname', null, 'The name of an existing plugin')
            ->addRequiredValueOption('component', null, 'The name of the component.');
    }

    protected function doExecute(): int
    {
        $pluginName = $this->getPluginName();
        $component  = $this->getConstructName($optionName = 'component', $constructType = 'component');
        $pluginPath = $this->getPluginPath($pluginName);

        $targetFile = $pluginPath . '/vue/src/' . $component . '.vue';

        if (is_file($targetFile)) {
            throw new \Exception('The Vue component ' . $component . '.vue already exists in plugin '
                . $pluginName);
        }

        $exampleFolder = Manager::getPluginDirectory('ExampleVue');
        $replace = array(
            'ExampleVue'       => $pluginName,
            'ExampleComponent' => $component,
            'exampleVueComponent' => lcfirst($component),
            'AsyncExampleComponent' => 'Async' . $component,
        );

        $allowlistFiles = array(
            '/vue',
            '/vue/src',
            '/vue/src/ExampleComponent',
            '/vue/src/ExampleComponent/ExampleComponent.vue',
        );

        $this->copyTemplateToPlugin($exampleFolder, $pluginName, $replace, $allowlistFiles);

        $indexFile = $pluginPath . '/vue/src/index.ts';
        if (!file_exists($indexFile)) {
            file_put_contents($indexFile, '');
        }
        file_put_contents($indexFile, "export { default as $component } from './$component/$component.vue';\n", FILE_APPEND);

        // TODO: generate a less file as well?

        $this->writeSuccessMessage(array(
            sprintf('Vue component "%s" for plugin "%s" in "%s" generated', $component, $pluginName, $targetFile),
            sprintf('You should now build the vue library using the vue:build command (use --watch to continuously build after making changes).'),
        ));

        return self::SUCCESS;
    }
}
