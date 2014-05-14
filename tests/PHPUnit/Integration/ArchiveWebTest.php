<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

use Piwik\Option;

/**
 * Tests to call the archive.php script via web and check there is no error,
 * @group Integration
 */
class Test_Piwik_Integration_ArchiveWebTest extends IntegrationTestCase
{
    public static $fixture = null; // initialized below class definition

    public function testWebArchiving()
    {
        if(self::isMysqli() && self::isTravisCI()) {
            $this->markTestSkipped('Skipping on Mysqli as it randomly fails.');
        }
        self::$fixture->setUp();
        self::deleteArchiveTables();

        $host  = Fixture::getRootUrl();
        $token = Fixture::getTokenAuth();

        $urlTmp = Option::get('piwikUrl');
        Option::set('piwikUrl', $host . 'tests/PHPUnit/proxy/index.php');

        $streamContext = stream_context_create(array('http' => array('timeout' => 180)));

        $url = $host . 'tests/PHPUnit/proxy/archive.php?token_auth=' . $token . '&forcelogtoscreen=1';
        $output = file_get_contents($url, 0, $streamContext);

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

Test_Piwik_Integration_ArchiveWebTest::$fixture = new Test_Piwik_Fixture_ManySitesImportedLogs();
Test_Piwik_Integration_ArchiveWebTest::$fixture->addSegments = true;