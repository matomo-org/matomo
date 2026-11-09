<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PrivacyManager\tests\Fixtures;

use Piwik\Common;
use Piwik\Db;
use Piwik\Policy\CnilPolicy;

/**
 * The visits of MultipleSitesMultipleVisitsFixture, with the CNIL policy enforced for site 5 and a consent stored on
 * some visits: on site 5, the visits with an even id are consented and the others hold none; on site 1, the visits
 * with an even id are consented, those with an id divisible by 3 refused, and the others hold none.
 *
 * The consent is written directly, as tracking only records it on a site measuring consent-aware analytics.
 */
class ConsentedVisitsFixture extends MultipleSitesMultipleVisitsFixture
{
    public const POLICY_SITE = 5;

    public function setUp(): void
    {
        parent::setUp();

        $logVisit = Common::prefixTable('log_visit');

        Db::query("UPDATE $logVisit SET consent = IF(idvisit % 2 = 0, 1, NULL) WHERE idsite = ?", [self::POLICY_SITE]);
        Db::query(
            "UPDATE $logVisit SET consent = CASE WHEN idvisit % 2 = 0 THEN 1 WHEN idvisit % 3 = 0 THEN 0 ELSE NULL END"
            . ' WHERE idsite = 1'
        );

        CnilPolicy::setActiveStatus(self::POLICY_SITE, true);
    }
}
