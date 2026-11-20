<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Fixtures;

use Piwik\DbHelper;
use Piwik\Tests\Framework\Fixture;

/**
 * Fixture that adds one site and tracks one visit whose referrer url path holds characters that
 * cannot be part of an XML name. Reports keep such a path as content, so a renderer has to handle
 * it wherever report content is used.
 */
class OneVisitWithReferrerUrlThatIsNoXmlName extends Fixture
{
    public $idSite = 1;
    public $dateTime = '2010-03-06 11:22:33';

    /**
     * Path of the tracked referrer url. Holds the characters an XML name cannot hold, so that a
     * renderer treating it as a name rather than as content is detectable.
     */
    public $referrerUrlPath = 'x="><injected/><row y="';

    public function setUp(): void
    {
        Fixture::createSuperUser();
        DbHelper::createAnonymousUser();
        $this->setUpWebsites();
        $this->trackVisits();
    }

    public function tearDown(): void
    {
        // empty
    }

    private function setUpWebsites()
    {
        if (!self::siteCreated($this->idSite)) {
            self::createWebsite($this->dateTime);
        }
    }

    private function trackVisits()
    {
        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);

        $t->setUrl('http://example.org/index.htm');
        $t->setUrlReferrer('http://referrer.example/' . $this->referrerUrlPath);
        self::checkResponse($t->doTrackPageView('page title'));
    }
}
