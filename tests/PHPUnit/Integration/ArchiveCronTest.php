<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

use Piwik\Access;
use Piwik\Date;
use Piwik\Plugins\SitesManager\API;

/**
 * Tests the archive.php cron script.
 */
class Test_Piwik_Integration_ArchiveCronTest extends IntegrationTestCase
{
    public static $fixture = null; // initialized below class definition
    
    public static function createAccessInstance()
    {
        Access::setSingletonInstance($access = new Test_Access_OverrideLogin());
        \Piwik\Piwik::postEvent('Request.initAuthenticationObject');
    }
    
    public function getApiForTesting()
    {
        $results = array();
        $results[] = array('VisitsSummary.get', array('idSite'  => 'all',
                                                      'date'    => '2012-08-09',
                                                      'periods' => array('day', 'week', 'month', 'year')));

        foreach (self::$fixture->getDefaultSegments() as $segmentName => $info) {
            $results[] = array('VisitsSummary.get', array('idSite'     => 'all',
                                                          'date'       => '2012-08-09',
                                                          'periods'    => array('day', 'week', 'month', 'year'),
                                                          'segment'    => $info['definition'],
                                                          'testSuffix' => '_' . $segmentName));
        }
        
        $results[] = array('VisitsSummary.get', array('idSite'     => 'all',
                                                      'date'       => '2012-08-09',
                                                      'periods'    => array('day', 'week', 'month', 'year'),
                                                      'segment'    => 'browserCode==EP',
                                                      'testSuffix' => '_nonPreArchivedSegment'));
        
        return $results;
    }
    
    public function getArchivePhpCronOptionsToTest()
    {
        return array(
            array('noOptions', array()),
            // segment archiving makes calling the script more than once impractical. if all 4 are
            // called, this test can take up to 13min to complete.
            /*array('forceAllWebsites', array('--force-all-websites' => false)),
            array('forceAllPeriods_lastDay', array('--force-all-periods' => '86400')),
            array('forceAllPeriods_allTime', array('--force-all-periods' => false)),*/
        );
    }
    
    /**
     * @dataProvider getArchivePhpCronOptionsToTest
     * @group        Integration
     */
    public function testArchivePhpCron($optionGroupName, $archivePhpOptions)
    {
        self::deleteArchiveTables();
        $this->setLastRunArchiveOptions();
        $this->runArchivePhpCron($archivePhpOptions);
        
        foreach ($this->getApiForTesting() as $testInfo) {
            list($api, $params) = $testInfo;
            
            if (!isset($params['testSuffix'])) {
                $params['testSuffix'] = '';
            }
            $params['testSuffix'] .= '_' . $optionGroupName;
            $params['disableArchiving'] = true;
            
            // only do month for the last 3 option groups
            if ($optionGroupName != 'noOptions') {
                $params['periods'] = array('day');
            }
            
            $this->runApiTests($api, $params);
        }
    }
    
    private function setLastRunArchiveOptions()
    {
        $periodTypes = array('day', 'periods');
        $idSites = API::getInstance()->getAllSitesId();
        
        $time = Date::factory(self::$fixture->dateTime)->subDay(1)->getTimestamp();
        
        foreach ($periodTypes as $period) {
            foreach ($idSites as $idSite) {
                // lastRunKey() function inlined
                $lastRunArchiveOption = "lastRunArchive" . $period . "_" . $idSite;
                
                \Piwik\Option::set($lastRunArchiveOption, $time);
            }
        }
    }
    
    private function runArchivePhpCron($options)
    {
        $archivePhpScript = PIWIK_INCLUDE_PATH . '/tests/PHPUnit/proxy/archive.php';
        $urlToProxy = Test_Piwik_BaseFixture::getRootUrl() . 'tests/PHPUnit/proxy/index.php';
        
        // create the command
        $cmd = "php \"$archivePhpScript\" --url=\"$urlToProxy\" ";
        foreach ($options as $name => $value) {
            $cmd .= $name;
            if ($value !== false) {
                $cmd .= '="' . $value . '"';
            }
            $cmd .= ' ';
        }
        $cmd .= '2>&1';
        
        // run the command
        exec($cmd, $output, $result);
        if ($result !== 0) {
            throw new Exception("archive cron failed: " . implode("\n", $output) . "\n\ncommand used: $cmd");
        }

        return $output;
    }
}

Test_Piwik_Integration_ArchiveCronTest::$fixture = new Test_Piwik_Fixture_ManySitesImportedLogs();
Test_Piwik_Integration_ArchiveCronTest::$fixture->addSegments = true;
