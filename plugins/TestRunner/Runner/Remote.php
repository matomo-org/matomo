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
        $this->ssh->exec('git submodule foreach --recursive git reset --hard');
        $this->ssh->exec('git clean -d -f');
        $this->ssh->exec('git fetch --all');
        $this->ssh->exec('git checkout ' . trim($gitHash));
        $this->ssh->exec('git submodule update --recursive --force');
        $this->ssh->exec('git submodule foreach git clean -f');
        $this->ssh->exec('sudo composer.phar self-update');
        $this->ssh->exec('composer.phar install');
    }

    public function runTests($host, $testSuite)
    {
        $this->prepareTestRun($host);
        $this->printVersionInfo();
        $this->doRunTests($testSuite);
    }

    private function prepareTestRun($host)
    {
        $this->ssh->exec('cp ./tests/PHPUnit/phpunit.xml.dist ./tests/PHPUnit/phpunit.xml');
        $this->ssh->exec("sed -i 's/@REQUEST_URI@/\\//g' ./tests/PHPUnit/phpunit.xml");
        $this->ssh->exec("sed -i 's/amazonAwsUrl/$host/g' ./config/config.ini.php");
    }

    private function printVersionInfo()
    {
        $this->ssh->exec('php --version');
        $this->ssh->exec('mysql --version');
        $this->ssh->exec('phantomjs --version');
    }

    private function doRunTests($testSuite)
    {
        if ('all' === $testSuite) {
            $this->ssh->exec('php console tests:run --options="--colors"');
        } elseif ('ui' === $testSuite) {
            $this->ssh->exec('php console tests:run-ui --persist-fixture-data --assume-artifacts');
        } else {
            $this->ssh->exec('php console tests:run --options="--colors" --testsuite="unit"');
            $this->ssh->exec('php console tests:run --options="--colors" --testsuite="' . $testSuite . '"');
        }
    }
}
