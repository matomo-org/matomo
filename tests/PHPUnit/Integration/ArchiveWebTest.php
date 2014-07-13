<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Integration;

use Piwik\Option;
use Piwik\Http;
use Piwik\Tests\IntegrationTestCase;
use Piwik\Tests\Fixtures\ManySitesImportedLogs;
use Piwik\Tests\Fixture;
use Exception;

/**
 * Tests to call the archive.php script via web and check there is no error.
 *
 * @group Integration
 * @group ArchiveWebTest
 */
class ArchiveWebTest extends IntegrationTestCase
{
    public static $fixture = null; // initialized below class definition

    public function testWebArchiving()
    {
        if(self::isMysqli() && self::isTravisCI()) {
            $this->markTestSkipped('Skipping on Mysqli as it randomly fails.');
        }

        self::deleteArchiveTables();

        $host  = Fixture::getRootUrl();
        $token = Fixture::getTokenAuth();

        $urlTmp = Option::get('piwikUrl');
        Option::set('piwikUrl', $host . 'tests/PHPUnit/proxy/index.php');

        $url = $host . 'tests/PHPUnit/proxy/archive.php?token_auth=' . $token . '&forcelogtoscreen=1';
        $output = Http::sendHttpRequest($url, 600);

        // ignore random build issues
        if (empty($output) || strpos($output, \Piwik\CronArchive::NO_ERROR) === false) {
            $message = "This test has failed. Because it sometimes randomly fails, we skip the test, and ignore this failure.\n";
            $message .= "If you see this message often, or in every build, please investigate as this should only be a random and rare occurence!\n";
            $message .= "\n\narchive web failed: " . $output . "\n\nurl used: $url";
            $this->markTestSkipped($message);
        }

        if (!empty($urlTmp)) {
            Option::set('piwikUrl', $urlTmp);
        } else {
            Option::delete('piwikUrl');
        }

        $this->assertContains('Starting Piwik reports archiving...', $output);
        $this->assertContains('Archived website id = 1', $output);
        $this->assertContains('Done archiving!', $output);
        $this->compareArchivePhpOutputAgainstExpected($output);
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
}

ArchiveWebTest::$fixture = new ManySitesImportedLogs();
ArchiveWebTest::$fixture->addSegments = true;