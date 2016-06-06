<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\System;

use Interop\Container\ContainerInterface;
use Piwik\Date;
use Piwik\Plugins\SitesManager\API;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Fixtures\ManySitesImportedLogs;
use Piwik\Tests\Framework\Fixture;
use Exception;

/**
 * Tests to call the cron core:archive command script and check there is no error,
 * Then call the API testing for "Browser archiving is disabled" (see disableArchiving)
 * This tests that, when archiving is disabled,
 *  then Piwik API will return data that was pre-processed during archive.php run
 *
 * @group Core
 * @group ArchiveCronTest
 */
class ArchiveCronTest extends SystemTestCase
{
    /**
     * @var ManySitesImportedLogs
     */
    public static $fixture = null; // initialized below class definition

    public function getApiForTesting()
    {
        $results = array();

        foreach (self::$fixture->getDefaultSegments() as $segmentName => $info) {
            $results[] = array('VisitsSummary.get', array('idSite'     => 'all',
                                                          'date'       => '2012-08-09',
                                                          'periods'    => array('day', 'week', 'month', 'year'),
                                                          'segment'    => $info['definition'],
                                                          'testSuffix' => '_' . $segmentName));


        }

        // API Call Without segments
        $results[] = array('VisitsSummary.get', array('idSite'  => 'all',
                                                      'date'    => '2012-08-09',
                                                      'periods' => array('day', 'month', 'year',  'week')));

        $results[] = array('VisitsSummary.get', array('idSite'     => 'all',
                                                      'date'       => '2012-08-09',
                                                      'periods'    => array('day', 'week', 'month', 'year'),
                                                      'segment'    => 'browserCode==EP',
                                                      'testSuffix' => '_nonPreArchivedSegment'));

        $segments = array(ManySitesImportedLogs::SEGMENT_PRE_ARCHIVED,
                          ManySitesImportedLogs::SEGMENT_PRE_ARCHIVED_CONTAINS_ENCODED
        );
        foreach($segments as $segment) {
            // Test with a pre-processed segment
            $results[] = array(array('VisitsSummary.get', 'Live.getLastVisitsDetails', 'VisitFrequency.get'),
                               array('idSite'     => '1',
                                     'date'       => '2012-08-09',
                                     'periods'    => array('day', 'year'),
                                     'segment'    => $segment,
                                     'testSuffix' => '_preArchivedSegment',
                                     'otherRequestParameters' => array(
                                        'hideColumns' => 'latitude,longitude'
                                     ))
            );
        }

        return $results;
    }

    public function testArchivePhpCron()
    {
        if(self::isPhpVersion53()) {
            $this->markTestSkipped('Fails on PHP 5.3 once in a blue moon.');
        }

        $this->setLastRunArchiveOptions();
        $output = $this->runArchivePhpCron();

        $this->compareArchivePhpOutputAgainstExpected($output);

        foreach ($this->getApiForTesting() as $testInfo) {

            list($api, $params) = $testInfo;

            if (!isset($params['testSuffix'])) {
                $params['testSuffix'] = '';
            }
            $params['testSuffix'] .= '_noOptions';
            $params['disableArchiving'] = true;

            $success = $this->runApiTests($api, $params);

            if (!$success) {
                var_dump($output);
            }
        }
    }

    public function test_archivePhpScript_DoesNotFail_WhenCommandHelpRequested()
    {
        $output = $this->runArchivePhpCron(array('--help' => null), PIWIK_INCLUDE_PATH . '/misc/cron/archive.php');
        $output = implode("\n", $output);

        $this->assertRegExp('/Usage:\s*core:archive/', $output);
        $this->assertNotContains("Starting Piwik reports archiving...", $output);
    }

    private function setLastRunArchiveOptions()
    {
        $periodTypes = array('day', 'periods');
        $idSites = API::getInstance()->getAllSitesId();

        $daysAgoArchiveRanSuccessfully = 1500;
        $this->assertTrue($daysAgoArchiveRanSuccessfully > (\Piwik\CronArchive::ARCHIVE_SITES_WITH_TRAFFIC_SINCE / 86400));
        $time = Date::factory(self::$fixture->dateTime)->subDay($daysAgoArchiveRanSuccessfully)->getTimestamp();

        foreach ($periodTypes as $period) {
            foreach ($idSites as $idSite) {
                // lastRunKey() function inlined
                $lastRunArchiveOption = "lastRunArchive" . $period . "_" . $idSite;
                \Piwik\Option::set($lastRunArchiveOption, $time);
            }
        }
    }

    private function runArchivePhpCron($options = array(), $archivePhpScript = false)
    {
        $archivePhpScript = $archivePhpScript ?: PIWIK_INCLUDE_PATH . '/tests/PHPUnit/proxy/archive.php';
        $urlToProxy = Fixture::getRootUrl() . 'tests/PHPUnit/proxy/index.php';

        // create the command
        $cmd = "php \"$archivePhpScript\" --url=\"$urlToProxy\"";
        foreach ($options as $name => $value) {
            $cmd .= " $name";
            if ($value !== null) {
                $cmd .= "=" . escapeshellarg($value);
            }
        }
        $cmd .= " 2>&1";

        // run the command
        exec($cmd, $output, $result);
        if ($result !== 0 || stripos($result, "error")) {
            $this->fail("archive cron failed: " . implode("\n", $output) . "\n\ncommand used: $cmd");
        }

        return $output;
    }

    private function compareArchivePhpOutputAgainstExpected($output)
    {
        $output = implode("\n", $output);

        $fileName = 'test_ArchiveCronTest_archive_php_cron_output.txt';
        list($pathProcessed, $pathExpected) = static::getProcessedAndExpectedDirs();

        $expectedOutputFile = $pathExpected . $fileName;
        $processedFile = $pathProcessed . $fileName;

        file_put_contents($processedFile, $output);

        try {
            $this->assertTrue(is_readable($expectedOutputFile));
            $this->assertEquals(file_get_contents($expectedOutputFile), $output);
        } catch (Exception $ex) {
            $this->comparisonFailures[] = $ex;
        }
    }

    public static function provideContainerConfigBeforeClass()
    {
        return array(
            'Psr\Log\LoggerInterface' => \DI\get('Monolog\Logger'),

            // for some reason, w/o real translations archiving segments in CronArchive fails. the data returned by CliMulti
            // is a translation token, and nothing else.
            'Piwik\Translation\Translator' => function (ContainerInterface $c) {
                return new \Piwik\Translation\Translator($c->get('Piwik\Translation\Loader\LoaderInterface'));
            }
        );
    }
}

ArchiveCronTest::$fixture = new ManySitesImportedLogs();
ArchiveCronTest::$fixture->addSegments = true;