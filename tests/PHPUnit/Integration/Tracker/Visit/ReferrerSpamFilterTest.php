<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Tracker\Visit;

use Piwik\Cache;
use Piwik\Option;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Tracker\Request;
use Piwik\Tracker\Visit\ReferrerSpamFilter;

/**
 * @group Tracker
 * @group Visit
 */
class ReferrerSpamFilterTest extends IntegrationTestCase
{
    /**
     * @var ReferrerSpamFilter
     */
    private $filter;

    public function setUp(): void
    {
        parent::setUp();

        Cache::flushAll();
        $this->filter = new ReferrerSpamFilter();
    }

    public function tearDown(): void
    {
        parent::tearDown();

        Cache::flushAll();
    }

    public function testShouldDetectSpam()
    {
        $request = new Request(array(
            'urlref' => 'semalt.com',
        ));

        $this->assertTrue($this->filter->isSpam($request));
    }

    public function testShouldIgnoreValidReferrers()
    {
        $request = new Request(array(
            'urlref' => 'google.com',
        ));

        $this->assertFalse($this->filter->isSpam($request));
    }

    public function testShouldIgnoreRequestsWithEmptyReferrers()
    {
        $request = new Request(array());

        $this->assertFalse($this->filter->isSpam($request));
    }

    public function testShouldLoadSpammerListFromOptionsIfExists()
    {
        // We store google.com in the spammer blacklist
        $list = serialize(array(
            'google.com',
        ));
        Option::set(ReferrerSpamFilter::OPTION_STORAGE_NAME, $list);

        $request = new Request(array(
            'urlref' => 'semalt.com',
        ));
        $this->assertFalse($this->filter->isSpam($request));

        // Now Google is blacklisted
        $request = new Request(array(
            'urlref' => 'google.com',
        ));
        $this->assertTrue($this->filter->isSpam($request));

        Option::delete(ReferrerSpamFilter::OPTION_STORAGE_NAME);
    }
}
