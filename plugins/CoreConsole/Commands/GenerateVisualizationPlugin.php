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

/**
 */
class GenerateVisualizationPlugin extends GeneratePlugin
{
    protected function configure()
    {
        $this->setName('generate:visualizationplugin')
            ->setDescription('Generates a new visualization plugin including all needed files')
            ->addRequiredValueOption('name', null, 'Plugin name ([a-Z0-9_-])')
            ->addRequiredValueOption('visualizationname', null, 'Visualization name ([a-Z0-9])')
            ->addRequiredValueOption('description', null, 'Plugin description, max 150 characters')
            ->addOptionalValueOption('pluginversion', null, 'Plugin version')
            ->addNoValueOption('overwrite', null, 'Generate even if plugin directory already exists.')
            ->addOptionalValueOption('full', null, 'If a value is set, an API and a Controller will be created as well. Option is only available for creating plugins, not for creating themes.');
    }

    protected function doExecute(): int
    {
        $pluginName  = $this->getPluginName();
        $this->checkAndUpdateRequiredPiwikVersion($pluginName);
        $description = $this->getPluginDescription();
        $version     = $this->getPluginVersion();
        $visualizationName = $this->getVisualizationName();

        $this->generatePluginFolder($pluginName);

        $exampleFolder = Manager::getPluginDirectory('ExampleVisualization');
        $replace = array(
            'SimpleTable'  => $visualizationName,
            'simpleTable'  => lcfirst($visualizationName),
            'Simple Table' => $this->makeTranslationIfPossible($pluginName, $visualizationName),
            'ExampleVisualization'            => $pluginName,
            'ExampleVisualizationDescription' => $description
        );

        $this->copyTemplateToPlugin($exampleFolder, $pluginName, $replace, $allowListFiles = array());

        $this->writeSuccessMessage(array(
             sprintf('Visualization plugin %s %s generated.', $pluginName, $version),
             'Enjoy!'
        ));

        return self::SUCCESS;
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    private function getVisualizationName()
    {
        $input = $this->getInput();
        $validate = function ($visualizationName) {
            if (empty($visualizationName)) {
                throw new \RuntimeException('You have to enter a visualization name');
            }

            if (!ctype_alnum($visualizationName)) {
                throw new \RuntimeException(sprintf('The visualization name %s is not valid (only AlNum allowed)', $visualizationName));
            }

            return $visualizationName;
        };

        $visualizationName = $input->getOption('visualizationname');

        if (empty($visualizationName)) {
            $visualizationName = $this->askAndValidate('Enter a visualization name (only AlNum allowed): ', $validate);
        } else {
            $validate($visualizationName);
        }

        $visualizationName = ucfirst($visualizationName);

        return $visualizationName;
    }
}
