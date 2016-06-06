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

    public function test_WebArchiving()
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

    }

    public function test_WebArchiveScriptCanBeRun_WithPhpCgi_AndWithoutTokenAuth()
    {
        list($returnCode, $output) = $this->runArchivePhpScriptWithPhpCgi();

        $this->assertEquals(0, $returnCode, "Output: " . $output);
        $this->assertWebArchivingDone($output, $checkArchivedSite = false);
    }

    private function assertWebArchivingDone($output, $checkArchivedSite = true)
    {
        $this->assertContains('Starting Piwik reports archiving...', $output);
        if ($checkArchivedSite) {
            $this->assertContains('Archived website id = 1', $output);
        }
        $this->assertContains('Done archiving!', $output);

        $this->assertNotContains('ERROR', $output);
        $this->assertNotContains('WARNING', $output);

        // Check there are enough lines in output
        $minimumLinesInOutput = 30;
        $linesInOutput = count( explode(PHP_EOL, $output) );
        $this->assertGreaterThan($minimumLinesInOutput, $linesInOutput);
    }

    private function runArchivePhpScriptWithPhpCgi()
    {
        $command = "php-cgi \"" . PIWIK_INCLUDE_PATH . "/tests/PHPUnit/proxy/archive.php" . "\"";

        exec($command, $output, $returnCode);

        $output = implode("\n", $output);

        return array($returnCode, $output);
    }

    public static function provideContainerConfigBeforeClass()
    {
        return array(
            'Psr\Log\LoggerInterface' => \DI\get('Monolog\Logger')
        );
    }
}

ArchiveWebTest::$fixture = new ManySitesImportedLogs();
ArchiveWebTest::$fixture->addSegments = true;