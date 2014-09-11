<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\UserSettings\tests\Fixtures;


use Piwik\Tests\Fixture;
use Piwik\Date;

class LanguageFixture extends Fixture
{
    public $dateTime = '2014-09-04 00:00:00';
    public $idSite = 1;

    public function setUp()
    {
        $this->setUpWebsite();
        $this->trackVisits();
    }

    public function tearDown()
    {

    }

    private function setUpWebsite()
    {
        if (!self::siteCreated($this->idSite)) {
            $idSite = self::createWebsite($this->dateTime);
            $this->assertSame($this->idSite, $idSite);
        }
    }

    private function getBrowserLangs() {
        return array(
            'fr-be', 'ar_QA', 'fr-ch', 'ha_GH', 'th_TH', 'zh_SG', 'eu_ES', 
            'sr_CS', 'el,fi', 'ug_CN', 'hi_IN', 'nso_ZA', 'cs_CZ', 'pl,en-us,en;q=', 
            'kpe_LR', 'mk_MK', 'en_NA'
        );
    }

    private function getCountries() {
        return array(
            'be', 'ca', 'ch', 'cn', 'de', 'es', 'fr', 'gb', 'gr',
            'ie', 'in,', 'it', 'mx', 'pl', 'pt', 'ru', 'us'
        );
    }

    private function trackVisits() {

        $tracker = self::getTracker(
            $this->idSite,
            $this->dateTime,
            $defaultInit = false
        );

        $countryForBrowserLang = array_combine($this->getCountries(), $this->getBrowserLangs());

        $hour = 1;
        foreach ($countryForBrowserLang as $country => $browserLang) {

            $tracker->setForceVisitDateTime(
                Date::factory($this->dateTime)->addHour($hour++)->getDatetime()
            );
            $tracker->setCountry($country);
            $tracker->setBrowserLanguage($browserLang);

            self::checkResponse($tracker->doTrackPageView("Viewing homepage"));
        }

    }

} 