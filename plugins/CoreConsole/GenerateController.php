<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik_Plugins
 * @package CoreConsole
 */

namespace Piwik\Plugins\CoreConsole;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package CoreConsole
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

        $exampleFolder  = PIWIK_INCLUDE_PATH . '/plugins/ExamplePluginTemplate';
        $replace        = array('ExamplePluginTemplate' => $pluginName);
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
     * @throws \RunTimeException
     */
    private function getPluginName(InputInterface $input, OutputInterface $output)
    {
        $pluginNames = $this->getPluginNamesHavingNotSpecificFile('Controller.php');

        $validate = function ($pluginName) use ($pluginNames) {
            if (!in_array($pluginName, $pluginNames)) {
                throw new \InvalidArgumentException('You have to enter the name of an existing plugin which does not already have a Controller');
            }

            return $pluginName;
        };

        $pluginName = $input->getOption('pluginname');

        if (empty($pluginName)) {
            $dialog = $this->getHelperSet()->get('dialog');
            $pluginName = $dialog->askAndValidate($output, 'Enter the name of your plugin: ', $validate, false, null, $pluginNames);
        } else {
            $validate($pluginName);
        }

        $pluginName = ucfirst($pluginName);

        return $pluginName;
    }

}