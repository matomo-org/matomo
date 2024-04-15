<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Exception;
use Matomo\Ini\IniReader;
use PHPUnit\Framework\TestCase;
use Piwik\AssetManager\UIAssetFetcher;
use Piwik\Config;
use Piwik\Container\StaticContainer;
use Piwik\Filesystem;
use Piwik\Plugin\Manager;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tracker;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * @group Core
 * @group ReleaseCheckListTest
 */
class ReleaseCheckListTest extends \PHPUnit\Framework\TestCase
{
    private $globalConfig;

    const MINIMUM_PHP_VERSION = '7.2.5';

    public function setUp(): void
    {
        $iniReader = new IniReader();
        $this->globalConfig = $iniReader->readFile(PIWIK_PATH_TEST_TO_ROOT . '/config/global.ini.php');

        parent::setUp();
    }

    public function test_TestCaseHasSetGroupsMethod()
    {
        // refs https://github.com/matomo-org/matomo/pull/16615 ensures setGroups method still exists in phpunit
        // checking this way as it is not an official API
        $this->assertTrue(method_exists(TestCase::class, 'setGroups'));
    }

    public function test_minimumPHPVersion_isEnforced()
    {
        global $piwik_minimumPHPVersion;
        $this->assertEquals(self::MINIMUM_PHP_VERSION, $piwik_minimumPHPVersion, 'minimum PHP version global variable correctly defined');
    }

    public function test_minimumPhpVersion_isDefinedInComposerJson()
    {
        $composerJson = $this->getComposerJsonAsArray();
        // platform value is currently higher than minimum required php version to circumvent minimum requirement of wikimedia/less.php
        $this->assertEquals('7.2.9' /*self::MINIMUM_PHP_VERSION*/, $composerJson['config']['platform']['php']);

        $expectedRequirePhp = '>=' . self::MINIMUM_PHP_VERSION;
        $this->assertEquals($expectedRequirePhp, $composerJson['require']['php']);
    }

    public function test_icoFilesIconsShouldBeInPngFormat()
    {
        $files = Filesystem::globr(PIWIK_INCLUDE_PATH . '/plugins', '*.ico');

        // filter favicon.ico as it may not be in PNG format which is fine
        $files = array_filter($files, function ($value) {
            return !preg_match('/favicon.ico/', $value);
        });

        // filter source files for icon creation as they can be favicons
        $files = array_filter($files, function ($value) {
            return !preg_match('~icons/src~', $value);
        });

        $this->checkFilesAreInPngFormat($files);
        $files = Filesystem::globr(PIWIK_INCLUDE_PATH . '/core', '*.ico');
        $this->checkFilesAreInPngFormat($files);
    }

    public function test_pngFilesIconsShouldBeInPngFormat()
    {
        $files = Filesystem::globr(PIWIK_INCLUDE_PATH . '/plugins', '*.png');
        // filter expected screenshots as they might not be checked out and downloaded when stored in git-lfs
        $files = array_filter($files, function ($value) {
            return !preg_match('/expected-screenshots/', $value) && !preg_match('~icons/src~', $value);
        });
        $this->checkFilesAreInPngFormat($files);
        $files = Filesystem::globr(PIWIK_INCLUDE_PATH . '/core', '*.png');
        $this->checkFilesAreInPngFormat($files);
    }

    public function test_gifFilesIconsShouldBeInGifFormat()
    {
        $files = Filesystem::globr(PIWIK_INCLUDE_PATH . '/plugins', '*.gif');
        $this->checkFilesAreInGifFormat($files);
        $files = Filesystem::globr(PIWIK_INCLUDE_PATH . '/core', '*.gif');
        $this->checkFilesAreInGifFormat($files);
    }

    public function test_jpgImagesShouldBeInJpgFormat()
    {
        $files = Filesystem::globr(PIWIK_INCLUDE_PATH . '/plugins', '*.jpg');
        $this->checkFilesAreInJpgFormat($files);
        $files = Filesystem::globr(PIWIK_INCLUDE_PATH . '/core', '*.jpg');
        $this->checkFilesAreInJpgFormat($files);
        $files = Filesystem::globr(PIWIK_INCLUDE_PATH . '/plugins', '*.jpeg');
        $this->checkFilesAreInJpgFormat($files);
        $files = Filesystem::globr(PIWIK_INCLUDE_PATH . '/core', '*.jpeg');
        $this->checkFilesAreInJpgFormat($files);
    }

    public function test_screenshotsStoredInLfs()
    {
        $screenshots = Filesystem::globr(PIWIK_INCLUDE_PATH . '/tests/UI/expected-screenshots', '*.png');
        $screenshotsPlugins = Filesystem::globr(PIWIK_INCLUDE_PATH . '/plugins/*/tests/UI/expected-screenshots', '*.png');
        $screenshots = array_merge($screenshots, $screenshotsPlugins);
        $cleanPath   = function ($value) {
            return str_replace(PIWIK_INCLUDE_PATH . '/', '', $value);
        };
        $screenshots = array_map($cleanPath, $screenshots);

        $lfsFiles = `git lfs ls-files`;
        if (empty($lfsFiles)) {
            $lfsFiles = `git lfs ls-files --exclude=`;
        }
        $submodules = `git submodule | awk '{ print $2 }'`;
        $submodules = explode("\n", $submodules);
        $storedLfsFiles = explode("\n", $lfsFiles);
        $cleanRevision  = function ($value) {
            $parts = explode(' ', $value);
            return array_pop($parts);
        };
        $storedLfsFiles = array_map($cleanRevision, $storedLfsFiles);

        foreach ($submodules as $submodule) {
            $submodule = trim(trim($submodule), './');
            $pluginLfsFiles = shell_exec('cd ' . PIWIK_DOCUMENT_ROOT . '/' . $submodule . ' && git lfs ls-files');
            if (!empty($pluginLfsFiles)) {
                $pluginLfsFiles = explode("\n", $pluginLfsFiles);
                $pluginLfsFiles = array_map($cleanRevision, $pluginLfsFiles);
                $pluginLfsFiles = array_map(function ($val) use ($submodule) {
                    return $submodule . '/' . $val;
                }, $pluginLfsFiles);
                $storedLfsFiles = array_merge($storedLfsFiles, $pluginLfsFiles);
            }
        }

        $diff = array_diff($screenshots, $storedLfsFiles);
        $this->assertEmpty($diff, 'Some Screenshots are not stored in LFS: ' . implode("\n", $diff));
    }

