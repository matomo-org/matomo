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

use Piwik\Common;
use Piwik\Console\Command;
use Piwik\Filesystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @package CoreConsole
 */
class GenerateApi extends Command
{
    protected function configure()
    {
        $this->setName('generate:api')
             ->setDescription('Adds an API to an existing plugin')
             ->addOption('pluginname', null, InputOption::VALUE_REQUIRED, 'Plugin name ([a-Z0-9_-])');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $pluginName = $this->getPluginName($input, $output);

        $this->generatePluginFolder($pluginName);
        $this->generatePluginApi($pluginName);

        $this->writeSuccessMessage($output, array(
            sprintf('API.php for %s generated.', $pluginName),
            'You can now start adding API methods',
            'Enjoy!'
        ));
    }

    /**
     * @param $pluginName
     * @return string
     */
    public function getPluginPath($pluginName)
    {
        $pluginPath = PIWIK_INCLUDE_PATH . '/plugins/' . ucfirst($pluginName);
        return $pluginPath;
    }

    private function generatePluginFolder($pluginName)
    {
        $pluginPath = $this->getPluginPath($pluginName);
        Filesystem::mkdir($pluginPath, true);
    }

    private function writePluginFile($pluginName, $fileName, $content)
    {
        $pluginPath = $this->getPluginPath($pluginName);

        file_put_contents($pluginPath . $fileName, $content);
    }

    /**
     * @param $pluginName
     */
    private function generatePluginApi($pluginName)
    {
        $template   = file_get_contents(__DIR__ . '/templates/PluginApiTemplate.php');
        $pluginFile = str_replace('PLUGINNAME', $pluginName, $template);
        $this->writePluginFile($pluginName, '/API.php', $pluginFile);
    }

    private function getPluginNamesHavingNoApi()
    {
        $pluginDirs = \_glob(PIWIK_INCLUDE_PATH . '/plugins/*', GLOB_ONLYDIR);

        $pluginNames = array();
        foreach ($pluginDirs as $pluginDir) {
            if (!file_exists($pluginDir . '/API.php')) {
                $pluginNames[] = basename($pluginDir);;
            }
        }

        return $pluginNames;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return array
     * @throws \RunTimeException
     */
    private function getPluginName(InputInterface $input, OutputInterface $output)
    {
        $pluginNames = $this->getPluginNamesHavingNoApi();

        $validate = function ($pluginName) use ($pluginNames) {
            if (!in_array($pluginName, $pluginNames)) {
                throw new \InvalidArgumentException('You have to enter the name of an existing plugin which does not already have an API');
            }

            return $pluginName;
        };

        $pluginName = $input->getOption('pluginname');

        if (empty($pluginName)) {
            $dialog     = $this->getHelperSet()->get('dialog');
            $pluginName = $dialog->askAndValidate($output, 'Enter the name of your plugin: ', $validate, false, null, $pluginNames);
        } else {
            $validate($pluginName);
        }

        $pluginName = ucfirst($pluginName);

        return $pluginName;
    }


}