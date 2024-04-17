<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Plugin;

use Piwik\Container\StaticContainer;
use Piwik\Plugin;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\UpdateCheck\ReleaseChannel;

/**
 * @group Plugin
 * @group ReleaseChannels
 * @group ReleaseChannelsTest
 */
class ReleaseChannelsTest extends IntegrationTestCase
{
    /**
     * @var Plugin\ReleaseChannels
     */
    private $channels;

    public function setUp(): void
    {
        parent::setUp();

        if (!Fixture::siteCreated(1)) {
            Fixture::createWebsite('2015-01-01 00:00:00');
        }

        $this->channels = new Plugin\ReleaseChannels(StaticContainer::get('Piwik\Plugin\Manager'));
    }

    public function test_getAllReleaseChannels_shouldFindAllAvailableRelaseChannels()
    {
        $channels = $this->channels->getAllReleaseChannels();

        $this->assertCount(4, $channels);

        foreach ($channels as $channel) {
            $this->assertTrue($channel instanceof ReleaseChannel);
        }
    }

    public function test_getAllReleaseChannels_shouldOrderChannelsByOrderId()
    {
        $channels = $this->channels->getAllReleaseChannels();

        $lowest = 0;
        foreach ($channels as $channel) {
            $this->assertGreaterThanOrEqual($lowest, $channel->getOrder());
            $lowest = $channel->getOrder();
        }

        // to make sure we actually went into the for loop...
        $this->assertGreaterThan(0, $lowest);
    }

    /**
     * @dataProvider getTestValidReleaseChannelIds
     */
    public function test_isValidReleaseChannelId_shouldDetectIfReleaseChannelIsCorrectOrNot($expectedExists, $id)
    {
        $this->assertSame($expectedExists, $this->channels->isValidReleaseChannelId($id));
    }

    public function getTestValidReleaseChannelIds()
    {
        return array(
            array($exists = true, $id = 'latest_stable'),
            array($exists = true, $id = 'latest_beta'),
            array($exists = true, $id = 'latest_5x_stable'),
            array($exists = true, $id = 'laTest_stable'), // we do not check for exact match
            array($exists = false, $id = ''),
            array($exists = false, $id = 'latest'),
            array($exists = false, $id = 'stable'),
            array($exists = false, $id = 'lateststable'),
        );
    }

    public function getTestActiveReleaseChannel()
    {
        return array(
            array('latest_stable', 'latest_stable'),
            array('latest_4x_stable', 'latest_5x_stable'),
            array('latest_beta', 'latest_beta'),
            array('latest_beta', 'latEst_betA'),
            array('latest_stable', ''), // if nothing configured should return default (the one with lowest order)
            array('latest_stable', 'latest'), // if invalid id configured should return default (the one with lowest order)
        );
    }
}
