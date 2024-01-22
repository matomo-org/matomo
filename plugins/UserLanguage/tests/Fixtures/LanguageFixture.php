<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\UserLanguage\tests\Fixtures;


use Piwik\Tests\Framework\Fixture;
use Piwik\Date;

class LanguageFixture extends Fixture
{
    public $dateTime = '2014-09-04 00:00:00';
    public $idSite = 1;

    public function setUp(): void
    {
        $this->setUpWebsite();
        $this->trackVisits();
    }

    public function tearDown(): void
    {
    }

    private function setUpWebsite()
    {
        if (!self::siteCreated($this->idSite)) {
            $idSite = self::createWebsite($this->dateTime);
            $this->assertSame($this->idSite, $idSite);
        }
    }

    private function getBrowserLangs()
    {
        return array(
            'fr-be', 'ar_QA', 'fr-ch', 'pl', 'pl', 'th_TH', 'zh_SG', 'eu_ES',
            'sr_RS', 'el,fi', 'fr,en-us,en;q=', 'fr-be', 'en,en-us,en;q=',
            'de,en-us,en;q=', 'cs_CZ', 'pl,en-us,en;q=',
            'kpe_LR', 'en,en-us,en;q=',
        );
    }

    private function trackVisits()
    {

        $tracker = self::getTracker(
            $this->idSite,
            $this->dateTime,
            $defaultInit = false
        );
        $tracker->setTokenAuth(self::getTokenAuth());

        $hour = 1;
        foreach ($this->getBrowserLangs() as $browserLang) {

            $tracker->setForceVisitDateTime(
                Date::factory($this->dateTime)->addHour($hour++)->getDatetime()
            );

            $tracker->setBrowserLanguage($browserLang);

            self::checkResponse($tracker->doTrackPageView("Viewing homepage"));
        }
    }
}
