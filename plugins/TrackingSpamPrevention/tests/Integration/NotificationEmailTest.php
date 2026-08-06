<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\TrackingSpamPrevention\tests\Integration;

use Piwik\Plugins\TrackingSpamPrevention\BanIpNotificationEmail;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group TrackingSpamPrevention
 * @group BlockedGeoIpTest
 * @group Plugins
 */
class NotificationEmailTest extends IntegrationTestCase
{
    /**
     * @var BanIpNotificationEmail
     */
    private $email;

    public function setUp(): void
    {
        parent::setUp();

        $this->email = new BanIpNotificationEmail();
    }

    public function test_send_noValidEmail()
    {
        $this->assertNull($this->email->send('10.10.10.10/32', '10.10.10.10', 'foo', 100, ['test'], '2020-12-14 01:42:27'));
    }

    public function test_send_ValidEmail()
    {
        $this->assertEquals(
            'This is for your information. The following IP was banned because visit tried to track more than 112 actions:

"10.10.10.10/12"

URL: localhost
Current date (UTC): 2020-12-14 01:42:27
IP as detected in header: 127.0.0.1
GET request info: []
POST request info: []
Geo IP info: {"test":"foo","bar":"baz"}',
            trim(
                $this->email->send(
                    '10.10.10.10/12',
                    '127.0.0.1',
                    'foo@matomo.org',
                    112,
                    ['test' => 'foo', 'bar' => 'baz'],
                    '2020-12-14 01:42:27'
                )
            )
        );
    }
}
