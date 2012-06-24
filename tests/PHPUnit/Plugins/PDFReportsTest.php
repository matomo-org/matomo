<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 * @version $Id$
 */
require_once 'PDFReports/PDFReports.php';

class PDFReportsTest extends DatabaseTestCase
{
    protected $idSiteAccess;

    public function setUp()
    {
        parent::setUp();
        
        // setup the access layer
        $pseudoMockAccess = new FakeAccess;
        FakeAccess::$superUser = true;
        //finally we set the user as a super user by default
        Zend_Registry::set('access', $pseudoMockAccess);
        Piwik_PluginsManager::getInstance()->loadPlugins( array('API', 'UserCountry', 'PDFReports') );
        Piwik_PluginsManager::getInstance()->installLoadedPlugins();

        $this->idSiteAccess = Piwik_SitesManager_API::getInstance()->addSite("Test",array("http://piwik.net"));
        
        $idSite = Piwik_SitesManager_API::getInstance()->addSite("Test",array("http://piwik.net"));
        FakeAccess::setIdSitesView( array($this->idSiteAccess,2));
        
    }

    public function tearDown()
    {
        Piwik_PDFReports_API::$cache = array();
        parent::tearDown();
    }
    
    /**
     * @group Plugins
     * @group PDFReports
     */
    public function testAddReportGetReports()
    {
        $data = array(
            'idsite' => $this->idSiteAccess,
            'description' => 'test description"',
            'type' => 'email',
            'period' => 'day',
            'format' => 'pdf',
            'reports' => array('UserCountry_getCountry'),
            'parameters' => array(
                'displayFormat' => '1',
                'emailMe' => true,
                'additionalEmails' => array('test@test.com', 't2@test.com')
            )
        );

        $dataWebsiteTwo = $data;
        $dataWebsiteTwo['idsite'] = 2;
        $dataWebsiteTwo['period'] = 'month';

        $idReportTwo = $this->_createReport($dataWebsiteTwo);
        // Testing getReports without parameters
        $tmp = Piwik_PDFReports_API::getInstance()->getReports();
        $report = reset($tmp);
        $this->_checkReportsEqual($report, $dataWebsiteTwo);

        $idReport = $this->_createReport($data);

        // Passing 3 parameters
        $tmp = Piwik_PDFReports_API::getInstance()->getReports($this->idSiteAccess, $data['period'], $idReport);
        $report = reset($tmp);
        $this->_checkReportsEqual($report, $data);

        // Passing only idsite
        $tmp = Piwik_PDFReports_API::getInstance()->getReports($this->idSiteAccess);
        $report = reset($tmp);
        $this->_checkReportsEqual($report, $data);

        // Passing only period
        $tmp = Piwik_PDFReports_API::getInstance()->getReports($idSite=false, $data['period']);
        $report = reset($tmp);
        $this->_checkReportsEqual($report, $data);

        // Passing only idreport
        $tmp = Piwik_PDFReports_API::getInstance()->getReports($idSite=false,$period=false, $idReport);
        $report = reset($tmp);
        $this->_checkReportsEqual($report, $data);
    }
    
    /**
     * @group Plugins
     * @group PDFReports
     * @expectedException Exception
     */
    public function testGetReportsIdReportNotFound()
    {
        $report = Piwik_PDFReports_API::getInstance()->getReports($idSite=false,$period=false, $idReport = 1);
    }
    
    /**
     * @group Plugins
     * @group PDFReports
     * @expectedException Exception
     */
    public function testGetReportsInvalidPermission()
    {
        $data = $this->_getAddReportData();
        $idReport = $this->_createReport($data);
        
        $report = Piwik_PDFReports_API::getInstance()->getReports($idSite=44,$period=false, $idReport);
    }
    
    /**
     * @group Plugins
     * @group PDFReports
     * @expectedException Exception
     */
    public function testAddReportInvalidWebsite()
    {
        $data = $this->_getAddReportData();
        $data['idsite'] = 33;
        $idReport = $this->_createReport($data);
    }
    
    /**
     * @group Plugins
     * @group PDFReports
     * @expectedException Exception
     */
    public function testAddReportInvalidPeriod()
    {
        $data = $this->_getAddReportData();
        $data['period'] = 'dx';
        $idReport = $this->_createReport($data);
    }
    
    /**
     * @group Plugins
     * @group PDFReports
     */
    public function testUpdateReport()
    {
        $dataBefore = $this->_getAddReportData();
        $idReport = $this->_createReport($dataBefore);
        $dataAfter = $this->_getYetAnotherAddReportData();
        $this->_updateReport($idReport, $dataAfter);
        $newReport = reset(Piwik_PDFReports_API::getInstance()->getReports($idSite=false,$period=false, $idReport));
        $this->_checkReportsEqual($newReport, $dataAfter);
    }
    
    /**
     * @group Plugins
     * @group PDFReports
     */
    public function testDeleteReport()
    {
        // Deletes non existing report throws exception
        try {
            Piwik_PDFReports_API::getInstance()->deleteReport($idReport = 1);
            $this->fail('Exception not raised');
        } catch(Exception $e) {
        }
        
        $idReport = $this->_createReport($this->_getYetAnotherAddReportData());
        $this->assertEquals(1, count(Piwik_PDFReports_API::getInstance()->getReports()));
        Piwik_PDFReports_API::getInstance()->deleteReport($idReport);
        $this->assertEquals(0, count(Piwik_PDFReports_API::getInstance()->getReports()));
    }
    
    
    protected function _getAddReportData()
    {
        return array(
            'idsite' => $this->idSiteAccess,
            'description' => 'test description"',
            'period' => 'day',
            'type' => 'email',
            'format' => 'pdf',
            'reports' => array('UserCountry_getCountry'),
            'parameters' => array(
                'displayFormat' => '1',
                'emailMe' => true,
                'additionalEmails' => array('test@test.com', 't2@test.com')
            )
        );
    }
    
    protected function _getYetAnotherAddReportData()
    {
        return array(
            'idsite' => $this->idSiteAccess,
            'description' => 'very very long and possibly truncated description. very very long and possibly truncated description. very very long and possibly truncated description. very very long and possibly truncated description. very very long and possibly truncated description. ',
            'period' => 'month',
            'type' => 'email',
            'format' => 'pdf',
            'reports' => array('UserCountry_getContinent'),
            'parameters' => array(
                'displayFormat' => '1',
                'emailMe' => false,
                'additionalEmails' => array('blabla@ec.fr')
            )
        );
    }

    protected function _createReport($data)
    {
        $idReport = Piwik_PDFReports_API::getInstance()->addReport(
            $data['idsite'],
            $data['description'],
            $data['period'],
            $data['type'],
            $data['format'],
            $data['reports'],
            $data['parameters']
        );
        return $idReport;
    }

    protected function _updateReport($idReport, $data)
    {
        $idReport = Piwik_PDFReports_API::getInstance()->updateReport(
            $idReport,
            $data['idsite'],
            $data['description'],
            $data['period'],
            $data['type'],
            $data['format'],
            $data['reports'],
            $data['parameters']);
        return $idReport;

    }

    protected function _checkReportsEqual($report, $data)
    {
        foreach($data as $key => $value)
        {
            if($key == 'description') $value = substr($value,0,250);
            $this->assertEquals($value, $report[$key], "Error for $key for report ".var_export($report ,true)." and data ".var_export($data,true)." ---> %s ");
        }
    }

}
