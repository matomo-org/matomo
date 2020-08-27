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

/**
 */
class GenerateAngularDirective extends GenerateAngularConstructBase
{
    protected function configure()
    {
        $this->setName('generate:angular-directive')
             ->setDescription('Generates a template for an AngularJS directive')
             ->addOption('pluginname', null, InputOption::VALUE_REQUIRED, 'The name of an existing plugin')
             ->addOption('directive', null, InputOption::VALUE_REQUIRED, 'The name of the directive you want to create.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pluginName = $this->getPluginName($input, $output);
        $directive  = $this->getConstructName($input, $output, $optionName = 'directive', $constructType = 'directive');
        $pluginPath = $this->getPluginPath($pluginName);

        $directiveLower = $this->getSnakeCaseName($directive);

        $targetDir = $pluginPath . '/angularjs/' . $directiveLower;

        if (is_dir($targetDir) || file_exists($targetDir)) {
            throw new \Exception('The AngularJS directive ' . $directiveLower . ' already exists in plugin ' . $pluginName);
        }

        $exampleFolder = Manager::getPluginDirectory('ExamplePlugin');
        $replace       = array(
            'ExamplePlugin'       => $pluginName,
            'directive-component' => $directiveLower,
            'componentClass'      => lcfirst($directive),
            'componentAs'         => lcfirst($directive),
            'component'           => $directiveLower,
            'Component'           => $directive
         );

        $componentPath = '/angularjs/directive-component';

        $whitelistFiles = array(
            '/angularjs',
            $componentPath,
            $componentPath . '/component.controller.js',
            $componentPath . '/component.directive.html',
            $componentPath . '/component.directive.js',
            $componentPath . '/component.directive.less',
        );

        $this->copyTemplateToPlugin($exampleFolder, $pluginName, $replace, $whitelistFiles);

        $replacedBasePath = '/angularjs/' . $directiveLower . '/' . $directiveLower;
        $js1   = $replacedBasePath . '.controller.js';
        $js2   = $replacedBasePath . '.directive.js';
        $less1 = $replacedBasePath . '.directive.less';

        $this->writeSuccessMessage($output, array(
            sprintf('AngularJS directive "%s" for plugin "%s" in "%s" generated', $directive, $pluginName, $targetDir),
            sprintf('In <comment>%1$s/%2$s.php</comment> you should now require the JS files', $pluginPath, $pluginName),
            sprintf('<comment>%1$s%2$s</comment>, <comment>%1$s%3$s</comment>', $pluginPath, $js1, $js2),
            sprintf('and the less file <comment>%1$s%2$s</comment>.', $pluginPath, $less1),
            'If you are not familiar with this have a look at <comment>https://developer.matomo.org/guides/working-with-piwiks-ui</comment>'
        ));
    }
}
