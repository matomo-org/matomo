<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Fixtures;

use Piwik\Date;
use Piwik\Tests\Fixture;

/**
 * Adds two sites and tracks several visits all in the past.
 */
class SiteVisitsWithInvalidation extends Fixture
{
    public $dateTimeFirstDateWebsite = '2010-03-06 01:22:33';

    public $idSite = 1;

    public function setUp()
    {
        $this->setUpWebsitesAndGoals();
        $this->trackVisits();
    }

    public function tearDown()
    {
        // empty
    }

    public function setUpWebsitesAndGoals()
    {
        if (!self::siteCreated($idSite = 1)) {
            self::createWebsite($this->dateTimeFirstDateWebsite);
        }
    }

    protected function trackVisits()
    {
        /**
         * Track Visits normal date for the 2 websites
         */
        // WEBSITE 1
        $t = self::getTracker($this->idSite, $this->dateTimeFirstDateWebsite, $defaultInit = true);
        $t->setUrl('http://example.org/category/Page1');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/category/Page2');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/category/Page3');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/Home');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/Contact');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/Contact/ThankYou');
        self::checkResponse($t->doTrackPageView('Hello'));

    }



    public function trackMoreVisits()
    {
        /**
         * Track Visits normal date for the 2 websites
         */
        // WEBSITE 1
        $t = self::getTracker($this->idSite, $this->dateTimeFirstDateWebsite, $defaultInit = true);
        $t->setIp('156.15.13.12');
        $t->setUrl('http://example.org/category/Page1');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/category/Page2');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/category/Page3');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/Home');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/Contact');
        self::checkResponse($t->doTrackPageView('Hello'));
        $t->setUrl('http://example.org/Contact/ThankYou');
        self::checkResponse($t->doTrackPageView('Hello'));

    }
}