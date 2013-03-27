<?php
/**
 * Piwik - Open source web analytics
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

class AnnotationsTest extends IntegrationTestCase
{
    public static $fixture = null;

    public function getOutputPrefix()
    {
        return 'annotations';
    }

    public function getApiForTesting()
    {
        $idSite1 = self::$fixture->idSite1;
        $idSite2 = self::$fixture->idSite2;

        return array(

            // get
            array('Annotations.get', array('idSite'                 => $idSite1,
                                           'date'                   => '2012-01-01',
                                           'periods'                => 'day',
                                           'otherRequestParameters' => array('idNote' => 1))),

            // getAll
            array('Annotations.getAll', array('idSite'  => $idSite1,
                                              'date'    => '2011-12-01',
                                              'periods' => array('day', 'week', 'month'))),
            array('Annotations.getAll', array('idSite'  => $idSite1,
                                              'date'    => '2012-01-01',
                                              'periods' => array('year'))),
            array('Annotations.getAll', array('idSite'                 => $idSite1,
                                              'date'                   => '2012-03-01',
                                              'periods'                => array('week'),
                                              'otherRequestParameters' => array('lastN' => 6),
                                              'testSuffix'             => '_lastN')),
            array('Annotations.getAll', array('idSite'                 => $idSite1,
                                              'date'                   => '2012-01-15,2012-02-15',
                                              'periods'                => array('range'),
                                              'otherRequestParameters' => array('lastN' => 6),
                                              'testSuffix'             => '_range')),
            array('Annotations.getAll', array('idSite'     => 'all',
                                              'date'       => '2012-01-01',
                                              'periods'    => array('month'),
                                              'testSuffix' => '_multipleSites')),

            // getAnnotationCountForDates
            array('Annotations.getAnnotationCountForDates', array('idSite'  => $idSite1,
                                                                  'date'    => '2011-12-01',
                                                                  'periods' => array('day', 'week', 'month'))),
            array('Annotations.getAnnotationCountForDates', array('idSite'  => $idSite1,
                                                                  'date'    => '2012-01-01',
                                                                  'periods' => array('year'))),
            array('Annotations.getAnnotationCountForDates', array('idSite'                 => $idSite1,
                                                                  'date'                   => '2012-03-01',
                                                                  'periods'                => array('week'),
                                                                  'otherRequestParameters' => array('lastN' => 6),
                                                                  'testSuffix'             => '_lastN')),
            array('Annotations.getAnnotationCountForDates', array('idSite'                 => $idSite1,
                                                                  'date'                   => '2012-01-15,2012-02-15',
                                                                  'periods'                => array('range'),
                                                                  'otherRequestParameters' => array('lastN' => 6),
                                                                  'testSuffix'             => '_range')),
            array('Annotations.getAnnotationCountForDates', array('idSite'     => 'all',
                                                                  'date'       => '2012-01-01',
                                                                  'periods'    => array('month'),
                                                                  'testSuffix' => '_multipleSites')),
        );
    }

    /**
     * @dataProvider getApiForTesting
     * @group        Integration
     * @group        Annotations
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    /**
     * @group        Integration
     * @group        Annotations
     */
    public function testAddMultipleSitesFail()
    {
        try {
            Piwik_Annotations_API::getInstance()->add("1,2,3", "2012-01-01", "whatever");
            $this->fail("add should fail when given multiple sites in idSite");
        } catch (Exception $ex) {
            // pass
        }
    }

    /**
     * @group        Integration
     * @group        Annotations
     */
    public function testAddInvalidDateFail()
    {
        try {
            Piwik_Annotations_API::getInstance()->add(self::$fixture->idSite1, "invaliddate", "whatever");
            $this->fail("add should fail when given invalid date");
        } catch (Exception $ex) {
            // pass
        }
    }

    /**
     * @group        Integration
     * @group        Annotations
     */
    public function testSaveMultipleSitesFail()
    {
        try {
            Piwik_Annotations_API::getInstance()->save("1,2,3", 0);
            $this->fail("save should fail when given multiple sites");
        } catch (Exception $ex) {
            // pass
        }
    }

    /**
     * @group        Integration
     * @group        Annotations
     */
    public function testSaveInvalidDateFail()
    {
        try {
            Piwik_Annotations_API::getInstance()->save(self::$fixture->idSite1, 0, "invaliddate");
            $this->fail("save should fail when given an invalid date");
        } catch (Exception $ex) {
            // pass
        }
    }

    /**
     * @group        Integration
     * @group        Annotations
     */
    public function testSaveInvalidNoteIdFail()
    {
        try {
            Piwik_Annotations_API::getInstance()->save(self::$fixture->idSite1, -1);
            $this->fail("save should fail when given an invalid note id");
        } catch (Exception $ex) {
            // pass
        }
    }

    /**
     * @group        Integration
     * @group        Annotations
     */
    public function testDeleteMultipleSitesFail()
    {
        try {
            Piwik_Annotations_API::getInstance()->delete("1,2,3", 0);
            $this->fail("delete should fail when given multiple site IDs");
        } catch (Exception $ex) {
            // pass
        }
    }

    /**
     * @group        Integration
     * @group        Annotations
     */
    public function testDeleteInvalidNoteIdFail()
    {
        try {
            Piwik_Annotations_API::getInstance()->delete(self::$fixture->idSite1, -1);
            $this->fail("delete should fail when given an invalid site ID");
        } catch (Exception $ex) {
            // pass
        }
    }

    /**
     * @group        Integration
     * @group        Annotations
     */
    public function testGetMultipleSitesFail()
    {
        try {
            Piwik_Annotations_API::getInstance()->get("1,2,3", 0);
            $this->fail("get should fail when given multiple site IDs");
        } catch (Exception $ex) {
            // pass
        }
    }

    /**
     * @group        Integration
     * @group        Annotations
     */
    public function testGetInvalidNoteIdFail()
    {
        try {
            Piwik_Annotations_API::getInstance()->get(self::$fixture->idSite1, -1);
            $this->fail("get should fail when given an invalid note ID");
        } catch (Exception $ex) {
            // pass
        }
    }

    /**
     * @group        Integration
     * @group        Annotations
     */
    public function testSaveSuccess()
    {
        Piwik_Annotations_API::getInstance()->save(
            self::$fixture->idSite1, 0, $date = '2011-04-01', $note = 'new note text', $starred = 1);

        $expectedAnnotation = array(
            'date'            => '2011-04-01',
            'note'            => 'new note text',
            'starred'         => 1,
            'user'            => 'superUserLogin',
            'idNote'          => 0,
            'canEditOrDelete' => true
        );
        $this->assertEquals($expectedAnnotation, Piwik_Annotations_API::getInstance()->get(self::$fixture->idSite1, 0));
    }

    /**
     * @group        Integration
     * @group        Annotations
     */
    public function testSaveNoChangesSuccess()
    {
        Piwik_Annotations_API::getInstance()->save(self::$fixture->idSite1, 1);

        $expectedAnnotation = array(
            'date'            => '2011-12-02',
            'note'            => '1: Site 1 annotation for 2011-12-02',
            'starred'         => 0,
            'user'            => 'superUserLogin',
            'idNote'          => 1,
            'canEditOrDelete' => true
        );
        $this->assertEquals($expectedAnnotation, Piwik_Annotations_API::getInstance()->get(self::$fixture->idSite1, 1));
    }

    /**
     * @group        Integration
     * @group        Annotations
     */
    public function testDeleteSuccess()
    {
        Piwik_Annotations_API::getInstance()->delete(self::$fixture->idSite1, 1);

        try {
            Piwik_Annotations_API::getInstance()->get(self::$fixture->idSite1, 1);
            $this->fail("failed to delete annotation");
        } catch (Exception $ex) {
            // pass
        }
    }

    public function getPermissionsFailData()
    {
        return array(
            // getAll
            array(false, false, "module=API&method=Annotations.getAll&idSite=1&date=2012-01-01&period=year", true, "getAll should throw if user does not have view access"),

            // get
            array(false, false, "module=API&method=Annotations.get&idSite=1&idNote=0", true, "get should throw if user does not have view access"),

            // getAnnotationCountForDates
            array(false, false, "module=API&method=Annotations.getAnnotationCountForDates&idSite=1&date=2012-01-01&period=year", true, "getAnnotationCountForDates should throw if user does not have view access"),

            // add
            array(false, false, "module=API&method=Annotations.add&idSite=1&date=2011-02-01&note=whatever", true, "add should throw if user does not have view access"),
            array(false, true, "module=API&method=Annotations.add&idSite=1&date=2011-02-01&note=whatever2", false, "add should not throw if user has view access"),
            array(true, true, "module=API&method=Annotations.add&idSite=1&date=2011-02-01&note=whatever3", false, "add should not throw if user has admin access"),

            // save
            array(false, false, "module=API&method=Annotations.save&idSite=1&idNote=0&date=2011-03-01&note=newnote", true, "save should throw if user does not have view access"),
            array(false, true, "module=API&method=Annotations.save&idSite=1&idNote=0&date=2011-03-01&note=newnote", true, "save should throw if user has view access but did not edit note"),
            array(true, true, "module=API&method=Annotations.save&idSite=1&idNote=0&date=2011-03-01&note=newnote", false, "save should not throw if user has admin access"),

            // delete
            array(false, false, "module=API&method=Annotations.delete&idSite=1&idNote=0", true, "delete should throw if user does not have view access"),
            array(false, true, "module=API&method=Annotations.delete&idSite=1&idNote=0", true, "delete should throw if user does not have view access"),
            array(true, true, "module=API&method=Annotations.delete&idSite=1&idNote=0", false, "delete should not throw if user has admin access"),
        );
    }

    /**
     * @dataProvider getPermissionsFailData
     * @group        Integration
     * @group        Annotations
     */
    public function testMethodPermissions($hasAdminAccess, $hasViewAccess, $request, $checkException, $failMessage)
    {
        // create fake access that denies user access
        $access = new FakeAccess();
        FakeAccess::$superUser = false;
        FakeAccess::$idSitesAdmin = $hasAdminAccess ? array(self::$fixture->idSite1) : array();
        FakeAccess::$idSitesView = $hasViewAccess ? array(self::$fixture->idSite1) : array();
        Zend_Registry::set('access', $access);

        if ($checkException) {
            try {
                $request = new Piwik_API_Request($request);
                $request->process();
                $this->fail($failMessage);
            } catch (Exception $ex) {
                // pass
            }
        } else {
            $request = new Piwik_API_Request($request);
            $request->process();
        }
    }
}

AnnotationsTest::$fixture = new Test_Piwik_Fixture_TwoSitesWithAnnotations();

