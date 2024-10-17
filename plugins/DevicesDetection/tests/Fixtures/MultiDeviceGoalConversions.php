<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DevicesDetection\tests\Fixtures;

use Piwik\Date;
use Piwik\Plugins\Goals\API;
use Piwik\Tests\Framework\Fixture;

/**
 * Fixture that adds one site with one goal and tracks some page views from different devices with some goal conversions
 */
class MultiDeviceGoalConversions extends Fixture
{
    public $dateTime = '2009-01-04 00:11:42';
    public $idSite   = 1;
    public $idGoal   = 1;

    public function setUp(): void
    {
        $this->setUpWebsitesAndGoals();
        $this->trackSmartphoneVisits();
        $this->trackTabletVisits();
        $this->trackOtherVisits();
    }

    public function tearDown(): void
    {
        // empty
    }

    private function setUpWebsitesAndGoals()
    {
        if (!self::siteCreated($idSite = 1)) {
            self::createWebsite($this->dateTime, $ecommerce = 1);
        }

        if (!self::goalExists($idSite = 1, $idGoal = 1)) {
            API::getInstance()->addGoal(
                $this->idSite,
                'Goal 1 - Thank you',
                'title',
                'Thank you',
                'contains',
                $caseSensitive = false,
                $revenue = 10,
                $allowMultipleConversions = 1
            );
        }
    }

    private function trackSmartphoneVisits()
    {
        // first visit (with conversion)
        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);

        $t->setUserAgent('Mozilla/5.0 (Linux; Android 4.2.2; HTC Butterfly Build/JDQ39) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.59 Mobile Safari/537.36');

        $t->setUrl('http://example.org/index.htm');
        self::checkResponse($t->doTrackPageView('0'));

        $t->setForceVisitDateTime($this->getAdjustedDateTime(0.3));

        self::checkResponse($t->doTrackGoal($this->idGoal, $revenue = 42.256));

        // second visit (without conversion)
        $t = self::getTracker($this->idSite, $this->getAdjustedDateTime(0.2), $defaultInit = true);

        $t->setUserAgent('Mozilla/5.0 (Linux; Android 4.2.2; HTC Butterfly Build/JDQ39) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.59 Mobile Safari/537.36');

        $t->setUrl('http://example.org/anypage.php');
        self::checkResponse($t->doTrackPageView('mine'));


        // third visit (with conversion)
        $t = self::getTracker($this->idSite, $this->getAdjustedDateTime(0.2), $defaultInit = true);

        $t->setUserAgent('Mozilla/5.0 (iPhone; CPU iPhone OS 7_1 like Mac OS X) AppleWebKit/537.51.2 (KHTML, like Gecko) Mobile/11D167 iPhone6,1/N51AP Zite/2.6');

        $t->setUrl('http://example.org/anypage.php');
        self::checkResponse($t->doTrackPageView('mine'));

        $t->setForceVisitDateTime($this->getAdjustedDateTime(1));

