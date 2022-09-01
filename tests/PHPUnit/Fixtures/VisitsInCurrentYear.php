<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Fixtures;

use Piwik\Tests\Framework\Fixture;

/**
 * This fixture adds one website and tracks two visits by one visitor.
 */
class VisitsInCurrentYear extends Fixture
{
    public $idSite = 1;

    public function setUp(): void
    {
        $this->setUpWebsite();
        $this->trackVisits();
    }

    public function tearDown(): void
    {
        // empty
    }

    private function setUpWebsite()
    {
        if (!self::siteCreated($idSite = 1)) {
            self::createWebsite('2018-01-01 15:00:00');
        }
    }

    private function trackVisits()
    {
        $idSite = $this->idSite;

        // Record 1st visit today
        $t = self::getTracker($idSite, date('Y-m-d H:i:s'), $defaultInit = true);
        $t->setUrl('http://example.org/index.htm?excluded_Parameter=SHOULD_NOT_DISPLAY&parameter=Should display');
        $t->setUrlReferrer('http://referrer.com/page.htm?param=valuewith some spaces');
        self::checkResponse($t->doTrackPageView('incredible title!'));

        // Record 2nd visit 7 days ago
        $t = self::getTracker($idSite, date('Y-m-d H:i:s', strtotime('-7days')), $defaultInit = true);
        $t->setUrl('http://example.org/index.htm?excluded_Parameter=SHOULD_NOT_DISPLAY&parameter=Should display');
        $t->setUrlReferrer('http://referrer.com/page.htm');
        self::checkResponse($t->doTrackPageView('incredible!'));

        // Record 3rd visit 1 month ago
        $t = self::getTracker($idSite, date('Y-m-d H:i:s', strtotime('-1month')), $defaultInit = true);
        $t->setUrl('http://example.org/store/purchase.htm');
        $t->setUrlReferrer('http://search.yahoo.com/search?p=purchase');
        self::checkResponse($t->doTrackPageView('Checkout/Purchasing...'));

        // Record 4th visit 1 year ago
        $t = self::getTracker($idSite, date('Y-m-d H:i:s', strtotime('-1year')), $defaultInit = true);
        $t->setUrl('http://example.org/shop/product.htm');
        self::checkResponse($t->doTrackPageView('Visit product'));
    }
}