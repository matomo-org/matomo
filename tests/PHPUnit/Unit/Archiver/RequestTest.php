<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Tests\Unit\Archiver;

use Piwik\Archiver\Request;
use Piwik\Date;

class RequestTest extends \PHPUnit\Framework\TestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();
        Date::$now = null;
    }

    /**
     * @dataProvider getTestDataForChangeDate
     */
    public function test_changeDate_replacesDateProperly($url, $newDate, $expectedNewUrl)
    {
        $request = new Request($url);
        $request->changeDate($newDate);
        $this->assertEquals($expectedNewUrl, $request->getUrl());
    }

    public function getTestDataForChangeDate()
    {
        return [
            [
                'http://abc.com/index.php?trigger=archivephp&method=API.get&date=2012-03-04',
                'last12',
                'http://abc.com/index.php?trigger=archivephp&method=API.get&date=last12',
            ],
            [
                'http://abc.com/index.php?trigger=archivephp&method=API.get&date=2012-03-04,2013-02-4&period=day',
                'previous18',
                'http://abc.com/index.php?trigger=archivephp&method=API.get&date=previous18&period=day',
            ],
            [
                'http://abc.com/index.php?date=lastN&period=day',
                '2013-10-12,2013-11-19',
                'http://abc.com/index.php?date=2013-10-12,2013-11-19&period=day',
            ],
        ];
    }

    /**
     * @dataProvider getTestDataForMakeSureDateIsNotSingleDayRange
     */
    public function test_makeSureDateIsNotSingleDayRange($url, $expectedResult)
    {
        $request = new Request($url);
        $request->makeSureDateIsNotSingleDayRange();
        $this->assertEquals($expectedResult, $request->getUrl());
    }

    public function getTestDataForMakeSureDateIsNotSingleDayRange()
    {
        return [
            ['?idSite=1&date=2012-03-04&period=day', '?idSite=1&date=2012-03-04&period=day'],
            ['?idSite=1&date=2012-03-04,2012-03-06&period=range', '?idSite=1&date=2012-03-04,2012-03-06&period=range'],
            ['?idSite=1&date=2012-03-04,2012-03-04&period=range', '?idSite=1&date=2012-03-04&period=day'],
            ['?idSite=1&date=last1&period=range', '?idSite=1&date=today&period=day'],
            ['?idSite=1&date=previous1&period=range', '?idSite=1&date=yesterday&period=day'],
        ];
    }
}
