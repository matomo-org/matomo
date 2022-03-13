<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreVue\Commands;

use Piwik\Plugin\Manager;
use Piwik\Plugins\CoreConsole\Commands\GenerateAngularConstructBase;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateVueComponent extends GenerateAngularConstructBase
{
    protected function configure()
    {
        $this->setName('generate:vue-component')
            ->setDescription('Generates a vue component for a plugin.')
            ->addOption('pluginname', null, InputOption::VALUE_REQUIRED, 'The name of an existing plugin')
            ->addOption('component', null, InputOption::VALUE_REQUIRED, 'The name of the component.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pluginName = $this->getPluginName($input, $output);
        $component  = $this->getConstructName($input, $output, $optionName = 'component', $constructType = 'component');
        $pluginPath = $this->getPluginPath($pluginName);

        $targetFile = $pluginPath . '/vue/src/' . $component . '.vue';

        if (is_file($targetFile)) {
            throw new \Exception('The Vue component ' . $component . '.vue already exists in plugin '
                . $pluginName);
        }

        $exampleFolder = Manager::getPluginDirectory('ExampleVue');
        $adapterFunctionName = lcfirst($component) . 'Adapter';
        $replace = array(
            'ExampleVue'       => $pluginName,
            'ExampleComponent' => $component,
            'exampleVueComponent' => lcfirst($component),
            'AsyncExampleComponent' => 'Async' . $component,
            'exampleVueComponentAdapter' => $adapterFunctionName,
        );

        $allowlistFiles = array(
            '/vue',
            '/vue/src',
            '/vue/src/ExampleComponent',
            '/vue/src/ExampleComponent/ExampleComponent.vue',
            '/vue/src/ExampleComponent/ExampleComponent.adapter.ts',
        );

        $this->copyTemplateToPlugin($exampleFolder, $pluginName, $replace, $allowlistFiles);

        $indexFile = $pluginPath . '/vue/src/index.ts';
        if (!file_exists($indexFile)) {
            file_put_contents($indexFile, '');
        }
        file_put_contents($indexFile, "export { default as $adapterFunctionName } from './$component/$component.adapter';\n", FILE_APPEND);
        file_put_contents($indexFile, "export { default as $component } from './$component/$component.vue';\n", FILE_APPEND);

        // TODO: generate a less file as well?

        $this->writeSuccessMessage($output, array(
            sprintf('Vue component "%s" for plugin "%s" in "%s" generated', $component, $pluginName, $targetFile),
            sprintf('You should now build the vue library using the vue:build command (use --watch to continuously build after making changes).'),
        ));
    }
}
