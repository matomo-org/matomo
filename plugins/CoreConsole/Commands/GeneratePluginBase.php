<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreConsole\Commands;


use Piwik\Filesystem;
use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 */
abstract class GeneratePluginBase extends ConsoleCommand
{
    public function getPluginPath($pluginName)
    {
        return PIWIK_INCLUDE_PATH . '/plugins/' . ucfirst($pluginName);
    }

    private function createFolderWithinPluginIfNotExists($pluginName, $folder)
    {
        $pluginPath = $this->getPluginPath($pluginName);

        if (!file_exists($pluginName . $folder)) {
            Filesystem::mkdir($pluginPath . $folder);
        }
    }

    protected function createFileWithinPluginIfNotExists($pluginName, $fileName, $content)
    {
        $pluginPath = $this->getPluginPath($pluginName);

        if (!file_exists($pluginPath . $fileName)) {
            file_put_contents($pluginPath . $fileName, $content);
        }
    }

    /**
     * @param string $templateFolder  full path like /home/...
     * @param string $pluginName
     * @param array $replace         array(key => value) $key will be replaced by $value in all templates
     * @param array $whitelistFiles  If not empty, only given files/directories will be copied.
     *                               For instance array('/Controller.php', '/templates', '/templates/index.twig')
     */
    protected function copyTemplateToPlugin($templateFolder, $pluginName, array $replace = array(), $whitelistFiles = array())
    {
        $replace['PLUGINNAME'] = $pluginName;

        $files = array_merge(
                Filesystem::globr($templateFolder, '*'),
                // Also copy files starting with . such as .gitignore
                Filesystem::globr($templateFolder, '.*')
        );

        foreach ($files as $file) {
            $fileNamePlugin = str_replace($templateFolder, '', $file);

            if (!empty($whitelistFiles) && !in_array($fileNamePlugin, $whitelistFiles)) {
                continue;
            }

            if (is_dir($file)) {
                $this->createFolderWithinPluginIfNotExists($pluginName, $fileNamePlugin);
            } else {
                $template = file_get_contents($file);
                foreach ($replace as $key => $value) {
                    $template = str_replace($key, $value, $template);
                }

                foreach ($replace as $key => $value) {
                    $fileNamePlugin = str_replace($key, $value, $fileNamePlugin);
                }

                $this->createFileWithinPluginIfNotExists($pluginName, $fileNamePlugin, $template);
            }

        }
    }

    protected function getPluginNames()
    {
        $pluginDirs = \_glob(PIWIK_INCLUDE_PATH . '/plugins/*', GLOB_ONLYDIR);

        $pluginNames = array();
        foreach ($pluginDirs as $pluginDir) {
            $pluginNames[] = basename($pluginDir);
        }

        return $pluginNames;
    }

    protected function getPluginNamesHavingNotSpecificFile($filename)
    {
        $pluginDirs = \_glob(PIWIK_INCLUDE_PATH . '/plugins/*', GLOB_ONLYDIR);

        $pluginNames = array();
        foreach ($pluginDirs as $pluginDir) {
            if (!file_exists($pluginDir . '/' . $filename)) {
                $pluginNames[] = basename($pluginDir);
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
    protected function askPluginNameAndValidate(InputInterface $input, OutputInterface $output, $pluginNames, $invalidArgumentException)
    {
        $validate = function ($pluginName) use ($pluginNames, $invalidArgumentException) {
            if (!in_array($pluginName, $pluginNames)) {
                throw new \InvalidArgumentException($invalidArgumentException);
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
