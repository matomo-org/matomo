<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Common;
use Piwik\Development;
use Piwik\Filesystem;
use Piwik\Plugin\ConsoleCommand;
use Piwik\Plugin\Dependency;
use Piwik\Plugin\Manager;
use Piwik\Version;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Piwik\SettingsPiwik;
use Piwik\Exception\NotGitInstalledException;

abstract class GeneratePluginBase extends ConsoleCommand
{
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->throwErrorIfNotGitInstalled();
    }

    public function isEnabled()
    {
        return Development::isEnabled();
    }

    public function getPluginPath($pluginName)
    {
        return Manager::getPluginDirectory($pluginName);
    }

    private function createFolderWithinPluginIfNotExists($pluginNameOrCore, $folder)
    {
        if ($pluginNameOrCore === 'core') {
            $pluginPath = $this->getPathToCore();
        } else {
            $pluginPath = $this->getPluginPath($pluginNameOrCore);
        }

        if (!file_exists($pluginPath . $folder)) {
            Filesystem::mkdir($pluginPath . $folder);
        }
    }

    protected function createFileWithinPluginIfNotExists($pluginNameOrCore, $fileName, $content)
    {
        if ($pluginNameOrCore === 'core') {
            $pluginPath = $this->getPathToCore();
        } else {
            $pluginPath = $this->getPluginPath($pluginNameOrCore);
        }

        if (!file_exists($pluginPath . $fileName)) {
            file_put_contents($pluginPath . $fileName, $content);
        }
    }

    /**
     * Creates a lang/en.json within the plugin in case it does not exist yet and adds a translation for the given
     * text.
     *
     * @param $pluginName
     * @param $translatedText
     * @param string $translationKey Optional, by default the key will be generated automatically
     * @return string  Either the generated translation key or the original text if a different translation for this
     *                 generated translation key already exists.
     */
    protected function makeTranslationIfPossible($pluginName, $translatedText, $translationKey = '')
    {
        $defaultLang = array($pluginName => array());

        $this->createFolderWithinPluginIfNotExists($pluginName, '/lang');
        $this->createFileWithinPluginIfNotExists($pluginName, '/lang/en.json', $this->toJson($defaultLang));

        $langJsonPath = $this->getPluginPath($pluginName) . '/lang/en.json';
        $translations = file_get_contents($langJsonPath);
        $translations = json_decode($translations, true);

        if (empty($translations[$pluginName])) {
            $translations[$pluginName] = array();
        }

        if (!empty($translationKey)) {
            $key = $translationKey;
        } else {
            $key = $this->buildTranslationKey($translatedText);
        }

        if (array_key_exists($key, $translations[$pluginName])) {
            // we do not want to overwrite any existing translations
            if ($translations[$pluginName][$key] === $translatedText) {
                return $pluginName . '_' . $key;
            }

            return $translatedText;
        }

        $translations[$pluginName][$key] = $this->removeNonJsonCompatibleCharacters($translatedText);

        file_put_contents($langJsonPath, $this->toJson($translations));

        return $pluginName . '_' . $key;
    }

    protected function checkAndUpdateRequiredPiwikVersion($pluginName, OutputInterface $output)
    {
        $pluginJsonPath     = $this->getPluginPath($pluginName) . '/plugin.json';
        $relativePluginJson = Manager::getPluginDirectory($pluginName) . '/plugin.json';

        if (!file_exists($pluginJsonPath) || !is_writable($pluginJsonPath)) {
            return;
        }

        $pluginJson = file_get_contents($pluginJsonPath);
        $pluginJson = json_decode($pluginJson, true);

        if (empty($pluginJson)) {
            return;
        }

        if (empty($pluginJson['require'])) {
            $pluginJson['require'] = array();
        }

        $piwikVersion     = Version::VERSION;
        $nextMajorVersion = (int) substr($piwikVersion, 0, strpos($piwikVersion, '.')) + 1;
        $secondPartPiwikVersionRequire = ',<' . $nextMajorVersion . '.0.0-b1';
        if (false === strpos($piwikVersion, '-')) {
            // see https://github.com/composer/composer/issues/4080 we need to specify -stable otherwise it would match
            // $piwikVersion-dev meaning it would also match all pre-released. However, we only want to match a stable
            // release
            $piwikVersion.= '-stable';
        }

        $newRequiredVersion = sprintf('>=%s,<%d.0.0-b1', $piwikVersion, $nextMajorVersion);

        if (!empty($pluginJson['require']['piwik'])) {
            $pluginJson['require']['matomo'] = $pluginJson['require']['piwik'];
            unset($pluginJson['require']['piwik']);
        }

        if (!empty($pluginJson['require']['matomo'])) {
            $requiredVersion = trim($pluginJson['require']['matomo']);

            if ($requiredVersion === $newRequiredVersion) {
                // there is nothing to updated
                return;
            }

            // our generated versions look like ">=2.25.4,<3.0.0-b1".
            // We only updated the Piwik version in the first part if the piwik version looks like that or if it has only
            // one piwik version defined. In all other cases, eg user uses || etc we do not update it as user has customized
            // the piwik version.

            foreach (['<>','!=', '<=','==', '^'] as $comparison) {
                if (strpos($requiredVersion, $comparison) === 0) {
                    // user is using custom piwik version require, we do not overwrite anything.
                    return;
                }
            }

            if (strpos($requiredVersion, '||') !== false || strpos($requiredVersion, ' ') !== false) {
                // user is using custom piwik version require, we do not overwrite anything.
                return;
            }

            $requiredPiwikVersions = explode(',', (string) $requiredVersion);
            $numRequiredPiwikVersions = count($requiredPiwikVersions);

            if ($numRequiredPiwikVersions > 2) {
                // user is using custom piwik version require, we do not overwrite anything.
                return;
            }

            if ($numRequiredPiwikVersions === 2 &&
                !Common::stringEndsWith($requiredVersion, $secondPartPiwikVersionRequire)) {
                // user is using custom piwik version require, we do not overwrite anything
                return;
            }

            // if only one piwik version is defined we update it to make sure it does now specify an upper version limit

            $dependency     = new Dependency();
            $missingVersion = $dependency->getMissingVersions($piwikVersion, $requiredVersion);

            if (!empty($missingVersion)) {
                $msg = sprintf('We cannot generate this component as the plugin "%s" requires the Piwik version "%s" in the file "%s". Generating this component requires "%s". If you know your plugin is compatible with your Piwik version remove the required Piwik version in "%s" and try to execute this command again.', $pluginName, $requiredVersion, $relativePluginJson, $newRequiredVersion, $relativePluginJson);

                throw new \Exception($msg);
            }

            $output->writeln('');
            $output->writeln(sprintf('<comment>We have updated the required Piwik version from "%s" to "%s" in "%s".</comment>', $requiredVersion, $newRequiredVersion, $relativePluginJson));
        } else {
            $output->writeln('');
            $output->writeln(sprintf('<comment>We have updated your "%s" to require the Piwik version "%s".</comment>', $relativePluginJson, $newRequiredVersion));
        }

        $pluginJson['require']['matomo'] = $newRequiredVersion;
        file_put_contents($pluginJsonPath, $this->toJson($pluginJson));
    }

    private function toJson($value)
    {
        if (defined('JSON_PRETTY_PRINT')) {

            return json_encode($value, JSON_PRETTY_PRINT);
        }

        return json_encode($value);
    }

    private function buildTranslationKey($translatedText)
    {
        $translatedText = preg_replace('/(\s+)/', '', $translatedText);
        $translatedText = preg_replace("/[^A-Za-z0-9]/", '', $translatedText);
        $translatedText = trim($translatedText);

        return $this->removeNonJsonCompatibleCharacters($translatedText);
    }

    private function removeNonJsonCompatibleCharacters($text)
    {
        return preg_replace('/[^(\x00-\x7F)]*/', '', $text);
    }

    /**
     * Copies the given method and all needed use statements into an existing class. The target class name will be
     * built based on the given $replace argument.
     * @param string $sourceClassName
     * @param string $methodName
     * @param array $replace
     */
    protected function copyTemplateMethodToExisitingClass($sourceClassName, $methodName, $replace)
    {
        $targetClassName = $this->replaceContent($replace, $sourceClassName);

        if (Development::methodExists($targetClassName, $methodName)) {
            // we do not want to add the same method twice
            return;
        }

        Development::checkMethodExists($sourceClassName, $methodName, 'Cannot copy template method: ');

        $targetClass = new \ReflectionClass($targetClassName);
        $file        = new \SplFileObject($targetClass->getFileName());

        $methodCode = Development::getMethodSourceCode($sourceClassName, $methodName);
        $methodCode = $this->replaceContent($replace, $methodCode);
        $methodLine = $targetClass->getEndLine() - 1;

        $sourceUses = Development::getUseStatements($sourceClassName);
        $targetUses = Development::getUseStatements($targetClassName);
        $usesToAdd  = array_diff($sourceUses, $targetUses);
        if (empty($usesToAdd)) {
            $useCode = '';
        } else {
            $useCode = "\nuse " . implode("\nuse ", $usesToAdd) . "\n";
        }

        // search for namespace line before the class starts
        $useLine = 0;
        foreach (new \LimitIterator($file, 0, $targetClass->getStartLine()) as $index => $line) {
            if (0 === strpos(trim($line), 'namespace ')) {
                $useLine = $index + 1;
                break;
            }
        }

        $newClassCode = '';
        foreach(new \LimitIterator($file) as $index => $line) {
            if ($index == $methodLine) {
                $newClassCode .= $methodCode;
            }

            if (0 !== $useLine && $index == $useLine) {
                $newClassCode .= $useCode;
            }

            $newClassCode .= $line;
        }

        file_put_contents($targetClass->getFileName(), $newClassCode);
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
                $fileNamePlugin = $this->replaceContent($replace, $fileNamePlugin);
                $this->createFolderWithinPluginIfNotExists($pluginName, $fileNamePlugin);
            } else {
                $template = file_get_contents($file);
                $template = $this->replaceContent($replace, $template);

                $fileNamePlugin = $this->replaceContent($replace, $fileNamePlugin);

                $this->createFileWithinPluginIfNotExists($pluginName, $fileNamePlugin, $template);
            }

        }
    }

    protected function getPluginNames()
    {
        $pluginNames = array();
        foreach (Manager::getPluginsDirectories() as $pluginsDir) {
            $pluginDirs = \_glob($pluginsDir . '*', GLOB_ONLYDIR);

            foreach ($pluginDirs as $pluginDir) {
                $pluginNames[] = basename($pluginDir);
            }
        }

        return $pluginNames;
    }

    protected function getPluginNamesHavingNotSpecificFile($filename)
    {
        $pluginNames = array();
        foreach (Manager::getPluginsDirectories() as $pluginsDir) {
            $pluginDirs = \_glob($pluginsDir . '*', GLOB_ONLYDIR);

            foreach ($pluginDirs as $pluginDir) {
                if (!file_exists($pluginDir . '/' . $filename)) {
                    $pluginNames[] = basename($pluginDir);
                }
            }

        }
        return $pluginNames;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return string
     * @throws \RuntimeException
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

        return $pluginName;
    }

    private function getPathToCore()
    {
        $path = PIWIK_INCLUDE_PATH . '/core';
        return $path;
    }

    private function replaceContent($replace, $contentToReplace)
    {
        foreach ((array) $replace as $key => $value) {
            $contentToReplace = str_replace($key, $value, $contentToReplace);
        }

        return $contentToReplace;
    }

    protected function throwErrorIfNotGitInstalled()
    {
        if (!SettingsPiwik::isGitDeployment()) {
            $url = 'https://developer.matomo.org/guides/getting-started-part-1';
            throw new NotGitInstalledException("This development feature requires Matomo to be checked out from git. For more information please visit {$url}.");
        }
    }
}
