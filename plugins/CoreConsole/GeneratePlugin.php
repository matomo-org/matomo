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
class GeneratePlugin extends Command
{
    protected function configure()
    {
        $this->setName('generate:plugin')
             ->setAliases(array('generate:theme'))
             ->setDescription('Generates a new plugin/theme including all needed files')
             ->addOption('name', null, InputOption::VALUE_REQUIRED, 'Plugin name ([a-Z0-9_-])')
             ->addOption('description', null, InputOption::VALUE_REQUIRED, 'Plugin description, max 150 characters.')
             ->addOption('pluginversion', null, InputOption::VALUE_OPTIONAL, 'Plugin version');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $isTheme     = $this->isTheme($input);
        $pluginName  = $this->getPluginName($input, $output);
        $description = $this->getPluginDescription($input, $output);
        $version     = $this->getPluginVersion($input, $output);

        $this->generatePluginFolder($pluginName);
        $this->generatePluginJson($pluginName, $version, $description, $isTheme);
        $this->generatePluginFiles($isTheme, $pluginName);

        $output->writeln(sprintf('Plugin %s %s generated', $pluginName, $version));
    }

    /**
     * @param InputInterface $input
     * @return bool
     */
    private function isTheme(InputInterface $input)
    {
        $commandName = $input->getFirstArgument();

        return false !== strpos($commandName, 'theme');
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

    /**
     * @param $pluginName
     * @param $version
     * @param $description
     * @param $isTheme
     * @return array
     */
    private function generatePluginJson($pluginName, $version, $description, $isTheme)
    {
        $pluginJson = array(
            'name'        => $pluginName,
            'version'     => $version,
            'description' => $description,
            'theme'       => $isTheme
        );

        if ($isTheme) {
            $pluginJson['stylesheet'] = 'stylesheets/theme.less';
        }

        $content = json_encode($pluginJson);
        $content = str_replace('",', "\",\n ", $content);
        $this->writePluginFile($pluginName, '/plugin.json', $content);

        return $pluginJson;
    }

    private function writePluginFile($pluginName, $fileName, $content)
    {
        $pluginPath = $this->getPluginPath($pluginName);

        file_put_contents($pluginPath . $fileName, $content);
    }

    /**
     * @param $isTheme
     * @param $pluginName
     */
    private function generatePluginFiles($isTheme, $pluginName)
    {
        if ($isTheme) {
            $pluginPath = $this->getPluginPath($pluginName);
            Filesystem::mkdir($pluginPath . '/stylesheets', false);
            $this->writePluginFile($pluginName, '/stylesheets/theme.less', '');
        } else {
            $template   = file_get_contents(__DIR__ . '/templates/PluginTemplate.php');
            $pluginFile = str_replace('PLUGINNAME', $pluginName, $template);
            $this->writePluginFile($pluginName, '/' . $pluginName . '.php', $pluginFile);
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return array
     * @throws \RunTimeException
     */
    private function getPluginName(InputInterface $input, OutputInterface $output)
    {
        $self = $this;

        $validate = function ($pluginName) use ($self) {
            if (empty($pluginName)) {
                throw new \RunTimeException('You have to enter a plugin name');
            }

            if (!Filesystem::isValidFilename($pluginName)) {
                throw new \RunTimeException(sprintf('The plugin name %s is not valid', $pluginName));
            }

            $pluginPath = $self->getPluginPath($pluginName);

            if (file_exists($pluginPath)) {
                throw new \RunTimeException('A plugin with this name already exists');
            }
        };

        $pluginName = $input->getOption('name');

        if (empty($pluginName)) {
            $dialog = $this->getHelperSet()->get('dialog');
            $pluginName = $dialog->askAndValidate($output, 'Enter a plugin name: ', $validate);
        } else {
            $validate($pluginName);
        }

        $pluginName = ucfirst($pluginName);

        return $pluginName;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     * @throws \RunTimeException
     */
    private function getPluginDescription(InputInterface $input, OutputInterface $output)
    {
        $validate = function ($description) {
            if (empty($description)) {
                throw new \RunTimeException('You have to enter a description');
            }
            if (150 < strlen($description)) {
                throw new \RunTimeException('Description is too long, max 150 characters allowed.');
            }
        };

        $description = $input->getOption('description');

        if (empty($description)) {
            $dialog      = $this->getHelperSet()->get('dialog');
            $description = $dialog->askAndValidate($output, 'Enter a plugin description: ', $validate);
        } else {
            $validate($description);
        }

        return $description;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return mixed
     */
    protected function getPluginVersion(InputInterface $input, OutputInterface $output)
    {
        $version = $input->getOption('pluginversion');

        if (is_null($version)) {
            $dialog  = $this->getHelperSet()->get('dialog');
            $version = $dialog->ask($output, 'Enter a plugin version number (default to 0.1.0): ', '0.1.0');
        }

        return $version;
    }

}