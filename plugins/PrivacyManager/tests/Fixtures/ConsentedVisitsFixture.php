<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PrivacyManager\tests\Fixtures;

use Piwik\Common;
use Piwik\Date;
use Piwik\Db;
use Piwik\Policy\CnilPolicy;

/**
 * The visits of MultipleSitesMultipleVisitsFixture, with the CNIL policy enforced for site 5 and a consent stored on
 * some visits: on site 5, the visits with an even id are consented and the others hold none; on site 1, the visits
 * with an even id are consented, those with an id divisible by 3 hold 0, and the others hold none.
 *
 * The consent is written directly, as tracking only records it on a site measuring consent-aware analytics.
 */
class ConsentedVisitsFixture extends MultipleSitesMultipleVisitsFixture
{
    public const POLICY_SITE = 5;

    /**
     * A site under the CNIL policy with visits of today, as the real-time widget only lists those: one consented,
     * one refused and one without a stored decision. Kept apart from the other sites so their data stays the same.
     */
    public const REAL_TIME_POLICY_SITE = 6;

    public function setUp(): void
    {
        parent::setUp();

        $this->setUpRealTimePolicySite();

        $logVisit = Common::prefixTable('log_visit');

        Db::query("UPDATE $logVisit SET consent = IF(idvisit % 2 = 0, 1, NULL) WHERE idsite = ?", [self::POLICY_SITE]);
        Db::query(
            "UPDATE $logVisit SET consent = CASE WHEN idvisit % 2 = 0 THEN 1 WHEN idvisit % 3 = 0 THEN 0 ELSE NULL END"
            . ' WHERE idsite = 1'
        );

        CnilPolicy::setActiveStatus(self::POLICY_SITE, true);
    }

    private function setUpRealTimePolicySite(): void
    {
        if (!self::siteCreated(self::REAL_TIME_POLICY_SITE)) {
            $idSite = self::createWebsite('2014-01-02 03:04:05', $ecommerce = 1, 'Real-time policy site');
            $this->assertSame(self::REAL_TIME_POLICY_SITE, $idSite);
        }

        $logVisit = Common::prefixTable('log_visit');

        foreach ([1, 0, null] as $consent) {
            $tracker = self::getTracker(self::REAL_TIME_POLICY_SITE, Date::now()->getDatetime(), $defaultInit = true);
            $tracker->setNewVisitorId();
            $tracker->setForceNewVisit();
            self::checkResponse($tracker->doTrackPageView('real-time page'));

            Db::query(
                "UPDATE $logVisit SET consent = ? WHERE idvisit = ?",
                [$consent, (int) Db::fetchOne("SELECT MAX(idvisit) FROM $logVisit")]
            );
        }

        CnilPolicy::setActiveStatus(self::REAL_TIME_POLICY_SITE, true);
    }
}
