<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\TestRunner\TravisYml;

use Piwik\View;

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
        'services',
        'language',
        'script',
        'before_install',
        'install',
        'before_script',
        'after_script',
        'after_success',
        'sudo'
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
        parent::__construct("@TestRunner/travis.yml");

        $this->setTestsToRun(array());
        $this->setTestsToExclude(array());
        $this->setTravisShScriptLocation("\$PIWIK_ROOT_DIR/tests/travis/travis.sh");
        $this->setTravisShCwd("tests/PHPUnit");
    }

    /**
     * Sets the generation mode. Can be 'core' for generating the core .travis.yml file,
     * 'plugin' for generating a plugin's .travis.yml file or 'piwik-tests-plugins'
     * for generating the .travis.yml file for the piwik-tests-plugins repo.
     *
     * @param string $mode
     */
    public function setGenerationMode($mode)
    {
        $this->generationMode = $mode;
    }

    /**
     * Sets the name of plugin the generated .travis.yml file is for.
     *
     * @param string $pluginName ie, ExamplePlugin, UserLanguage, etc.
     */
    public function setPlugin($pluginName)
    {
        $this->pluginName = $pluginName;
    }

    /**
     * Sets the path where custom travis.yml files should be searched for. The view will load
     * files in this directory that look like "XXX.before.yml" or "XXX.after.yml" where XXX
     * is the name of a .travis.yml section (eg, install.before.yml). The view will insert
     * the contents of these files in the correct positions in the generated output.
     *
     * @param string $path A path to a directory.
     */
    public function setPathToCustomTravisStepsFiles($path)
    {
        $customTravisBuildSteps = array();

        foreach (self::$travisYmlExtendableSectionNames as $name) {
            $customTravisBuildSteps[$name] = array();

            $beforeStepsTemplate = $this->getPathToCustomTravisStepsFile($path, $name, 'before');
            if (file_exists($beforeStepsTemplate)) {
                $customTravisBuildSteps[$name]['before'] = $this->changeIndent(file_get_contents($beforeStepsTemplate), '  ');
            }

            $afterStepsTemplate = $this->getPathToCustomTravisStepsFile($path, $name, 'after');
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
     * @param string[] $phpVersions ie, `array("5.3", "5.4", "5.5")`.
     */
    public function setPhpVersions($phpVersions)
    {
        $this->phpVersions = $phpVersions;
    }

    /**
     * Sets the YAML sections that were found in an existing .travis.yml file and
     * should be preserved. See {@link $travisYmlSectionNames} for list of sections
     * that will NOT be preserved.
     *
     * @param $existingSections
     */
    public function setExistingSections($existingSections)
    {
        foreach ($existingSections as $sectionName => $section) {
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
     * Sets the test jobs to run.
     *
     * @param array $testsToRun Each element must be an array w/ two elements:
     *
     *                          **name**: The test suite name (ie, PluginTests, UITests, etc.)
     *                          **vars**: The environment variables (ie, TEST_AGAINST_CORE=latest_stable)
     */
    public function setTestsToRun($testsToRun)
    {
        $this->testsToRun = $testsToRun;
    }

    /**
     * Sets the tests to exclude.
     *
     * @param array $testsToExclude Each element must be an array w/ the following elements:
     *
     *                              **php**: The PHP version of the job to exclude.
     *                              **env**: The environment variables of the job to exclude.
     *                              **description**: (optional) If supplied, this will be
     *                                               output as a comment above the excluding
     *                                               YAML.
     */
    public function setTestsToExclude($testsToExclude)
    {
        $this->testsToExclude = $testsToExclude;
    }

    /**
     * Sets the location of the travis.sh script to use in the .travis.yml file. This
     * will be the value of the `script:` section.
     *
     * @param string $path
     */
    public function setTravisShScriptLocation($path)
    {
        $this->travisShScriptLocation = $path;
    }

    /**
     * Sets the current working directory that the travis.sh script should be using. This
     * will generate a .travis.yml file that will cd into this directory right before
     * travis executes the build script.
     *
     * @param string $path
     */
    public function setTravisShCwd($path)
    {
        $this->travisShCwd = $path;
    }

    private function changeIndent($text, $newIndent)
    {
        $text = trim($text);

        return preg_replace("/^\\s*/", $newIndent, $text);
    }

    private function getPathToCustomTravisStepsFile($rootPath, $sectionName, $type)
    {
        return "$rootPath/$sectionName.$type.yml";
    }

    public function setLatestStable($version)
    {
        $this->latestStableVersion = $version;
    }
}