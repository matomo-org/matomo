<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExampleLogTables\tests\Fixtures;

use Piwik\Date;
use Piwik\Plugins\ExampleLogTables\Tracker\UserAttributesRequestProcessor;
use Piwik\Tests\Framework\Fixture;

class VisitsWithUserIdAndCustomData extends Fixture
{
    public $dateTime = '2018-02-01 11:22:33';
    public $idSite = 1;

    /**
     * The attributes each tracked user sends along with their visits. The plugin's own
     * RequestProcessor is what turns these into rows in its two tables -- the fixture only tracks
     * visits, exactly as a real site would.
     *
     * @var array<string, array{gender: string, group: string, groupIsAdmin: bool}>
     */
    private const USER_ATTRIBUTES = [
        'user1' => ['gender' => 'men', 'group' => 'admin', 'groupIsAdmin' => true],
        'user2' => ['gender' => 'women', 'group' => 'user', 'groupIsAdmin' => false],
        'user3' => ['gender' => 'women', 'group' => 'admin', 'groupIsAdmin' => true],
        'user4' => ['gender' => 'men', 'group' => '', 'groupIsAdmin' => false],
    ];

    /**
     * @var string[]
     */
    private static array $countryCodes = ['CA', 'CN', 'DE', 'ES', 'FR', 'IE', 'IN', 'IT', 'MX', 'PT', 'RU', 'GB', 'US'];

    public function setUp(): void
    {
        if (!self::siteCreated($this->idSite)) {
            self::createWebsite($this->dateTime);
        }

        $this->trackVisits();
    }

    private function trackVisits(): void
    {
        $t = self::getTracker($this->idSite, $this->dateTime, defaultInit: true);
        $t->setTokenAuth(self::getTokenAuth());
        $t->enableBulkTracking();

        foreach (['user1', 'user2', 'user3', 'user4', null] as $key => $userId) {
            for ($numVisits = 0; $numVisits < ($key + 1) * 10; $numVisits++) {
                $visitDateTime = Date::factory($this->dateTime)->addHour($numVisits)->getDatetime();
                $t->setForceVisitDateTime($visitDateTime);
                $t->setUserId($userId);
                $t->setVisitorId(str_pad($numVisits . $key, 16, 'a'));
                $t->setCountry(self::$countryCodes[$numVisits % count(self::$countryCodes)]);

                if ($numVisits % 5 == 0) {
                    $this->setUserAttributes($t, $userId);
                    $t->doTrackSiteSearch('some search term' . $numVisits);
                }

                if ($numVisits % 4 == 0) {
                    $this->setUserAttributes($t, $userId);
                    $t->doTrackEvent('Event action ' . $numVisits, 'event cat ' . $numVisits);
                }

                if ($numVisits % 7 == 0) {
                    $this->setUserAttributes($t, $userId);
                    $t->doTrackContentInteraction('click', 'slider ' . $numVisits % 4);
                }

                if ($numVisits % 7 == 4) {
                    $this->setUserAttributes($t, $userId);
                    $t->doTrackAction('http://out.link', 'outlink');
                }

                if ($numVisits % 5 == 3) {
                    $t->setEcommerceView(
                        'SKU VERY nice indeed ' . ($numVisits % 3),
                        'PRODUCT name ' . ($numVisits % 4),
                        'category ' . ($numVisits % 5),
                        $numVisits * 2.79
                    );
                }

                $t->setForceNewVisit();
                $t->setUrl('http://example.org/my/dir/page' . ($numVisits % 4));

                $visitDateTime = Date::factory($this->dateTime)->addHour($numVisits + 6)->getDatetime();
                $t->setForceVisitDateTime($visitDateTime);

                if ($numVisits % 7 == 0) {
                    $this->setUserAttributes($t, $userId);
                    $t->doTrackAction('http://example.org/download.pdf', 'download');
                }

                $this->setUserAttributes($t, $userId);
                self::assertTrue($t->doTrackPageView('incredible title ' . ($numVisits % 3)));

                if ($numVisits % 9 == 0) {
                    $t->setForceVisitDateTime(Date::factory($this->dateTime)->addHour($numVisits + 6.1)->getDatetime());
                    $t->addEcommerceItem(
                        'SKU VERY nice indeed ' . ($numVisits % 3),
                        'PRODUCT name ' . ($numVisits % 4),
                        'category ' . ($numVisits % 5),
                        $numVisits * 2.79
                    );
                    $this->setUserAttributes($t, $userId);
                    self::assertTrue($t->doTrackEcommerceCartUpdate($numVisits * 17));
                }
            }
        }

        self::checkBulkTrackingResponse($t->doBulkTrack());
    }

    /**
     * Sends the tracking parameters the plugin reads. They are cleared after every tracking
     * request, so they have to be set again before each one.
     */
    private function setUserAttributes(\MatomoTracker $t, ?string $userId): void
    {
        if (null === $userId || !isset(self::USER_ATTRIBUTES[$userId])) {
            return;
        }

        $attributes = self::USER_ATTRIBUTES[$userId];

        $t->setCustomTrackingParameter(UserAttributesRequestProcessor::PARAM_GENDER, $attributes['gender']);
        $t->setCustomTrackingParameter(UserAttributesRequestProcessor::PARAM_GROUP, $attributes['group']);
        $isAdmin = $attributes['groupIsAdmin'] ? '1' : '0';
        $t->setCustomTrackingParameter(UserAttributesRequestProcessor::PARAM_GROUP_IS_ADMIN, $isAdmin);
    }
}
