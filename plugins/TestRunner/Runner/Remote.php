<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\TestRunner\Runner;

use \Net_SSH2;

class Remote
{
    /**
     * @var \Net_SSH2
     */
    private $ssh;

    public function __construct(Net_SSH2 $ssh)
    {
        $this->ssh = $ssh;
    }

    public function updatePiwik($gitHash)
    {
        $this->ssh->exec('git reset --hard');
        $this->ssh->exec('git submodule update --init');
        $this->ssh->exec('git submodule foreach --recursive git reset --hard');
        $this->ssh->exec('git clean -d -f');
        $this->ssh->exec('git submodule foreach git clean -f');
        $this->ssh->exec('git fetch --all');
        $this->ssh->exec('git checkout ' . trim($gitHash));
        $this->ssh->exec('git submodule update --init');
        $this->ssh->exec('git submodule update --recursive --force');
        $this->ssh->exec('sudo composer.phar self-update');
        $this->ssh->exec('composer.phar install');
    }

    public function replaceConfigIni($file)
    {
        $content = file_get_contents($file);

        if (!empty($content)) {
            $content = escapeshellarg($content);
            $this->ssh->exec('echo ' . $content . ' > config/config.ini.php');
        }
    }

    public function applyPatch($fileToApply)
    {
        $content = file_get_contents($fileToApply);

        if (!empty($content)) {
            $content = escapeshellarg($content);
            $this->ssh->exec('echo ' . $content . ' | git apply - ');
        }
    }

    public function runTests($host, $testSuite, array $arguments)
    {
        $this->prepareTestRun($host);
        $this->printVersionInfo();
        $this->doRunTests($testSuite, $arguments);
    }

    private function prepareTestRun($host)
    {
        $this->ssh->exec("sed -i 's/amazonAwsUrl/$host/g' ./config/config.ini.php");
    }

    private function printVersionInfo()
    {
        $this->ssh->exec('php --version');
        $this->ssh->exec('mysql --version');
        $this->ssh->exec('phantomjs --version');
    }

    private function doRunTests($testSuite, array $arguments)
    {
        $arguments = implode(' ', $arguments);

        $this->ssh->exec("ps -ef | grep \"php console tests:run\" | grep -v grep | awk '{print $2}' | xargs kill -9");

        if ('all' === $testSuite) {
            $this->ssh->exec('php console tests:run --options="--colors" ' . $arguments);
        } elseif ('ui' === $testSuite) {
            $this->ssh->exec('php console tests:run-ui --persist-fixture-data --assume-artifacts ' . $arguments);
        } else {
            $this->ssh->exec('php console tests:run --options="--colors" --testsuite="unit" ' . $arguments);
            $this->ssh->exec('php console tests:run --options="--colors" --testsuite="' . $testSuite . '" ' . $arguments);
        }

        if ('system' === $testSuite) {
            $this->ssh->exec("tar -cjf tests/PHPUnit/System/processed/processed.tar.bz2 tests/PHPUnit/System/processed/ plugins/*/tests/System/processed/ --exclude='.gitkeep' --exclude='tests/PHPUnit/System/processed/processed.tar.bz2'");
        }
    }
}
