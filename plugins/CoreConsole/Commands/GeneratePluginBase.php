<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreConsole\Commands;

use Piwik\Development;
use Piwik\Filesystem;
use Piwik\Plugin\ConsoleCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

abstract class GeneratePluginBase extends ConsoleCommand
{
    public function getPluginPath($pluginName)
    {
        return PIWIK_INCLUDE_PATH . '/plugins/' . ucfirst($pluginName);
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
     * @return string  Either the generated translation key or the original text if a different translation for this
     *                 generated translation key already exists.
     */
    protected function makeTranslationIfPossible($pluginName, $translatedText)
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

        $key = $this->buildTranslationKey($translatedText);

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

        $pluginName = ucfirst($pluginName);

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

}
