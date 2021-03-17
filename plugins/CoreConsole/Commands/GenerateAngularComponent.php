<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Plugin\Manager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateAngularComponent extends GenerateAngularConstructBase
{
    protected function configure()
    {
        $this->setName('generate:angular-component')
            ->setDescription('Generates a template for an AngularJS component')
            ->addOption('pluginname', null, InputOption::VALUE_REQUIRED, 'The name of an existing plugin')
            ->addOption('component', null, InputOption::VALUE_REQUIRED, 'The name of the component you want to create.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pluginName = $this->getPluginName($input, $output);
        $component  = $this->getConstructName($input, $output, $optionName = 'component', $constructType = 'component');
        $pluginPath = $this->getPluginPath($pluginName);

        $componentLower = $this->getSnakeCaseName($component);

        $targetDir = $pluginPath . '/angularjs/' . $componentLower;

        if (is_dir($targetDir) || file_exists($targetDir)) {
            throw new \Exception('The AngularJS component ' . $componentLower . ' already exists in plugin '
                . $pluginName);
        }

        $exampleFolder = Manager::getPluginDirectory('ExamplePlugin');
        $replace       = array(
            'ExamplePlugin'       => $pluginName,
            'example-component' => $componentLower,
            'componentClass'      => lcfirst($component),
            'componentAs'         => lcfirst($component),
            'Component'           => $component,
        );

        $componentPath = '/angularjs/example-component';

        $whitelistFiles = array(
            '/angularjs',
            $componentPath,
            $componentPath . '/example-component.component.html',
            $componentPath . '/example-component.component.js',
            $componentPath . '/example-component.component.less',
        );

        $this->copyTemplateToPlugin($exampleFolder, $pluginName, $replace, $whitelistFiles);

        $replacedBasePath = '/angularjs/' . $componentLower . '/' . $componentLower;
        $js1   = $replacedBasePath . '.component.js';
        $less1 = $replacedBasePath . '.component.less';

        $this->writeSuccessMessage($output, array(
            sprintf('AngularJS directive "%s" for plugin "%s" in "%s" generated', $component, $pluginName, $targetDir),
            sprintf('In <comment>%1$s/%2$s.php</comment> you should now require the JS files', $pluginPath, $pluginName),
            sprintf('<comment>%1$s%2$s</comment>', $pluginPath, $js1),
            sprintf('and the less file <comment>%1$s%2$s</comment>.', $pluginPath, $less1),
            'If you are not familiar with this have a look at <comment>https://developer.matomo.org/guides/working-with-piwiks-ui</comment>'
        ));
    }
}
