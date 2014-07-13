<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreConsole\Commands;


use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 */
class GenerateVisualizationPlugin extends GeneratePlugin
{
    protected function configure()
    {
        $this->setName('generate:visualizationplugin')
            ->setDescription('Generates a new visualization plugin including all needed files')
            ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Plugin name ([a-Z0-9_-])')
            ->addOption('visualizationname', null, InputOption::VALUE_REQUIRED, 'Visualization name ([a-Z0-9])')
            ->addOption('description', null, InputOption::VALUE_REQUIRED, 'Plugin description, max 150 characters')
            ->addOption('pluginversion', null, InputOption::VALUE_OPTIONAL, 'Plugin version')
            ->addOption('full', null, InputOption::VALUE_OPTIONAL, 'If a value is set, an API and a Controller will be created as well. Option is only available for creating plugins, not for creating themes.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pluginName  = $this->getPluginName($input, $output);
        $description = $this->getPluginDescription($input, $output);
        $version     = $this->getPluginVersion($input, $output);
        $visualizationName = $this->getVisualizationName($input, $output);

        $this->generatePluginFolder($pluginName);

        $exampleFolder = PIWIK_INCLUDE_PATH . '/plugins/ExampleVisualization';
        $replace = array(
            'SimpleTable'  => $visualizationName,
            'simpleTable'  => lcfirst($visualizationName),
            'Simple Table' => $visualizationName,
            'ExampleVisualization'            => $pluginName,
            'ExampleVisualizationDescription' => $description
        );

        $this->copyTemplateToPlugin($exampleFolder, $pluginName, $replace, $whitelistFiles = array());

        $this->writeSuccessMessage($output, array(
             sprintf('Visualization plugin %s %s generated.', $pluginName, $version),
             'Enjoy!'
        ));
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return string
     * @throws \RunTimeException
     */
    private function getVisualizationName(InputInterface $input, OutputInterface $output)
    {
        $self = $this;

        $validate = function ($visualizationName) use ($self) {
            if (empty($visualizationName)) {
                throw new \RunTimeException('You have to enter a visualization name');
            }

            if (!ctype_alnum($visualizationName)) {
                throw new \RunTimeException(sprintf('The visualization name %s is not valid', $visualizationName));
            }

            return $visualizationName;
        };

        $visualizationName = $input->getOption('visualizationname');

        if (empty($visualizationName)) {
            $dialog = $this->getHelperSet()->get('dialog');
            $visualizationName = $dialog->askAndValidate($output, 'Enter a visualization name: ', $validate);
        } else {
            $validate($visualizationName);
        }

        $visualizationName = ucfirst($visualizationName);

        return $visualizationName;
    }


}