    public function testCheckThatConfigurationValuesAreProductionValues()
    {
        $this->_checkEqual(array('Debug' => 'always_archive_data_day'), '0');
        $this->_checkEqual(array('Debug' => 'always_archive_data_period'), '0');
        $this->_checkEqual(array('Debug' => 'enable_sql_profiler'), '0');
        $this->_checkEqual(array('General' => 'time_before_today_archive_considered_outdated'), '900');
        $this->_checkEqual(array('General' => 'enable_browser_archiving_triggering'), '1');
        $this->_checkEqual(array('General' => 'default_language'), 'en');
        $this->_checkEqual(array('Tracker' => 'record_statistics'), '1');
        $this->_checkEqual(array('Tracker' => 'visit_standard_length'), '1800');
        $this->_checkEqual(array('Tracker' => 'trust_visitors_cookies'), '0');
        $this->_checkEqual(array('log' => 'log_level'), 'WARN');
        $this->_checkEqual(array('log' => 'log_writers'), array('screen'));
        $this->_checkEqual(array('log' => 'logger_api_call'), null);

        $this->assertFalse(defined('DEBUG_FORCE_SCHEDULED_TASKS'));

        // Check the index.php has "backtrace disabled"
        $content = file_get_contents(PIWIK_INCLUDE_PATH . "/index.php");
        $expected = "define('PIWIK_PRINT_ERROR_BACKTRACE', false);";
        $this->assertTrue(false !== strpos($content, $expected), 'index.php should contain: ' . $expected);
    }

    private function _checkEqual($key, $valueExpected)
    {
        $section = key($key);
        $optionName = current($key);
        $value = null;
        if (isset($this->globalConfig[$section][$optionName])) {
            $value = $this->globalConfig[$section][$optionName];
        }
        $this->assertEquals($valueExpected, $value, "$section -> $optionName was '" . var_export($value, true) . "', expected '" . var_export($valueExpected, true) . "'");
    }

    public function testTemplatesDontContainDebug()
    {
        $patternFailIfFound = 'dump(';
        $files = Filesystem::globr(PIWIK_INCLUDE_PATH . '/plugins', '*.twig');
        foreach ($files as $file) {
            if ($file == PIWIK_INCLUDE_PATH . '/plugins/TestRunner/templates/matomo-tests.yml.twig') {
                continue;
            }

            $content = file_get_contents($file);
            $this->assertFalse(strpos($content, $patternFailIfFound), 'found in ' . $file);
        }
    }

    public function getTemplateFileExtensions()
    {
        $extensions = array(
            array('htm'),
            array('html'),
            array('twig'),
            array('tpl'),
        );
        return $extensions;
    }

    /**
     * @dataProvider getTemplateFileExtensions
     */
    public function testTemplatesDontContainJquery($extension)
    {
        $patternFailIfFound = 'jquery';

        // known files that will for sure not contain a "buggy" $patternFailIfFound
        $allowedFiles = array(
            PIWIK_INCLUDE_PATH . '/plugins/TestRunner/templates/matomo-tests.yml.twig',
            PIWIK_INCLUDE_PATH . '/plugins/CoreUpdater/templates/layout.twig',
            PIWIK_INCLUDE_PATH . '/plugins/Installation/templates/layout.twig',
            PIWIK_INCLUDE_PATH . '/plugins/Login/templates/loginLayout.twig',
            PIWIK_INCLUDE_PATH . '/plugins/SEO/tests/resources/whois_response.html',
            PIWIK_INCLUDE_PATH . '/plugins/SEO/tests/resources/whoiscom_response.html',
            PIWIK_INCLUDE_PATH . '/tests/UI/screenshot-diffs/singlediff.html',

            // Note: entries below are paths and any file within these paths will be automatically allowed
            PIWIK_INCLUDE_PATH . '/tests/resources/overlay-test-site-real/',
            PIWIK_INCLUDE_PATH . '/tests/resources/overlay-test-site/',
            PIWIK_INCLUDE_PATH . '/vendor/lox/xhprof/xhprof_html/docs/',
            PIWIK_INCLUDE_PATH . '/vendor/phpunit/',
            PIWIK_INCLUDE_PATH . '/plugins/Morpheus/icons/',
            PIWIK_INCLUDE_PATH . '/node_modules/',
        );

        $files = Filesystem::globr(PIWIK_INCLUDE_PATH, '*.' . $extension);
        $this->assertFilesDoNotContain($files, $patternFailIfFound, $allowedFiles);
    }

    /**
     * @param $files
     * @param $patternFailIfFound
     * @param $allowedFiles
     */
    private function assertFilesDoNotContain($files, $patternFailIfFound, $allowedFiles)
    {
        $foundPatterns = array();
        foreach ($files as $file) {
            if ($this->isFileOrPathAllowed($allowedFiles, $file)) {
                continue;
            }
            $content = file_get_contents($file);
            $foundPattern = strpos($content, $patternFailIfFound) !== false;

            if ($foundPattern) {
                $foundPatterns[] = $file;
            }
        }

        $this->assertEmpty(
            $foundPatterns,
            sprintf(
                "Forbidden pattern \"%s\" was found in the following files ---> please manually delete these files from Git. \n\n\t%s",
                $patternFailIfFound,
                implode("\n\t", $foundPatterns)
            )
        );
    }

