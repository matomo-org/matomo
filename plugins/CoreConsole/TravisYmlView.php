<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CoreConsole;

use Piwik\View;
use Symfony\Component\Console\Output\OutputInterface;
use Exception;

/**
 * View class for the travis.yml.twig template file. Generates the contents for a .travis.yml file.
 */
class TravisYmlView extends View
{
    /**
     * The .travis.yml section names that are overwritten by this command.
     * 
     * @var string[]
     */
    private static $travisYmlSectionNames = array(
        'php',
        'language',
        'script',
        'before_install',
        'install',
        'before_script',
        'after_script',
        'after_success'
    );

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct("@CoreConsole/travis.yml");
    }

    /**
     * Parse existing data in a .travis.yml file that should be preserved in the output .travis.yml.
     * Includes comments.
     * 
     * @var string $existingYmlPath The path to the existing .travis.yml file.
     */
    public function processExistingTravisYml($existingYmlPath)
    {
        $existingYamlText = file_get_contents($existingYmlPath);
        foreach ($this->getRootSectionsFromYaml($existingYamlText) as $sectionName => $offset) {
            $section = $this->getRootSectionText($existingYamlText, $offset);
            if ($sectionName == 'env') {
                $this->existingEnv = $section;
            } else if ($sectionName == 'matrix') {
                $this->existingMatrix = $section;
            } else if (!in_array($sectionName, self::$travisYmlSectionNames)) {
                $this->extraSections .= "\n\n$sectionName:" . $section;
            }
        }
    }

    /**
     * Configures the view for generation.
     *
     * @param string|null $targetPlugin The plugin target or `null` if generating for core.
     * @param string|null $artifactsPass The password for the builds artifacts server. Encrypted in output.
     * @param string $generateYmlCommand The command to use in travis when checking if a .travis.yml file is out
     *                                   of date.
     * @param OutputInterface $output OutputInterface to output warnings and the like.
     */
    public function configure($targetPlugin, $artifactsPass, $githubToken, $generateYmlCommand, OutputInterface $output)
    {
        $this->pluginName = $targetPlugin;

        if (empty($this->existingEnv)) {
            $artifactsPass = $artifactsPass;
            if (!empty($artifactsPass)) {
                $this->artifactsPass = $this->travisEncrypt("ARTIFACTS_PASS=" . $artifactsPass, $output);
            }

            $githubToken = $githubToken;
            if (!empty($githubToken)) {
                $this->githubToken = $this->travisEncrypt("GITHUB_USER_TOKEN=" . $githubToken, $output);
            }
        } else {
            $output->writeln("<info>Existing .yml files found, ignoring global variables specified on command line.</info>");
        }

        list($this->testsToRun, $this->testsToExclude) = $this->getTestsToRun();

        $this->consoleCommand = $generateYmlCommand;
    }

    /**
     * Extracts the name and offset of all root elements of a YAML document. This method does this by
     * checking for text that starts at the beginning of a line and ends with a ':'.
     *
     * @param string $yamlText The YAML text to search through.
     * @return array Array mapping string section names with the starting offset of the text in the YAML.
     */
    private function getRootSectionsFromYaml($yamlText)
    {
        preg_match_all("/^[a-zA-Z_]+:/m", $yamlText, $allMatches, PREG_OFFSET_CAPTURE);

        $result = array();
        foreach ($allMatches[0] as $match) {
            $matchLength = strlen($match[0]);
            $sectionName = substr($match[0], 0, $matchLength - 1);

            $result[$sectionName] = $match[1] + $matchLength;
        }
        return $result;
    }

    /**
     * Gets the text of a root YAML element in a YAML doc using the name of the element and the starting
     * offset of the element's text. This is accomplished by searching for the first line that doesn't
     * start with whitespace after the given offset and using the text between the given offset and the
     * line w/o starting whitespace.
     *
     * @param string $yamlText The YAML text to search through.
     * @param int $offset The offset start of the YAML text (does not include the element name and colon, ie
     *                    the offset is after `'element:'`).
     * @return string
     */
    private function getRootSectionText($yamlText, $offset)
    {
        preg_match("/^[^\s]/m", $yamlText, $endMatches, PREG_OFFSET_CAPTURE, $offset);

        $endPos = isset($endMatches[0][1]) ? $endMatches[0][1] : strlen($yamlText);

        return substr($yamlText, $offset, $endPos - $offset);
    }

    private function travisEncrypt($data, OutputInterface $output)
    {
        $output->writeln("Encrypting \"$data\"...");

        $command = "travis encrypt \"$data\"";

        // change dir to target plugin since plugin will be in its own git repo
        if (!empty($this->pluginName)) {
            $command = "cd \"" . $this->getPluginRootFolder() . "\" && " . $command;
        }

        exec($command, $output, $returnCode);
        if ($returnCode !== 0) {
            throw new Exception("Cannot encrypt \"$data\" for travis! Please make sure you have the travis command line "
                              . "utility installed (see http://blog.travis-ci.com/2013-01-14-new-client/).\n\n"
                              . "return code: $returnCode\n\n"
                              . "travis output:\n\n" . implode("\n", $output));
        }

        if (empty($output)) {
            throw new Exception("Cannot parse travis encrypt output:\n\n" . implode("\n", $output));
        }

        // when not executed from a command line travis encrypt will return only the encrypted data
        $encryptedData = $output[0];
        if (substr($encryptedData, 0, 1) == '"') {
            $encryptedData = substr($encryptedData, 1);
        }
        if (substr($encryptedData, -1) == '"') {
            $encryptedData = substr($encryptedData, 0, strlen($encryptedData) - 1);
        }

        return $encryptedData;
    }

    private function getTestsToRun()
    {
        $testsToRun = array();
        $testsToExclude = array();

        if ($this->isTargetPluginContainsPluginTests()) {
            $testsToRun[] = array('name' => 'PluginTests',
                                  'vars' => "MYSQL_ADAPTER=PDO_MYSQL");
            $testsToRun[] = array('name' => 'PluginTests',
                                  'vars' => "MYSQL_ADAPTER=PDO_MYSQL TEST_AGAINST_CORE=latest_stable");

            $testsToExclude[] = array('description' => 'execute latest stable tests only w/ PHP 5.5',
                                      'php' => '5.3.3',
                                      'env' => 'TEST_SUITE=PluginTests MYSQL_ADAPTER=PDO_MYSQL TEST_AGAINST_CORE=latest_stable');
            $testsToExclude[] = array('php' => '5.4',
                                      'env' => 'TEST_SUITE=PluginTests MYSQL_ADAPTER=PDO_MYSQL TEST_AGAINST_CORE=latest_stable');
        }
        if ($this->isTargetPluginContainsUITests()) {
            $testsToRun[] = array('name' => 'UITests',
                                  'vars' => "MYSQL_ADAPTER=PDO_MYSQL");

            $testsToExclude[] = array('description' => 'execute UI tests only w/ PHP 5.5',
                                      'php' => '5.3.3',
                                      'env' => 'TEST_SUITE=UITests MYSQL_ADAPTER=PDO_MYSQL');
            $testsToExclude[] = array('php' => '5.4',
                                      'env' => 'TEST_SUITE=UITests MYSQL_ADAPTER=PDO_MYSQL');
        }

        if (!empty($this->pluginName)
            && empty($testsToRun)
        ) {
            throw new Exception("No tests to run for this plugin, aborting .travis.yml generation.");
        }

        return array($testsToRun, $testsToExclude);
    }

    private function isTargetPluginContainsPluginTests()
    {
        $pluginPath = $this->getPluginRootFolder();
        return $this->doesFolderContainPluginTests($pluginPath . "/tests")
            || $this->doesFolderContainPluginTests($pluginPath . "/Test");
    }

    private function doesFolderContainPluginTests($folderPath)
    {
        $testFiles = array_merge(glob($folderPath . "/**/*Test.php"), glob($folderPath . "/*Test.php"));
        return !empty($testFiles);
    }

    private function isTargetPluginContainsUITests()
    {
        $pluginPath = $this->getPluginRootFolder();
        return $this->doesFolderContainUITests($pluginPath . "/tests")
            || $this->doesFolderContainUITests($pluginPath . "/Test");
    }

    private function doesFolderContainUITests($folderPath)
    {
        $testFiles = array_merge(glob($folderPath . "/**/*_spec.js"), glob($folderPath . "/*_spec.js"));
        return !empty($testFiles);
    }

    private function getPluginRootFolder()
    {
        return PIWIK_INCLUDE_PATH . "/plugins/{$this->pluginName}";
    }
}