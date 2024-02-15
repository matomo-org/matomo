<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Ecommerce\tests\Fixtures;

use Piwik\Date;
use Piwik\Tests\Framework\Fixture;
use Piwik\Plugins\Goals\API as GoalsAPI;

class AbandonedCartWithoutConversions extends Fixture
{
    public $idSite = 1;
    public $idGoalStandard = 1;
    public $dateTime = '2011-04-05 00:11:42';

    public function setUp(): void
    {
        parent::setUp();

        $this->setUpWebsitesAndGoals();
        $this->trackVisits();
    }

    private function setUpWebsitesAndGoals()
    {
        if (!self::siteCreated($this->idSite)) {
            $this->idSite = self::createWebsite($this->dateTime, $ecommerce = 1, 'test site');
        }

        if (!self::goalExists($this->idSite, $this->idGoalStandard)) {
            GoalsAPI::getInstance()->addGoal(
                $this->idSite,
                'title match, triggered NEVER',
                'title',
                'saldkfjaslkdfjsalkdjf',
                'contains',
                $caseSensitive = false,
                $revenue = 10,
                $allowMultipleConversions = true
            );
        }
    }

    private function trackVisits()
    {
        // visit without ecommerce
        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setUrl('http://piwik.net/here/we/go');
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->getDatetime());
        self::checkResponse($t->doTrackPageView('one page visit'));

        // visit with abandoned cart
        $t = self::getTracker($this->idSite, $this->dateTime, $defaultInit = true);
        $t->setUrl('http://piwik.net/here/we/go');
        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(1)->getDatetime());
        self::checkResponse($t->doTrackPageView('one page visit'));

        $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour(1.1)->getDatetime());
        $t->addEcommerceItem($sku = 'SKU IN ABANDONED CART ONE', $name = 'PRODUCT ONE LEFT in cart', $category = '', $price = 500.11111112, $quantity = 2);
        self::checkResponse($t->doTrackEcommerceCartUpdate($grandTotal = 1000));
    }
}