    /**
     * @param $allowedFiles
     * @param $file
     * @return bool
     */
    private function isFileOrPathAllowed($allowedFiles, $file)
    {
        foreach ($allowedFiles as $allowedFile) {
            if (strpos($file, $allowedFile) === 0) {
                return true;
            }
        }
        return false;
    }


    public function testCheckThatGivenPluginsAreDisabledByDefault()
    {
        $pluginsShouldBeDisabled = array(
            'DBStats'
        );
        foreach ($pluginsShouldBeDisabled as $pluginName) {
            $this->assertNotContains(
                $pluginName,
                $this->globalConfig['Plugins']['Plugins'],
                "Plugin $pluginName is enabled by default but shouldn't."
            );
        }
    }

    /**
     * test that the profiler is disabled (mandatory on a production server)
     */
    public function testProfilingDisabledInProduction()
    {
        require_once 'Tracker/Db.php';
        $this->assertTrue(\Piwik\Tracker\Db::isProfilingEnabled() === false, 'SQL profiler should be disabled in production! See Db::$profiling');
    }

    public function testPiwikTrackerDebugIsOff()
    {
        $this->assertTrue(!isset($GLOBALS['PIWIK_TRACKER_DEBUG']));
        $this->assertEquals(0, $this->globalConfig['Tracker']['debug']);

        $tracker = new Tracker();
        $this->assertFalse($tracker->isDebugModeEnabled());
    }

    /**
     * This tests that all PHP files start with <?php
     * This would help detect errors such as a php file starting with spaces
     */
    public function test_phpFilesStartWithRightCharacter()
    {
        $files = Filesystem::globr(PIWIK_INCLUDE_PATH, '*.php');

        $tested = 0;
        foreach ($files as $file) {
            // skip files in these folders
            if (strpos($file, '/libs/') !== false) {
                continue;
            }

            $handle = fopen($file, "r");
            $expectedStart = "<?php";

            $isIniFile = strpos($file, ".ini.php") !== false;
            if ($isIniFile) {
                $expectedStart = "; <?php exit;";
            }

            $skipStartFileTest = $this->isSkipPhpFileStartWithPhpBlock($file, $isIniFile);

            if ($skipStartFileTest) {
                continue;
            }

            $start = fgets($handle, strlen($expectedStart) + 1);
            $this->assertEquals($start, $expectedStart, "File $file does not start with $expectedStart");
            $tested++;
        }

        $this->assertGreaterThan(2000, $tested, 'should have tested at least thousand of  php files');
    }

    public function test_jsfilesDoNotContainFakeSpaces()
    {
        $js = Filesystem::globr(PIWIK_INCLUDE_PATH, '*.js');
        $this->checkFilesDoNotHaveWeirdSpaces($js);
    }

    public function test_phpfilesDoNotContainFakeSpaces()
    {
        $js = Filesystem::globr(PIWIK_INCLUDE_PATH, '*.php');
        $this->checkFilesDoNotHaveWeirdSpaces($js);
    }

    public function test_twigfilesDoNotContainFakeSpaces()
    {
        $js = Filesystem::globr(PIWIK_INCLUDE_PATH, '*.twig');
        $this->checkFilesDoNotHaveWeirdSpaces($js);
    }

    public function test_htmlfilesDoNotContainFakeSpaces()
    {
        $js = Filesystem::globr(PIWIK_INCLUDE_PATH, '*.html');
        $this->checkFilesDoNotHaveWeirdSpaces($js);
    }

    public function test_directoriesShouldBeChmod755()
    {
        $pluginsPath = realpath(PIWIK_INCLUDE_PATH . '/plugins/');

        $objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($pluginsPath), RecursiveIteratorIterator::SELF_FIRST);
        $paths = array();
        foreach ($objects as $name => $object) {
            if (
                is_dir($name)
                && strpos($name, "/.") === false
            ) {
                $paths[] = $name;
            }
        }

        $this->assertGreaterThan(50, count($paths), 'test at latest 50 directories, got ' . count($paths));