        self::checkResponse($t->doTrackGoal($this->idGoal, $revenue = 0));
    }

    private function trackTabletVisits()
    {
        // first visit (with conversion)
        $t = self::getTracker($this->idSite, $this->getAdjustedDateTime(1), $defaultInit = true);

        $t->setUserAgent('Mozilla/5.0 (iPad; CPU OS 6_0_1 like Mac OS X) AppleWebKit/536.26 (KHTML, like Gecko) CriOS/31.0.1650.18 Mobile/10A523 Safari/8536.25');

        $t->setUrl('http://example.org/index.htm');
        self::checkResponse($t->doTrackPageView('0'));

        $t->setForceVisitDateTime($this->getAdjustedDateTime(1.6));

        self::checkResponse($t->doTrackGoal($this->idGoal, $revenue = 42.256));

        // second visit (without conversion)
        $t = self::getTracker($this->idSite, $this->getAdjustedDateTime(0.6), $defaultInit = true);

        $t->setUserAgent('Mozilla/5.0 (Linux; Android 4.2.2; SM-T310 Build/JDQ39) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.136 Safari/537.36');

        $t->setUrl('http://example.org/index.htm');
        self::checkResponse($t->doTrackPageView('0'));


        // third visit (with conversion)
        $t = self::getTracker($this->idSite, $this->getAdjustedDateTime(1.6), $defaultInit = true);

        $t->setUserAgent('Mozilla/5.0 (Linux; U; Android 2.3;en-us; ViewSonic-ViewPad7e build/ERE27) AppleWebKit/533.1 (KHTML, like Gecko) Version/4.0 Mobile Safari/533.1');

        $t->setUrl('http://example.org/anypage.php');
        self::checkResponse($t->doTrackPageView('mine'));

        $t->setForceVisitDateTime($this->getAdjustedDateTime(1.9));

        self::checkResponse($t->doTrackGoal($this->idGoal, $revenue = 0));
    }

    private function trackOtherVisits()
    {
        // unknown device visit (with conversion)
        $t = self::getTracker($this->idSite, $this->getAdjustedDateTime(1), $defaultInit = true);

        $t->setUserAgent('not detectable');

        $t->setUrl('http://example.org/anypage.php');
        self::checkResponse($t->doTrackPageView('mine'));

        $t->setForceVisitDateTime($this->getAdjustedDateTime(4));

        self::checkResponse($t->doTrackGoal($this->idGoal, $revenue = 42.256));

        // tv visit (without conversion)
        $t = self::getTracker($this->idSite, $this->getAdjustedDateTime(3), $defaultInit = true);

        $t->setUserAgent('WebKit/3.7.6, (CE-HTML/1.0 NETTV/3.3.0 NewB) PHILIPS-AVM-2013/2.19 (Philips, BDP5600, wired)');

        $t->setUrl('http://example.org/anypage.php');
        self::checkResponse($t->doTrackPageView('mine'));


        // feature phone visit (with conversion)
        $t = self::getTracker($this->idSite, $this->getAdjustedDateTime(4), $defaultInit = true);

        $t->setUserAgent('Fly_DS123/Q03C_MAUI_Browser/MIDP2.0 Configuration/CLDC-1.1');

        $t->setUrl('http://example.org/index.htm');
        self::checkResponse($t->doTrackPageView('0'));

        $t->setForceVisitDateTime($this->getAdjustedDateTime(4.2));

        self::checkResponse($t->doTrackGoal($this->idGoal, $revenue = 0));


        // desktop visit (with conversion)
        $t = self::getTracker($this->idSite, $this->getAdjustedDateTime(1.6), $defaultInit = true);

        $t->setUserAgent('Mozilla/5.0 (Windows NT 6.1; WOW64; Trident/7.0; Banca Caboto s.p.a.; rv:11.0) like Gecko');
        // The client hints below should change the OS to Windows 11 and browser to Edge 95.5.2
        $t->setClientHints('', 'Windows', '14.0.0', '" Not A;Brand";v="99", "Chromium";v="95", "Microsoft Edge";v="95"', '95.5.2');

        $t->setUrl('http://example.org/index.htm');
        self::checkResponse($t->doTrackPageView('0'));

        $t->setForceVisitDateTime($this->getAdjustedDateTime(1.9));

        self::checkResponse($t->doTrackGoal($this->idGoal, $revenue = 0));

        // car browser visit (without conversion)
        $t = self::getTracker($this->idSite, $this->getAdjustedDateTime(1.8), $defaultInit = true);

        $t->setUserAgent('Some Unknown UA');
        // The client hints below should change the OS to Android, browser to Chrome 95.5.2 and device type to car browser
        $t->setClientHints(
            'UltraOcta-T8',
            'Android',
            '14.0.0',
            '" Not A;Brand";v="99", "Chromium";v="95"',
            '95.5.2',
            '"Tablet", "Automotive"'
        );

        $t->setUrl('http://example.org/index.htm');
        self::checkResponse($t->doTrackPageView('0'));
    }


    private function getAdjustedDateTime($addition)
    {
        return Date::factory($this->dateTime)->addHour($addition)->getDatetime();
    }
}
