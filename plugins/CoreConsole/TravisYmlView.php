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
     * The names of .travis.yml sections that can be extended w/ custom steps by plugins. Twig templates
     * in the plugins/PluginName/tests/travis directory can be used to insert travis commands at the
     * beginning or end of a section. For example, before_install.before.yml will add steps
     * at the beginning of the before_install: section.
     *
     * @var string[]
     */
    private static $travisYmlExtendableSectionNames = array(
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
     * Sets the name of plugin the generated .travis.yml file is for.
     *
     * @param string $pluginName ie, ExamplePlugin, UserSettings, etc.
     */
    public function setPlugin($pluginName)
    {
        $this->pluginName = $pluginName;

        $customTravisBuildSteps = array();

        foreach (self::$travisYmlExtendableSectionNames as $name) {
            $customTravisBuildSteps[$name] = array();

            $beforeStepsTemplate = $this->getPathToCustomTravisStepsFile($name, 'before');
            if (file_exists($beforeStepsTemplate)) {
                $customTravisBuildSteps[$name]['before'] = $this->changeIndent(file_get_contents($beforeStepsTemplate), '  ');
            }

            $afterStepsTemplate = $this->getPathToCustomTravisStepsFile($name, 'after');
            if (file_exists($afterStepsTemplate)) {
                $customTravisBuildSteps[$name]['after'] = $this->changeIndent(file_get_contents($afterStepsTemplate), '  ');
            }
        }

        $this->customTravisBuildSteps = $customTravisBuildSteps;
    }

    /**
     * Set extra global environment variables that should be set in the generated .travis.yml file. The entries
     * should be whole statements like `"MY_VAR=myvalue"` or `"secure: mysecurevalue"`.
     *
     * @param string[] $extraVars
     */
    public function setExtraGlobalEnvVars($extraVars)
    {
        $this->extraGlobalEnvVars = $extraVars;
    }

    /**
     * Sets the self-referential command that will generate the .travis.yml file on travis.
     *
     * @param string $consoleCommand ie, `"./console generate:travis-yml ..."`
     */
    public function setGenerateYmlCommand($consoleCommand)
    {
        $this->consoleCommand = addslashes($consoleCommand);
    }

    /**
     * Sets the PHP versions to run tests against in travis.
     *
     * @param string[] $phpVersions ie, `array("5.3.3", "5.4", "5.5")`.
     */
    public function setPhpVersions($phpVersions)
    {
        $this->phpVersions = $phpVersions;
    }

    /**
     * Renders the view. See {@link Piwik\View::render()}.
     *
     * @return string
     */
    public function render()
    {
        list($this->testsToRun, $this->testsToExclude) = $this->getTestsToRun();

        return parent::render();
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
            $testsToExclude[] = array('php' => '5.6',
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

    private function changeIndent($text, $newIndent)
    {
        $text = trim($text);

        return preg_replace("/^\\s*/", $newIndent, $text);
    }

    public function getPluginRootFolder()
    {
        return PIWIK_INCLUDE_PATH . "/plugins/{$this->pluginName}";
    }

    private function getPathToCustomTravisStepsFile($sectionName, $type)
    {
        return $this->getPluginRootFolder() . "/tests/travis/$sectionName.$type.yml";
    }
}