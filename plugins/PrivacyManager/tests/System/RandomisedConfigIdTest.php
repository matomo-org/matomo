<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PrivacyManager\tests\System;

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

    public function testConfigIdRandomised()
    {
        // temporary nop to populate the database
        $this->assertTrue(true);
    }
}

RandomisedConfigIdTest::$fixture = new RandomizedConfigIdVisitsFixture();
