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
class GenerateController extends GeneratePluginBase
{
    protected function configure()
    {
        $this->setName('generate:controller')
            ->setDescription('Adds a Controller to an existing plugin')
            ->addOption('pluginname', null, InputOption::VALUE_REQUIRED, 'The name of an existing plugin which does not have a Controller yet');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pluginName = $this->getPluginName($input, $output);
        $this->checkAndUpdateRequiredPiwikVersion($pluginName, $output);

        $exampleFolder  = Manager::getPluginDirectory('ExamplePlugin');
        $replace        = array('ExamplePlugin' => $pluginName);
        $whitelistFiles = array('/Controller.php', '/templates', '/templates/index.twig');

        $this->copyTemplateToPlugin($exampleFolder, $pluginName, $replace, $whitelistFiles);

        $this->writeSuccessMessage($output, array(
             sprintf('Controller for %s generated.', $pluginName),
             'You can now start adding Controller actions',
             'Enjoy!'
        ));
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return array
     * @throws \RuntimeException
     */
    protected function getPluginName(InputInterface $input, OutputInterface $output)
    {
        $pluginNames = $this->getPluginNamesHavingNotSpecificFile('Controller.php');
        $invalidName = 'You have to enter the name of an existing plugin which does not already have a Controller';

        return $this->askPluginNameAndValidate($input, $output, $pluginNames, $invalidName);
    }

}
