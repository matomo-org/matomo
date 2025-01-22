<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PrivacyManager\tests\System;

use Piwik\Common;
use Piwik\Db;
use Piwik\Plugins\PrivacyManager\tests\Fixtures\RandomizedConfigIdVisitsFixture;
use Piwik\Tests\Framework\TestCase\SystemTestCase;

/**
 * @group PrivacyManager
 * @group RandomisedConfigIdTest
 * @group Plugins
 */
class RandomisedConfigIdTest extends SystemTestCase
{
    /**
     * @var RandomizedConfigIdVisitsFixture
     */
    public static $fixture = null; // initialized below class definition

    public static $dateTimeNormalConfig = '2015-01-11'; // based on value in RandomizedConfigIdVisitsFixture
    public static $dateTimeRandomisedConfig = '2015-01-12'; // based on value in RandomizedConfigIdVisitsFixture + 1 day

    public function testNormalConfigIdBehaviour()
    {
        // four sets of visits with an hour break each which creates a new visit (as over the default visit inactivity)
        $count = Db::fetchOne('SELECT COUNT(idvisitor) FROM ' . Common::prefixTable('log_visit') . ' WHERE DATE(visit_last_action_time) = DATE("' . self::$dateTimeNormalConfig . '")');
        $this->assertEquals(4, $count);

        // 2 standard visits
        // 3 visits with 2 actions -> 9 LLVA connections as each visit also stores the URL
        // 2 user id visits
        // 1 ecommerce since conversion is not an action here
        // total => 14 rows of LLVA
        $count = Db::fetchOne('SELECT COUNT(idlink_va) FROM ' . Common::prefixTable('log_link_visit_action') . ' WHERE DATE(server_time) = DATE("' . self::$dateTimeNormalConfig . '")');
        $this->assertEquals(14, $count);

        // 1 rows with user set
        $count = Db::fetchOne('SELECT COUNT(user_id) FROM ' . Common::prefixTable('log_visit') . ' WHERE DATE(visit_last_action_time) = DATE("' . self::$dateTimeNormalConfig . '")');
        $this->assertEquals(1, $count);

        // 3 rows of ecommerce conversion
        $count = Db::fetchOne('SELECT COUNT(1) FROM ' . Common::prefixTable('log_conversion') . ' WHERE DATE(server_time) = DATE("' . self::$dateTimeNormalConfig . '")');
        $this->assertEquals(3, $count);
    }

    public function testConfigIdRandomised()
    {
        // 2 standard visits -> 2
        // 3 visits with 2 actions -> 9 unique config IDs as each visit is an action itself
        // 2 visits with set user id -> 2
        // 3 ecommerce orders + order page visit -> 4
        // total => 17
        $count = Db::fetchOne('SELECT COUNT(idvisitor) FROM ' . Common::prefixTable('log_visit') . ' WHERE DATE(visit_last_action_time) = DATE("' . self::$dateTimeRandomisedConfig . '")');
        $this->assertEquals(17, $count);

        // 2 standard visits
        // 3 visits with 2 actions -> 9 LLVA connections as each visit also stores the URL
        // 2 user_id visits
        // 1 ecommerce since conversion is not an action here
        // total => 14 rows of LLVA
        $count = Db::fetchOne('SELECT COUNT(idlink_va) FROM ' . Common::prefixTable('log_link_visit_action') . ' WHERE DATE(server_time) = DATE("' . self::$dateTimeRandomisedConfig . '")');
        $this->assertEquals(14, $count);

        // 2 rows with user set
        $count = Db::fetchOne('SELECT COUNT(user_id) FROM ' . Common::prefixTable('log_visit') . ' WHERE DATE(visit_last_action_time) = DATE("' . self::$dateTimeRandomisedConfig . '")');
        $this->assertEquals(2, $count);

        // 0 rows of conversion
        $count = Db::fetchOne('SELECT COUNT(1) FROM ' . Common::prefixTable('log_conversion') . ' WHERE DATE(server_time) = DATE("' . self::$dateTimeRandomisedConfig . '")');
        $this->assertEquals(0, $count);
    }
}

RandomisedConfigIdTest::$fixture = new RandomizedConfigIdVisitsFixture();
