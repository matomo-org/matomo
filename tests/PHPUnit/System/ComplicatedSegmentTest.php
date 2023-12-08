<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\System;

use Piwik\Date;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

class ComplicatedSegmentTest extends SystemTestCase
{
    private static $dateTime = '2020-10-01 06:03:45';
    private static $idSite = 1;

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        Fixture::createWebsite(self::$dateTime);
        self::trackVisitThatMatches();
        self::trackVisitThatDoesNotMatch();
    }

    public static function getOutputPrefix()
    {
        return "ComplicatedSegment";
    }

    /**
     * @dataProvider getApiForTesting
     */
    public function testApi($api, $params)
    {
        $this->runApiTests($api, $params);
    }

    public function getApiForTesting()
    {
        $idSite = self::$idSite;
        $dateTime = self::$dateTime;

        // Note: Creating a segment in the UI would generate double url encoded values
        // But as segments might also be created through API, or provided as URL parameter, this might not always be the case
        // Therefor some values are urlencoded twice, some aren't
        $segmentWithManyActions = 'contentTarget=@' . urlencode(urlencode('place.com'))
            . ',contentName==my\,video' // the backslash should escape the "," as part of the value
            . ';contentInteraction!@maximise'
            . ';contentPiece=@' . urlencode(urlencode('/to/'))
            . ',entryPageTitle!@exit'
            . ';entryPageTitle==' . urlencode(urlencode('entry page'))
            . ';pageTitle=@page'
            . ',pageUrl!@=home'
            . ';entryPageUrl!=' . urlencode(urlencode('https://piwik.net/some/other/page'))
            . ';exitPageUrl==' . urlencode(urlencode('https://piwik.net/outro'))
            . ',downloadUrl==' . urlencode(urlencode('http://piwik.net/fileout.zip'))
            . ';actionUrl!@absent'
            . ';productViewSku!@DEF'
            . ';productCategory==product\category'
            . ';productSku=@ABC'
            . ',productName=@plugin'
            . ';productViewCategory!@thing'
            . ';productViewName!@thing'
            . ',eventAction==action3'
            . ';eventAction!@blah'
            . ',eventName!=blah2'
        ;

        return [
            [
                // This should use a live query (without temporary segment table)
                'Live.getLastVisitsDetails',
                [
                    'idSite'                 => $idSite,
                    'date'                   => $dateTime,
                    'periods'                => ['day'],
                    'segment'                => $segmentWithManyActions,
                    'testSuffix'             => '_SegmentWithManyActions',
                ],
            ], [
                // This should trigger archiving (using temporary segment table)
                'VisitsSummary.get',
                [
                    'idSite'                 => $idSite,
                    'date'                   => $dateTime,
                    'periods'                => ['day'],
                    'segment'                => $segmentWithManyActions,
                    'testSuffix'             => '_SegmentWithManyActions',
                ],
            ],
        ];
    }

    private static function trackVisitThatMatches()
    {
        $t = Fixture::getTracker(self::$idSite, self::$dateTime);
        $t->setIp('203.46.67.91');
        $t->setUrl('https://piwik.net/home/page');
        Fixture::checkResponse($t->doTrackPageView('entry page'));

        // content tracking
        Fixture::checkResponse($t->doTrackContentImpression('test content name', '/path/to/image.png', 'http://place.com/landingpage'));
        Fixture::checkResponse($t->doTrackContentInteraction('expand', 'my,video', '/path/to/myvideo.mp3'));

        // download
        $t->setForceVisitDateTime(Date::factory(self::$dateTime)->addHour(0.15));
        Fixture::checkResponse($t->doTrackAction('http://piwik.net/fileout.zip', 'download'));

        // site search
        Fixture::checkResponse($t->doTrackSiteSearch('site search keyword', 'internal', 40));

        // ecommerce
        $t->setForceVisitDateTime(Date::factory(self::$dateTime)->addHour(0.2)->getTimestamp());
        $t->setEcommerceView($sku = 'ABCSKU123', $name = 'special plugin', $cat = 'product\category', $price = 888);
        $t->setUrl('http://piwik.net/product');
        Fixture::checkResponse($t->doTrackPageView('product page')); // view

        $t->setForceVisitDateTime(Date::factory(self::$dateTime)->addHour(0.22)->getTimestamp());
        $t->addEcommerceItem($sku = 'ABCSKU123', $name = 'special plugin', $cat = 'product\category', $price = 888, $quantity = 2);
        $t->setUrl('http://piwik.net/order');
        Fixture::checkResponse($t->doTrackPageView('order page'));
        $discount = 50;
        $tax = 100;
        $shipping = 35;
        Fixture::checkResponse($t->doTrackEcommerceOrder('937nsjusu 3894', $grandTotal = 888 * 2 - $discount + $shipping + $tax, 888 * 2, $tax, $shipping, $discount));

        // events
        $t->setForceVisitDateTime(Date::factory(self::$dateTime)->addHour(0.3)->getTimestamp());
        $t->setUrl('http://piwik.net/page/with/events');
        Fixture::checkResponse($t->doTrackEvent($category = 'event cat1', $action = 'action3', $name = 'somemovie', $value = 1));
        Fixture::checkResponse($t->doTrackEvent($category = 'event cat2', $action = 'action4', $name = 'someimage', $value = 2));

        // outlink
        $t->setForceVisitDateTime(Date::factory(self::$dateTime)->addHour(0.4));
        Fixture::checkResponse($t->doTrackAction('https://www.anothersite.com/somewhere/else', 'link'));

        $t->setForceVisitDateTime(Date::factory(self::$dateTime)->addHour(0.5)->getTimestamp());
        $t->setUrl('https://piwik.net/outro');
        Fixture::checkResponse($t->doTrackPageView('exit page'));
    }

    private static function trackVisitThatDoesNotMatch()
    {
        $t = Fixture::getTracker(self::$idSite, self::$dateTime);
        $t->setIp('203.45.66.90');
        $t->setUrl('https://piwik.net/some/other/page');
        Fixture::checkResponse($t->doTrackPageView('other page'));
    }
}
