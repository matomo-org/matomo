<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Annotations\tests\System;

use Piwik\API\Request;
use Piwik\Plugins\Annotations\API;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\SystemTestCase;
use Piwik\Tests\Fixtures\TwoSitesWithAnnotations;
use Exception;

/**
 * @group Plugins
 * @group AnnotationsTest
 */
class AnnotationsTest extends SystemTestCase
{
    public static $fixture = null;

    public static function getOutputPrefix()
    {
        return 'annotations';
    }

    public function getApiForTesting()
    {
        $idSite1 = self::$fixture->idSite1;

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
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function testAddMultipleSitesFail()
    {
        try {
            API::getInstance()->add("1,2,3", "2012-01-01", "whatever");
            $this->fail("add should fail when given multiple sites in idSite");
        } catch (Exception $ex) {
            // pass
        }
    }

    public function testAddInvalidDateFail()
    {
        try {
            API::getInstance()->add(self::$fixture->idSite1, "invaliddate", "whatever");
            $this->fail("add should fail when given invalid date");
        } catch (Exception $ex) {
            // pass
        }
    }

    public function testSaveMultipleSitesFail()
    {
        try {
            API::getInstance()->save("1,2,3", 0);
            $this->fail("save should fail when given multiple sites");
        } catch (Exception $ex) {
            // pass
        }
    }

    public function testSaveInvalidDateFail()
    {
        try {
            API::getInstance()->save(self::$fixture->idSite1, 0, "invaliddate");
            $this->fail("save should fail when given an invalid date");
        } catch (Exception $ex) {
            // pass
        }
    }

    public function testSaveInvalidNoteIdFail()
    {
        try {
            API::getInstance()->save(self::$fixture->idSite1, -1);
            $this->fail("save should fail when given an invalid note id");
        } catch (Exception $ex) {
            // pass
        }
    }

    public function testDeleteMultipleSitesFail()
    {
        try {
            API::getInstance()->delete("1,2,3", 0);
            $this->fail("delete should fail when given multiple site IDs");
        } catch (Exception $ex) {
            // pass
        }
    }

    public function testDeleteInvalidNoteIdFail()
    {
        try {
            API::getInstance()->delete(self::$fixture->idSite1, -1);
            $this->fail("delete should fail when given an invalid site ID");
        } catch (Exception $ex) {
            // pass
        }
    }

    public function testGetMultipleSitesFail()
    {
        try {
            API::getInstance()->get("1,2,3", 0);
            $this->fail("get should fail when given multiple site IDs");
        } catch (Exception $ex) {
            // pass
        }
    }

    public function testGetInvalidNoteIdFail()
    {
        try {
            API::getInstance()->get(self::$fixture->idSite1, -1);
            $this->fail("get should fail when given an invalid note ID");
        } catch (Exception $ex) {
            // pass
        }
    }

    public function testSaveSuccess()
    {
        API::getInstance()->save(
            self::$fixture->idSite1, 0, $date = '2011-04-01', $note = 'new note text', $starred = 1);

        $expectedAnnotation = array(
            'date'            => '2011-04-01',
            'note'            => 'new note text',
            'starred'         => 1,
            'user'            => 'superUserLogin',
            'idNote'          => 0,
            'canEditOrDelete' => true
        );
        $this->assertEquals($expectedAnnotation, API::getInstance()->get(self::$fixture->idSite1, 0));
    }

    public function testSaveNoChangesSuccess()
    {
        API::getInstance()->save(self::$fixture->idSite1, 1);

        $expectedAnnotation = array(
            'date'            => '2011-12-02',
            'note'            => '1: Site 1 annotation for 2011-12-02',
            'starred'         => 0,
            'user'            => 'superUserLogin',
            'idNote'          => 1,
            'canEditOrDelete' => true
        );
        $this->assertEquals($expectedAnnotation, API::getInstance()->get(self::$fixture->idSite1, 1));
    }

    public function testDeleteSuccess()
    {
        API::getInstance()->delete(self::$fixture->idSite1, 1);

        try {
            API::getInstance()->get(self::$fixture->idSite1, 1);
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
     */
    public function testMethodPermissions($hasAdminAccess, $hasViewAccess, $request, $checkException, $failMessage)
    {
        // create fake access that denies user access
        FakeAccess::$superUser = false;
        FakeAccess::$idSitesAdmin = $hasAdminAccess ? array(self::$fixture->idSite1) : array();
        FakeAccess::$idSitesView = $hasViewAccess ? array(self::$fixture->idSite1) : array();

        if ($checkException) {
            try {
                $request = new Request($request);
                $request->process();
                $this->fail($failMessage);
            } catch (Exception $ex) {
                // pass
            }
        } else {
            $request = new Request($request);
            $request->process();

        }
    }
}

AnnotationsTest::$fixture = new TwoSitesWithAnnotations();