<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Annotations\tests\System;

use Piwik\API\Request;
use Piwik\Date;
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
    /** @var TwoSitesWithAnnotations */
    public static $fixture = null;

    public function setUp(): void
    {
        parent::setUp();

        // Fixed time necessary for "last30" API tests.
        Date::$now = strtotime('2012-03-03 12:00:00');
    }

    public function tearDown(): void
    {
        Date::$now = null;

        parent::tearDown();
    }

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
                                              'date'                   => '2012-01-26,2012-03-01',
                                              'periods'                => array('week'),
                                              'testSuffix'             => '_multiplePeriod')),
            array('Annotations.getAll', array('idSite'                 => $idSite1,
                                              'date'                   => '2012-01-15,2012-02-15',
                                              'periods'                => array('range'),
                                              'otherRequestParameters' => array('lastN' => 6),
                                              'testSuffix'             => '_range')),
            array('Annotations.getAll', array('idSite'                 => $idSite1,
                                              'date'                   => 'last30',
                                              'periods'                => array('range'),
                                              'otherRequestParameters' => array('lastN' => 6),
                                              'testSuffix'             => '_last30')),
            array('Annotations.getAll', array('idSite'     => 'all',
                                              'date'       => '2012-01-01',
                                              'periods'    => array('month'),
                                              'testSuffix' => '_multipleSites')),
            array('Annotations.getAll', array('idSite'     => $idSite1,
                                              'testSuffix' => '_noDate')),

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
            array('Annotations.getAnnotationCountForDates', array('idSite'                 => $idSite1,
                                                                  'date'                   => 'last30',
                                                                  'periods'                => array('range'),
                                                                  'otherRequestParameters' => array('lastN' => 6),
                                                                  'testSuffix'             => '_last30')),
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

    /**
     * @dataProvider getTestAddSuccessData
     */
    public function testAddSuccess(string $date, string $expectedDate)
    {
        $addResponse = API::getInstance()->add(
            self::$fixture->idSite1,
            $date,
            $note = 'new note text single add'
        );
        unset($addResponse['id']); // unsetting id for comparison
        unset($addResponse['idNote']);

        $expectedAddResponse = [
            'date'              => $expectedDate,
            'note'              => 'new note text single add',
            'starred'           => 0,
            'user'              => 'superUserLogin',
            'idsite'            => 1,
            'canEditOrDelete'   => true,
        ];

        $this->assertEquals($expectedAddResponse, $addResponse);
    }

    public function getTestAddSuccessData(): iterable
    {
        yield 'specific date' => ['2012-04-01', '2012-04-01'];
        yield 'today'         => ['today', '2012-03-03']; // based on fixed date in the test
        yield 'yesterday'     => ['yesterday', '2012-03-02'];
        yield 'tomorrow'      => ['tomorrow', '2012-03-04'];
    }

    public function testAddMultipleSitesFail()
    {
        self::expectError();

        API::getInstance()->add("1,2,3", "2012-01-01", "whatever");
    }

    public function testAddInvalidDateFail()
    {
        self::expectException(Exception::class);

        API::getInstance()->add(self::$fixture->idSite1, "invaliddate", "whatever");
    }

    public function testSaveMultipleSitesFail()
    {
        self::expectError();

        API::getInstance()->save("1,2,3", 0);
    }

    public function testSaveInvalidDateFail()
    {
        self::expectException(Exception::class);

        API::getInstance()->save(self::$fixture->idSite1, 0, "invaliddate");
    }

    public function testSaveInvalidNoteIdFail()
    {
        self::expectException(Exception::class);

        API::getInstance()->save(self::$fixture->idSite1, -1);
    }

    public function testDeleteMultipleSitesFail()
    {
        self::expectError();

        API::getInstance()->delete("1,2,3", 0);
    }

    public function testDeleteInvalidNoteIdFail()
    {
        self::expectException(Exception::class);

        API::getInstance()->delete(self::$fixture->idSite1, -1);
    }

    public function testGetMultipleSitesFail()
    {
        self::expectError();

        API::getInstance()->get("1,2,3", 0);
    }

    public function testGetInvalidNoteIdFail()
    {
        self::expectException(Exception::class);

        API::getInstance()->get(self::$fixture->idSite1, -1);
    }

    public function testSaveSuccess()
    {
        API::getInstance()->save(
            self::$fixture->idSite1,
            1,
            $date = '2011-04-01',
            $note = 'new note text',
            $starred = 1
        );

        $expectedAnnotation = array(
            'date'              => '2011-04-01',
            'note'              => 'new note text',
            'starred'           => 1,
            'user'              => 'superUserLogin',
            'id'                => 1,
            'idsite'            => 1,
            'canEditOrDelete'   => true,
            'idNote'            => 1,
        );
        $this->assertEquals($expectedAnnotation, API::getInstance()->get(self::$fixture->idSite1, 1));
    }

    public function testSaveSuccessWithoutDate()
    {
        API::getInstance()->save(
            self::$fixture->idSite1,
            1,
            $date = null,
            $note = 'new note text',
            $starred = 1
        );

        $expectedAnnotation = array(
            'date'              => '2011-04-01',
            'note'              => 'new note text',
            'starred'           => 1,
            'user'              => 'superUserLogin',
            'id'                => 1,
            'idsite'            => 1,
            'canEditOrDelete'   => true,
            'idNote'            => 1,
        );
        $this->assertEquals($expectedAnnotation, API::getInstance()->get(self::$fixture->idSite1, 1));
    }

    public function testSaveSuccessChangesDateToTomorrow()
    {
        API::getInstance()->save(
            self::$fixture->idSite1,
            1,
            $date = 'tomorrow',
            $note = 'updated note text for tomorrow, not starred',
            $starred = 0
        );

        $expectedAnnotation = array(
            'date'              => '2012-03-04',
            'note'              => 'updated note text for tomorrow, not starred',
            'starred'           => 0,
            'user'              => 'superUserLogin',
            'id'                => 1,
            'idsite'            => 1,
            'canEditOrDelete'   => true,
            'idNote'            => 1,
        );
        $this->assertEquals($expectedAnnotation, API::getInstance()->get(self::$fixture->idSite1, 1));
    }

    public function testSaveNoChangesSuccess()
    {
        API::getInstance()->save(self::$fixture->idSite1, 3);

        $expectedAnnotation = array(
            'date'            => '2011-12-02',
            'note'            => '1: Site 1 annotation for 2011-12-02',
            'starred'         => 0,
            'user'            => 'superUserLogin',
            'id'              => 3,
            'idsite'          => 1,
            'canEditOrDelete' => true,
            'idNote'          => 3,
        );
        $this->assertEquals($expectedAnnotation, API::getInstance()->get(self::$fixture->idSite1, 3));
    }

    public function testDeleteSuccess()
    {
        self::expectException(Exception::class);

        API::getInstance()->delete(self::$fixture->idSite1, 1);
        API::getInstance()->get(self::$fixture->idSite1, 1);
    }

    public function getPermissionsFailData()
    {
        return array(
            // getAll
            array(false, false, "module=API&method=Annotations.getAll&idSite=1&date=2012-01-01&period=year", true, "getAll should throw if user does not have view access"),

            // get
            array(false, false, "module=API&method=Annotations.get&idSite=1&idNote=1", true, "get should throw if user does not have view access"),

            // getAnnotationCountForDates
            array(false, false, "module=API&method=Annotations.getAnnotationCountForDates&idSite=1&date=2012-01-01&period=year", true, "getAnnotationCountForDates should throw if user does not have view access"),

            // add
            array(false, false, "module=API&method=Annotations.add&idSite=1&date=2011-02-01&note=whatever", true, "add should throw if user does not have view access"),
            array(false, true, "module=API&method=Annotations.add&idSite=1&date=2011-02-01&note=whatever2", false, "add should not throw if user has view access"),
            array(true, true, "module=API&method=Annotations.add&idSite=1&date=2011-02-01&note=whatever3", false, "add should not throw if user has admin access"),

            // save
            array(false, false, "module=API&method=Annotations.save&idSite=1&idNote=1&date=2011-03-01&note=newnote", true, "save should throw if user does not have view access"),
            array(false, true, "module=API&method=Annotations.save&idSite=1&idNote=1&date=2011-03-01&note=newnote", true, "save should throw if user has view access but did not edit note"),
            array(true, true, "module=API&method=Annotations.save&idSite=1&idNote=53&date=2011-03-01&note=newnote", false, "save should not throw if user has admin access"),

            // delete
            array(false, false, "module=API&method=Annotations.delete&idSite=1&idNote=1", true, "delete should throw if user does not have view access"),
            array(false, true, "module=API&method=Annotations.delete&idSite=1&idNote=1", true, "delete should throw if user does not have view access"),
            array(true, true, "module=API&method=Annotations.delete&idSite=1&idNote=53", false, "delete should not throw if user has admin access"),
        );
    }

    /**
     * @dataProvider getPermissionsFailData
     */
    public function testMethodPermissions($hasAdminAccess, $hasViewAccess, $request, $checkException, $failMessage)
    {
        if (true === $checkException) {
            self::expectException(Exception::class);
        } else {
            self::expectNotToPerformAssertions();
        }

        // create fake access that denies user access
        FakeAccess::clearAccess(false);
        FakeAccess::$identity = 'user' . (int)$hasAdminAccess . (int)$hasViewAccess;
        FakeAccess::$idSitesAdmin = $hasAdminAccess ? array(self::$fixture->idSite1) : array();
        FakeAccess::$idSitesView = $hasViewAccess ? array(self::$fixture->idSite1) : array();

        $request = new Request($request . '&format=original');
        $request->process();
    }

    public static function getPathToTestDirectory()
    {
        return dirname(__FILE__);
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess(),
        );
    }
}

AnnotationsTest::$fixture = new TwoSitesWithAnnotations();