        // to prevent errors with un-readable assets,
        // we ensure all directories in plugins/* are added to git with CHMOD 755
        foreach ($paths as $pathToTest) {
            $chmod = substr(decoct(fileperms($pathToTest)), -3);
            $valid = array('777', '775', '755');
            $command = "find $pluginsPath -type d -exec chmod 755 {} +";
            $this->assertTrue(
                in_array($chmod, $valid),
                "Some directories within plugins/ are not chmod 755 \n\nGot: $chmod for : $pathToTest \n\n" .
                "Run this command to set all directories to 755: \n$command\n"
            );
        }
    }

    /**
     * Check that directories in plugins/ folder are specifically either enabled or disabled.
     *
     * This fails when a new folder is added to plugins/* and forgot to enable or mark as disabled in Manager.php.
     */
    public function test_DirectoriesInPluginsFolder_areKnown()
    {
        $pluginsBundledWithPiwik = Config::getInstance()->getFromGlobalConfig('Plugins');
        $pluginsBundledWithPiwik = $pluginsBundledWithPiwik['Plugins'];
        $magicPlugins = 42;
        $this->assertTrue(count($pluginsBundledWithPiwik) > $magicPlugins);

        $plugins = _glob(Manager::getPluginsDirectory() . '*', GLOB_ONLYDIR);
        $count = 1;
        foreach ($plugins as $pluginPath) {
            $pluginName = basename($pluginPath);

            $addedToGit = $this->isPathAddedToGit($pluginPath);

            if (!$addedToGit) {
                // if not added to git, then it is not part of the release checklist.
                continue;
            }
            $manager = Manager::getInstance();
            $isGitSubmodule = $manager->isPluginOfficialAndNotBundledWithCore($pluginName);

            $pluginList = StaticContainer::get('Piwik\Application\Kernel\PluginList');

            $disabled = in_array($pluginName, $pluginList->getCorePluginsDisabledByDefault())  || $isGitSubmodule;

            $enabled = in_array($pluginName, $pluginsBundledWithPiwik);

            $this->assertTrue(
                $enabled + $disabled === 1,
                "Plugin $pluginName should be either enabled (in global.ini.php) or disabled (in Piwik\\Application\\Kernel\\PluginList).
                It is currently (enabled=" . (int)$enabled . ", disabled=" . (int)$disabled . ")"
            );
            $count++;
        }
        $this->assertTrue($count > $magicPlugins);
    }

    public function testEndOfLines()
    {
        foreach (Filesystem::globr(PIWIK_DOCUMENT_ROOT, '*') as $file) {
            // skip files in these folders
            if (
                strpos($file, '/.git/') !== false ||
                strpos($file, '/documentation/') !== false ||
                strpos($file, '/tests/') !== false ||
                strpos($file, '/lang/') !== false ||
                strpos($file, 'yuicompressor') !== false ||
                (strpos($file, '/vendor') !== false && strpos($file, '/vendor/piwik') === false) ||
                strpos($file, '/tmp/') !== false ||
                strpos($file, '/node_modules/') !== false ||
                strpos($file, '/Morpheus/icons/src/') !== false ||
                strpos($file, '/phantomjs/') !== false
            ) {
                continue;
            }

            // skip files with these file extensions
            if (preg_match('/\.(mmdb|bmp|fdf|gif|deb|deflate|exe|gz|ico|jar|jpg|p12|pdf|png|rar|swf|vsd|z|zip|ttf|so|dat|eps|phar|pyc|gzip|eot|woff|svg|woff2)$/', $file)) {
                continue;
            }

            if (!is_dir($file)) {
                $contents = file_get_contents($file);

                // expect CRLF
                if (preg_match('/\.(bat|ps1)$/', $file)) {
                    $contents = str_replace("\r\n", '', $contents);
                    $this->assertTrue(strpos($contents, "\n") === false, 'Incorrect line endings in ' . $file);
                } else {
                    // expect native
                    $hasWindowsEOL = strpos($contents, "\r\n");

                    // overwrite translations files with incorrect line endings
                    $this->assertTrue($hasWindowsEOL === false, 'Incorrect line endings \r\n found in ' . $file);
                }
            }
        }
    }

    public function testPiwikJavaScript()
    {
        // check source against Snort rule 8443
        // @see https://github.com/piwik/piwik/issues/2203
        $pattern = '/\x5b\x5c{2}.*\x5c{2}[\x22\x27]/';
        $contents = file_get_contents(PIWIK_DOCUMENT_ROOT . '/js/piwik.js');

        $this->assertTrue(preg_match($pattern, $contents) == 0);

        $contents = file_get_contents(PIWIK_DOCUMENT_ROOT . '/piwik.js');
        $this->assertTrue(preg_match($pattern, $contents) == 0);
    }

    public function test_piwikJs_minified_isUpToDate()
    {
        shell_exec("sed '/<DEBUG>/,/<\/DEBUG>/d' < " . PIWIK_DOCUMENT_ROOT . "/js/piwik.js | sed 's/eval/replacedEvilString/' | java -jar " . PIWIK_DOCUMENT_ROOT . "/tests/resources/yuicompressor/yuicompressor-2.4.8.jar --type js --line-break 1000 | sed 's/replacedEvilString/eval/' | sed 's/^[/][*]/\/*!/' > " . PIWIK_DOCUMENT_ROOT . "/piwik-minified.js");

        $this->assertFileEquals(
            PIWIK_DOCUMENT_ROOT . '/piwik-minified.js',
            PIWIK_DOCUMENT_ROOT . '/piwik.js',
            'minified /piwik.js is out of date, please re-generate the minified files using instructions in /js/README'
        );
        $this->assertFileEquals(
            PIWIK_DOCUMENT_ROOT . '/piwik-minified.js',
            PIWIK_DOCUMENT_ROOT . '/js/piwik.min.js',
            'minified /js/piwik.min.js is out of date, please re-generate the minified files using instructions in /js/README'
        );
    }

    public function test_piwikJs_SameAsMatomoJs()
    {
        $this->assertFileEquals(
            PIWIK_DOCUMENT_ROOT . '/matomo.js',
            PIWIK_DOCUMENT_ROOT . '/piwik.js',
            '/piwik.js does not match /matomo.js, please re-generate the minified files using instructions in /js/README'
        );
    }

    public function testTmpDirectoryContainsGitKeep()
    {
        $this->assertFileExists(PIWIK_DOCUMENT_ROOT . '/tmp/.gitkeep');
    }

    private function checkFilesAreInPngFormat($files)
    {
        $this->checkFilesAreInFormat($files, "png");
    }
    private function checkFilesAreInJpgFormat($files)
    {
        $this->checkFilesAreInFormat($files, "jpeg");
    }

    private function checkFilesAreInGifFormat($files)
    {
        $this->checkFilesAreInFormat($files, "gif");
    }

    private function checkFilesAreInFormat($files, $format)
    {
        self::expectNotToPerformAssertions();

        $errors = array();
        foreach ($files as $file) {
            // skip files in these folders
            if (strpos($file, '/libs/') !== false) {
                continue;
            }

            $function = "imagecreatefrom" . $format;
            if (!function_exists($function)) {
                throw new \Exception("Unexpected error: $function function does not exist!");
            }

            $handle = @$function($file);
            if (empty($handle)) {
                $errors[] = $file;
            }
        }

        if (!empty($errors)) {
            $icons = implode(" ", $errors);
            $this->fail("$format format failed for following icons $icons \n");
        }
    }

    /**
     * @return bool
     */
    protected function isSkipPhpFileStartWithPhpBlock($file, $isIniFile)
    {
        $isIniFileInTests = strpos($file, "/tests/") !== false;
        $isTestResultFile = strpos($file, "/System/expected") !== false
            || strpos($file, "tests/resources/Updater/") !== false
            || strpos($file, "Twig/Tests/") !== false
            || strpos($file, "processed/") !== false
            || strpos($file, "/vendor/") !== false
            || (strpos($file, "tmp/") !== false && strpos($file, 'index.php') !== false);
        $isLib = strpos($file, "lib/xhprof") !== false || strpos($file, "phpunit/phpunit") !== false;

        return ($isIniFile && $isIniFileInTests) || $isTestResultFile || $isLib;
    }

    /**
     * @return bool
     */
    protected function isPathAddedToGit($pluginPath)
    {
        $gitOutput = shell_exec('git ls-files ' . $pluginPath . ' --error-unmatch 2>&1');
        $addedToGit = (strlen($gitOutput) > 0) && strpos($gitOutput, 'error: pathspec') === false;
        return $addedToGit;
    }


    /**
     * Tests that the Piwik files are not too big, to ensure the downloadable ZIP package is not too large
     */
    public function test_TotalPiwikFilesSize_isWithinReasonnableSize()
    {
        if (!SystemTestCase::isCIEnvironment()) {
            // Don't run the test on local dev machine, as we may have other files (not in GIT) that would fail this test
            $this->markTestSkipped("Skipped this test on local dev environment.");
        }
        $maximumTotalFilesizesExpectedInMb = 62;
        $minimumTotalFilesizesExpectedInMb = 38;
        $minimumExpectedFilesCount = 7000;

        $filesizes = $this->getAllFilesizes();
        $sumFilesizes = array_sum($filesizes);

        $filesOrderedBySize = $filesizes;
        arsort($filesOrderedBySize);

        $this->assertLessThan(
            $maximumTotalFilesizesExpectedInMb * 1024 * 1024,
            $sumFilesizes,
            sprintf(
                "Sum of all files should be less than $maximumTotalFilesizesExpectedInMb Mb.
                    \nGot total file sizes of: %d Mb.
                    \nBiggest files: %s",
                $sumFilesizes / 1024 / 1024,
                var_export(array_slice($filesOrderedBySize, 0, 100, $preserveKeys = true), true)
            )
        );

        $this->assertGreaterThan($minimumExpectedFilesCount, count($filesizes), "Expected at least $minimumExpectedFilesCount files should be included in Piwik.");
        $this->assertGreaterThan($minimumTotalFilesizesExpectedInMb * 1024 * 1024, $sumFilesizes, "expected to have at least $minimumTotalFilesizesExpectedInMb Mb of files in Piwik codebase.");
    }

    public function test_noUpdatesInCorePlugins()
    {
        $manager = Manager::getInstance();
        $plugins = $manager->loadAllPluginsAndGetTheirInfo();

        $pluginsWithUnexpectedUpdates = array();
        $pluginsWithUpdates = array();
        $numTestedCorePlugins = 0;

        // eg these plugins are managed in a submodule and they are installing all tables/columns as part of their plugin install method etc.
        $corePluginsThatAreIndependent = array('TagManager', 'Provider', 'CustomVariables');

        foreach ($plugins as $pluginName => $info) {
            if ($manager->isPluginBundledWithCore($pluginName) && !in_array($pluginName, $corePluginsThatAreIndependent)) {
                $numTestedCorePlugins++;
                $pathToUpdates = Manager::getPluginDirectory($pluginName) . '/Updates/*.php';
                $files = _glob($pathToUpdates);
                if (empty($files)) {
                    $files = array();
                }

                foreach ($files as $file) {
                    $fileVersion = basename($file, '.php');
                    if (
                        version_compare('3.13.0', $fileVersion) != 1
                    ) {
                        // since matomo 3.13.0 we basically don't want to see any plugin specific updates for core plugins
                        // they should be instead in core/Updates/*
                        $pluginsWithUnexpectedUpdates[$pluginName] = $file;
                    } else {
                        $pluginsWithUpdates[] = $pluginName;
                    }
                }
            }
        }

        $this->assertSame(array(), $pluginsWithUnexpectedUpdates);

        // some assertions below to make sure we're actually doing valid tests and there is no bug in above code
        $this->assertGreaterThan(50, $numTestedCorePlugins);
        // eg this here shows the plugins that have update files but from older matomo versions.
        $this->assertSame(array('CustomDimensions', 'DevicesDetection', 'ExamplePlugin', 'Goals', 'LanguagesManager'), array_values(array_unique($pluginsWithUpdates)));
    }

    public function test_bowerComponentsBc_referencesFilesThatExists()
    {
        $filesThatDoNotExist = [];
        foreach (UIAssetFetcher::$bowerComponentFileMappings as $oldFile => $newFile) {
            if ($newFile && !file_exists(PIWIK_DOCUMENT_ROOT . '/' . $newFile)) {
                $filesThatDoNotExist[] = $newFile;
            }
        }

        $this->assertEmpty($filesThatDoNotExist, 'The following asset files in UIAssetFetcher::$bowerComponentFileMappings do not exist: '
            . implode(', ', $filesThatDoNotExist));
    }

    public function test_noVueHtmlWithoutSanitize()
    {
        $command = 'grep -r "v-html=" ' . PIWIK_INCLUDE_PATH . '/plugins --include=*.vue | grep -v "v-html=[\'\\"]\\$sanitize"';
        $output = shell_exec($command);

        $errorMessage = "";
        if (!empty($output)) {
            $lines = explode("\n", $output);

            $files = [];
            foreach ($lines as $line) {
                if (empty(trim($line))) {
                    continue;
                }

                list($file, $match) = explode(':', $line);
                $files[] = '- ' . trim($file);
            }

            $errorMessage = "Found uses of v-html without \$sanitize:\n" . implode("\n", $files);
        }

        $this->assertEmpty($output, $errorMessage);
    }

    /**
     * @param $file
     * @return bool
     */
    private function isFileIncludedInFinalRelease($file)
    {
        if (is_dir($file)) {
            return false;
        }

        // in build-package.sh we have: `find ./ -iname 'tests' -type d -prune -exec rm -rf {} \;`
        if ($this->isFileBelongToTests($file)) {
            return false;
        }
        if (strpos($file, PIWIK_INCLUDE_PATH . "/tmp/") !== false) {
            return false;
        }

        // ignore downloaded geoip files
        if ((strpos($file, 'GeoIP') !== false || strpos($file, 'DBIP') !== false) && strpos($file, '.mmdb') !== false) {
            return false;
        }

        if ($this->isFileIsAnIconButDoesNotBelongToDistribution($file)) {
            return false;
        }


        if ($this->isPluginSubmoduleAndThereforeNotFoundInFinalRelease($file)) {
            return false;
        }

        if ($this->isFileBelongToComposerDevelopmentPackage($file)) {
            return false;
        }

        if ($this->isFileDeletedFromPackage($file)) {
            return false;
        }

        return true;
    }

    /**
     * Plugins Submodule in Piwik codebase are not there in the release package,
     * (the plugins are released on the Marketplace.)
     *
     * @param $file
     * @return bool
     */
    private function isPluginSubmoduleAndThereforeNotFoundInFinalRelease($file)
    {
        if (strpos($file, PIWIK_INCLUDE_PATH . "/plugins/") === false) {
            return false;
        }

        $pluginName = str_replace(PIWIK_INCLUDE_PATH . "/plugins/", "", $file);
        $pluginName = substr($pluginName, 0, strpos($pluginName, "/"));

        $this->assertNotEmpty($pluginName, "Detected an empty plugin name from path: $file ");

        $pluginManager = Manager::getInstance();
        $notInPackagedRelease = $pluginManager->isPluginOfficialAndNotBundledWithCore($pluginName);

        // test that the submodule check works
        if ($pluginName == 'VisitorGenerator') {
            $this->assertTrue($notInPackagedRelease, "Expected isPluginOfficialAndNotBundledWithCore to return true for VisitorGenerator plugin");
        }
        return $notInPackagedRelease;
    }

    /**
     * @param $file
     * @return bool
     */
    private function isFileBelongToComposerDevelopmentPackage($file)
    {
        $composerDependencyDevOnly = $this->getComposerRequireDevPackages();

        return $this->isFilePathFoundInArray($file, $composerDependencyDevOnly);
    }

    /**
     * @return array
     */
    private function getComposerRequireDevPackages()
    {
        $composerJson = $this->getComposerLockAsArray();
        $composerDependencyDevOnly = array_column($composerJson['packages-dev'], 'name');
        return $composerDependencyDevOnly;
    }

    /**
     * return true if $file is found within any sub-string in $filesToMatchAgainst,
     *
     * @param $file
     * @param $filesToMatchAgainst array
     * @return bool
     */
    private function isFilePathFoundInArray($file, $filesToMatchAgainst)
    {
        foreach ($filesToMatchAgainst as $fileToMatchAgainst) {
            if (strpos($file, $fileToMatchAgainst) !== false || fnmatch(PIWIK_INCLUDE_PATH . '/' . $fileToMatchAgainst, $file)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $file
     * @return bool
     */
    private function isFileDeletedFromPackage($file)
    {
        $filesAndFoldersToDeleteFromPackage = [
            # Should stay synchronised with: https://github.com/matomo/matomo-package/blob/master/scripts/build-package.sh#L104-L116
            'composer.phar',
            'vendor/bin/',
            'vendor/container-interop/container-interop/docs',
            'vendor/davaxi/sparkline/composer-8.json',
            'vendor/davaxi/sparkline/docker-compose.yml',
            'vendor/davaxi/sparkline/Dockerfile',
            'vendor/geoip2/geoip2/examples/',
            'vendor/lox/xhprof/bin',
            'vendor/lox/xhprof/examples',
            'vendor/lox/xhprof/scripts',
            'vendor/lox/xhprof/extension',
            'vendor/lox/xhprof/xhprof_html',
            'vendor/maxmind-db/reader/ext/',
            'vendor/maxmind-db/reader/autoload.php',
            'vendor/maxmind-db/reader/CHANGELOG.md',
            'vendor/maxmind/web-service-common/dev-bin/',
            'vendor/maxmind/web-service-common/CHANGELOG.md',
            'vendor/pear/archive_tar/docs',
            'vendor/php-di/invoker/doc/',
            'vendor/php-di/php-di/benchmarks/',
            'vendor/symfony/console/Symfony/Component/Console/Resources/bin',
            'vendor/szymach/c-pchart/resources/doc',
            'vendor/szymach/c-pchart/coverage.sh',
            'vendor/szymach/c-pchart/codeception.yml',
            'vendor/tecnickcom/tcpdf/examples',
            'vendor/tecnickcom/tcpdf/tools',
            'vendor/tecnickcom/tcpdf/CHANGELOG.TXT',
            'vendor/twig/twig/test/',
            'vendor/twig/twig/doc/',
            'vendor/twig/twig/.php-cs-fixer.dist.php',
            'config/environment/test.php',
            'config/environment/ui-test.php',
            'plugins/*/config/test.php',
            'plugins/*/config/ui-test.php',
            'plugins/Morpheus/icons/src*',
            'plugins/Morpheus/icons/tools*',
            'plugins/Morpheus/icons/flag-icon-css*',
            'plugins/Morpheus/icons/submodules*',
            'plugins/Morpheus/icons/.git*',
            'plugins/Morpheus/icons/*.py',
            'plugins/Morpheus/icons/*.sh',
            'plugins/Morpheus/icons/*.json',
            'plugins/Morpheus/icons/*.lock',
            'plugins/Morpheus/icons/*.svg',
            'plugins/Morpheus/icons/*.txt',
            'plugins/Morpheus/icons/*.php',
            'plugins/Morpheus/icons/*.yml',
            'plugins/Example*',
            'vendor/tecnickcom/tcpdf/fonts/ae_fonts_2.0',
            'vendor/tecnickcom/tcpdf/fonts/dejavu-fonts-ttf-2.33',
            'vendor/tecnickcom/tcpdf/fonts/dejavu-fonts-ttf-2.34',
            'vendor/tecnickcom/tcpdf/fonts/freefont-20100919',
            'vendor/tecnickcom/tcpdf/fonts/freefont-20120503',
            'vendor/tecnickcom/tcpdf/fonts/freemon*',
            'vendor/tecnickcom/tcpdf/fonts/cid*',
            'vendor/tecnickcom/tcpdf/fonts/courier*',
            'vendor/tecnickcom/tcpdf/fonts/aefurat*',
            'vendor/tecnickcom/tcpdf/fonts/dejavusansb*',
            'vendor/tecnickcom/tcpdf/fonts/dejavusansi*',
            'vendor/tecnickcom/tcpdf/fonts/dejavusansmono*',
            'vendor/tecnickcom/tcpdf/fonts/dejavusanscondensed*',
            'vendor/tecnickcom/tcpdf/fonts/dejavusansextralight*',
            'vendor/tecnickcom/tcpdf/fonts/dejavuserif*',
            'vendor/tecnickcom/tcpdf/fonts/freesansi*',
            'vendor/tecnickcom/tcpdf/fonts/freesansb*',
            'vendor/tecnickcom/tcpdf/fonts/freeserifb*',
            'vendor/tecnickcom/tcpdf/fonts/freeserifi*',
            'vendor/tecnickcom/tcpdf/fonts/pdf*',
            'vendor/tecnickcom/tcpdf/fonts/times*',
            'vendor/tecnickcom/tcpdf/fonts/uni2cid*',
            'vendor/szymach/c-pchart/resources/fonts/advent_light*',
            'vendor/szymach/c-pchart/resources/fonts/Bedizen*',
            'vendor/szymach/c-pchart/resources/fonts/calibri*',
            'vendor/szymach/c-pchart/resources/fonts/Forgotte*',
            'vendor/szymach/c-pchart/resources/fonts/MankSans*',
            'vendor/szymach/c-pchart/resources/fonts/pf_arma_five*',
            'vendor/szymach/c-pchart/resources/fonts/Silkscreen*',
            'vendor/szymach/c-pchart/resources/fonts/verdana*',
            'node_modules/chroma-js/Makefile',
            'node_modules/chroma-js/chroma.js',
            'node_modules/chroma-js/doc',
            'node_modules/chroma-js/readme.md',
            'node_modules/chroma-js/src',
            'node_modules/chroma-js/test',
            'node_modules/iframe-resizer/js/iframeResizer.contentWindow.js',
            'node_modules/iframe-resizer/js/iframeResizer.js',
            'node_modules/iframe-resizer/src/ie8.polyfils.js',
            'node_modules/iframe-resizer/src/iframeResizer.contentWindow.js',
            'node_modules/iframe-resizer/src/iframeResizer.js',
            'node_modules/iframe-resizer/test-main.js',
            'node_modules/jquery/dist/jquery.js',
            'node_modules/jquery/src',
            'node_modules/jquery/external',
            'node_modules/jquery-ui-dist/component.json',
            'node_modules/jquery-ui-dist/external',
            'node_modules/jquery-ui-dist/images',
            'node_modules/jquery-ui-dist/index.html',
            'node_modules/jquery-ui-dist/jquery-ui.css',
            'node_modules/jquery-ui-dist/jquery-ui.js',
            'node_modules/jquery-ui-dist/jquery-ui.structure.css',
            'node_modules/jquery-ui-dist/jquery-ui.theme.css',
            'node_modules/jquery.scrollto/jquery.scrollTo.js',
            'node_modules/jquery.scrollto/scrollTo.jquery.json',
            'node_modules/jquery.scrollto/changes.txt',
            'node_modules/jquery.scrollto/demo',
            'node_modules/@materializecss/materialize/extras',
            'node_modules/@materializecss/materialize/js',
            'node_modules/@materializecss/materialize/sass',
            'node_modules/@materializecss/materialize/dist/js/materialize.js',
            'node_modules/@materializecss/materialize/dist/css/materialize.css',
            'node_modules/mousetrap/mousetrap.js',
            'node_modules/mousetrap/plugins',
            'node_modules/mousetrap/mousetrap.sublime-project',
            'node_modules/ng-dialog/CONTRIBUTING.md',
            'node_modules/ng-dialog/css',
            'node_modules/ng-dialog/example',
            'node_modules/ng-dialog/protractor.conf.js',
            'node_modules/ng-dialog/server.js',
            'node_modules/qrcodejs2/index-svg.html',
            'node_modules/qrcodejs2/index.html',
            'node_modules/qrcodejs2/index.svg',
            'node_modules/qrcodejs2/jquery.min.js',
            'node_modules/qrcodejs2/qrcode.js',
            'node_modules/sprintf-js/CONTRIBUTORS.MD',
            'node_modules/sprintf-js/README.md',
            'node_modules/sprintf-js/src',
            'node_modules/visibilityjs/ChangeLog.md',
            'node_modules/visibilityjs/component.json',
            'node_modules/visibilityjs/index.d.ts',
            'node_modules/visibilityjs/index.js',
            'node_modules/visibilityjs/README.md',
            'node_modules/vue/dist/vue.cjs.js',
            'node_modules/vue/dist/vue.cjs.prod.js',
            'node_modules/vue/dist/vue.d.ts',
            'node_modules/vue/dist/vue.esm-browser.js',
            'node_modules/vue/dist/vue.esm-browser.prod.js',
            'node_modules/vue/dist/vue.esm-bundler.js',
            'node_modules/vue/dist/vue.runtime.esm-browser.js',
            'node_modules/vue/dist/vue.runtime.esm-browser.prod.js',
            'node_modules/vue/dist/vue.runtime.esm-bundler.js',
            'node_modules/vue/dist/vue.runtime.global.js',
            'node_modules/vue/dist/vue.runtime.global.prod.js',
            'libs/jqplot/jqplot.core.js',
            'libs/jqplot/jqplot.lineRenderer.js',
            'libs/jqplot/jqplot.linearAxisRenderer.js',
            'libs/jqplot/jqplot.themeEngine.js',
            'libs/jqplot/plugins/jqplot.barRenderer.js',
            'libs/jqplot/plugins/jqplot.pieRenderer.js',
            'config/config.php',
            '*.gitignore',
            '*.gitmodules',
            '*.gitattributes',
            '*.git-blame-ignore-revs',
            '*.bowerrc',
            '*.bower.json',
            '*bower.json',
            '*.coveralls.yml',
            '*.editorconfig',
            '*.gitkeep',
            '*.jshintrc',
            '*.php_cs',
            '*.php_cs.dist',
            '*phpunit.xml.dist',
            '*phpunit.xml',
            '*.phpcs.xml.dist',
            '*phpcs.xml',
            '*Gruntfile.js',
            '*gruntfile.js',
            '*.map',
            '*.travis.yml',
            '*installed.json',
            '*package.json',
            '*package-lock.json',
            '*yarn.lock',
            '*.scrutinizer.yml',
            '*.gitstats.yml',
            '*composer.json',
            '*composer.lock',
            '*.spec.js',
            '*.phpstorm.meta.php',
            '*.lfsconfig',
            '*.travis.sh',
            '*tsconfig.json',
            '*tsconfig.spec.json',
            '*.eslintrc.js',
            '*.eslintignore',
            '*.eslintrc',
            '*.browserslistrc',
            '*babel.config.js',
            '*jest.config.js',
            '*karma.conf.js',
            '*karma-conf.js',
            '*vue.config.js',
            '*.npmignore',
            '*.ncurc.json',
            '*.prettierrc',
            '*.jscsrc',
            '*phpstan.neon',
            '*phpstan.neon.dist',
            '*package.xml',
            '*.stylelintrc.json'
        ];

        return $this->isFilePathFoundInArray($file, $filesAndFoldersToDeleteFromPackage);
    }

    /**
     * @return array
     * @throws Exception
     */
    private function getAllFilesizes()
    {
        $files = Filesystem::globr(PIWIK_INCLUDE_PATH, '*');

        $filesizes = array();
        foreach ($files as $file) {
            if (!$this->isFileIncludedInFinalRelease($file)) {
                continue;
            }

            $filesize = filesize($file);

            if ($filesize === false) {
                throw new Exception("Error getting filesize for file: $file");
            }
            $filesizes[$file] = $filesize;
        }
        return $filesizes;
    }

    /**
     * @param $files
     * @throws Exception
     */
    protected function checkFilesDoNotHaveWeirdSpaces($files)
    {
        $weirdSpace = ' ';
        $this->assertEquals('c2a0', bin2hex($weirdSpace), "Checking that this test file was not tampered with");
        $this->assertEquals('20', bin2hex(' '), "Checking that this test file was not tampered with");

        $errors = array();
        $countFileChecked = 0;
        foreach ($files as $file) {
            if ($this->isFileBelongToTests($file) || is_dir($file)) {
                continue;
            }

            if (
                strpos($file, 'vendor/php-di/php-di/website/') !== false
                || strpos($file, 'vendor/phpmailer/phpmailer/language/') !== false
                || strpos($file, 'vendor/wikimedia/less.php/') !== false
                || strpos($file, 'node_modules/') !== false
                || strpos($file, 'vendor/mayflower/mo4-coding-standard/') !== false
                || strpos($file, 'vendor/symfony/polyfill-iconv/') !== false
                || strpos($file, 'vendor/symfony/polyfill-intl-normalizer/') !== false
                || strpos($file, 'plugins/CoreVue/polyfills/dist/MatomoPolyfills.min.js') !== false
                || strpos($file, 'plugins/VisitorGenerator/vendor/fzaninotto/faker/src/Faker/Provider/') !== false
                || preg_match('%/plugins/[a-zA-Z0-9_]+/vue/dist%', $file)
            ) {
                continue;
            }

            $content = file_get_contents($file);
            $posWeirdSpace = strpos($content, $weirdSpace);
            if ($posWeirdSpace !== false) {
                $around = substr($content, $posWeirdSpace - 20, 40);
                $around = trim($around);
                $errors[] = "File $file contains an unusual space character, please remove it from here: ...$around...";
            }

            $countFileChecked++;
        }
        $this->assertGreaterThan(1, $countFileChecked, "expected to test at least one file, but tested only " . $countFileChecked);

        if (!empty($errors)) {
            throw new Exception(implode(",\n\n ", $errors));
        }
    }

    /**
     * @param $file
     * @return bool
     */
    private function isFileBelongToTests($file)
    {
        return stripos($file, "/tests/") !== false || stripos($file, "/phantomjs/") !== false;
    }

    /**
     * @return mixed
     */
    private function getComposerJsonAsArray()
    {
        $composer = file_get_contents(PIWIK_INCLUDE_PATH . '/composer.json');
        $composerJson = json_decode($composer, $assoc = true);
        return $composerJson;
    }

    /**
     * @return mixed
     */
    private function getComposerLockAsArray()
    {
        $composer = file_get_contents(PIWIK_INCLUDE_PATH . '/composer.lock');
        $composerJson = json_decode($composer, $assoc = true);
        return $composerJson;
    }

    /**
     * ignore icon source files as they are large, but not included in the final package
     *
     */
    private function isFileIsAnIconButDoesNotBelongToDistribution($file)
    {
        return preg_match('~Morpheus/icons/(?!dist)~', $file);
    }
}
