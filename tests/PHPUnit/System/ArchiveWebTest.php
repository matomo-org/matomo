<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\System;

use Piwik\Option;
use Piwik\Http;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Fixtures\ManySitesImportedLogs;
use Piwik\Tests\Framework\Fixture;
use Exception;

/**
 * Tests to call the archive.php script via web and check there is no error.
 *
 * @group Core
 * @group ArchiveWebTest
 */
class ArchiveWebTest extends SystemTestCase
{
    public static $fixture = null; // initialized below class definition

    public function testWebArchiving()
    {
        if (self::isMysqli() && self::isTravisCI()) {
            $this->markTestSkipped('Skipping on Mysqli as it randomly fails.');
        }

        $host  = Fixture::getRootUrl();
        $token = Fixture::getTokenAuth();

        $urlTmp = Option::get('piwikUrl');
        Option::set('piwikUrl', $host . 'tests/PHPUnit/proxy/index.php');

        $url    = $host . 'tests/PHPUnit/proxy/archive.php?token_auth=' . $token;
        $output = Http::sendHttpRequest($url, 600);

        // ignore random build issues
        if (empty($output) || strpos($output, \Piwik\CronArchive::NO_ERROR) === false) {
            $this->fail("archive web failed: " . $output . "\n\nurl used: $url");
        }

        if (!empty($urlTmp)) {
            Option::set('piwikUrl', $urlTmp);
        } else {
            Option::delete('piwikUrl');
        }

        $this->assertWebArchivingDone($output);
        $this->compareArchivePhpOutputAgainstExpected($output);
    }

    public function test_WebArchiveScriptCanBeRun_WithPhpCgi_AndWithoutTokenAuth()
    {
        list($returnCode, $output) = $this->runArchivePhpScriptWithPhpCgi();

        $this->assertEquals(0, $returnCode);
        $this->assertWebArchivingDone($output, $checkArchivedSite = false);
    }

    private function compareArchivePhpOutputAgainstExpected($output)
    {
        $fileName = 'test_ArchiveCronTest_archive_php_cron_output.txt';
        list($pathProcessed, $pathExpected) = static::getProcessedAndExpectedDirs();

        $expectedOutputFile = $pathExpected . $fileName;

        try {
            $this->assertTrue(is_readable($expectedOutputFile));
            $this->assertEquals(file_get_contents($expectedOutputFile), $output);
        } catch (Exception $ex) {
            $this->comparisonFailures[] = $ex;
        }
    }

    private function assertWebArchivingDone($output, $checkArchivedSite = true)
    {
        $this->assertContains('Starting Piwik reports archiving...', $output);
        if ($checkArchivedSite) {
            $this->assertContains('Archived website id = 1', $output);
        }
        $this->assertContains('Done archiving!', $output);
    }

    private function runArchivePhpScriptWithPhpCgi()
    {
        $command = "php-cgi \"" . PIWIK_INCLUDE_PATH . "/tests/PHPUnit/proxy/archive.php" . "\"";

        exec($command, $output, $returnCode);

        $output = implode("\n", $output);

        return array($returnCode, $output);
    }
}

ArchiveWebTest::$fixture = new ManySitesImportedLogs();
ArchiveWebTest::$fixture->addSegments = true;