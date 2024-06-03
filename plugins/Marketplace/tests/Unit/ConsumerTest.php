<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Unit;

use Piwik\Plugins\Marketplace\tests\Framework\Mock\Consumer;
use Piwik\Plugins\Marketplace\tests\Framework\Mock\Service;
use Piwik\Plugins\Marketplace\tests\Framework\Mock\Consumer as ConsumerBuilder;

/**
 * @group Marketplace
 * @group ConsumerTest
 * @group Consumer
 * @group Plugins
 */
class ConsumerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Service
     */
    private $service;

    public function setUp(): void
    {
        $this->service = new Service();
    }

    /**
     * @dataProvider getConsumerNotAuthenticated
     */
    public function testIsValidConsumerShouldReturnFalseWhenNotAuthenticedBecauseNoTokenSetOrInvalidToken($fixture)
    {
        $this->service->returnFixture($fixture);
        $this->assertFalse($this->buildConsumer()->isValidConsumer());
    }

    /**
     * @dataProvider getConsumerAuthenticated
     */
    public function testIsValidConsumerShouldReturnTrueWhenValidTokenGiven($fixture)
    {
        $this->service->returnFixture($fixture);
        $this->assertTrue($this->buildConsumer()->isValidConsumer());
    }

    public function testGetConsumerShouldReturnConsumerInformationWhenValid()
    {
        $this->service->returnFixture('v2.0_consumer-access_token-consumer1_paid2_custom1.json');

        $expected = array (
            'licenses' =>
                array (
                    0 =>
                        array (
                            'startDate' => '2014-05-27 04:46:05',
                            'endDate' => '2014-06-01 06:22:35',
                            'nextPaymentDate' => null,
                            'status' => 'Cancelled',
                            'productType' => 'Up to 4 users',
                            'isValid' => false,
                            'isExceeded' => false,
                            'isExpiredSoon' => false,
                            'plugin' => array('name' => 'PaidPlugin1', 'displayName' => 'Paid Plugin 1', 'htmlUrl' => 'https://plugins.piwik.org/PaidPlugin1'),
                        ),
                    1 =>
                        array (
                            'startDate' => '2016-05-20 04:46:05',
                            'endDate' => '2030-05-27 11:03:06',
                            'nextPaymentDate' => '2030-05-27 11:03:06',
                            'status' => 'Active',
                            'productType' => '5 to 15 users',
                            'isValid' => true,
                            'isExceeded' => null,
                            'isExpiredSoon' => false,
                            'plugin' => array('name' => 'PaidPlugin2', 'displayName' => 'Paid Plugin 2', 'htmlUrl' => 'https://plugins.piwik.org/PaidPlugin2'),
                        ),
                    2 =>
                        array (
                            'startDate' => '2016-05-25 04:46:05',
                            'endDate' => '2030-06-03 11:03:06',
                            'nextPaymentDate' => '2030-06-03 11:03:06',
                            'status' => 'Active',
                            'productType' => 'Up to 4 users',
                            'isValid' => true,
                            'isExceeded' => null,
                            'isExpiredSoon' => false,
                            'plugin' => array('name' => 'CustomPlugin1', 'displayName' => 'Custom Plugin 1', 'htmlUrl' => ''),
                        ),
            ),
            'loginUrl' => 'https://shop.piwik.org/my-account',
        );
        $this->assertEquals($expected, $this->buildConsumer()->getConsumer());
    }

    public function testGetConsumerShouldNotReturnAnyInformationWhenNotAuthenticated()
    {
        $this->service->returnFixture('v2.0_consumer-access_token-notexistingtoken.json');

        $this->assertSame(array(), $this->buildConsumer()->getConsumer());
    }

    public function testGetConsumerShouldNotReturnInformationWhenAuthenticatedButNoLicense()
    {
        $this->service->returnFixture('v2.0_consumer-access_token-validbutnolicense.json');

        $expected = array(
            'licenses' => array(),
            'loginUrl' => 'https://shop.piwik.org/my-account'
        );

        $this->assertSame($expected, $this->buildConsumer()->getConsumer());
    }

    public function getConsumerNotAuthenticated()
    {
        return array(
            array('v2.0_consumer_validate.json'),
            array('v2.0_consumer_validate-access_token-notexistingtoken.json'),
        );
    }

    public function getConsumerAuthenticated()
    {
        return array(
            array('v2.0_consumer_validate-access_token-consumer1_paid2_custom1.json'),
            array('v2.0_consumer_validate-access_token-consumer2_paid1.json'),
            array('v2.0_consumer_validate-access_token-validbutnolicense.json') // valid token but no license
        );
    }

    public function testBuildInvalidLicenseKey()
    {
        $isValid = Consumer::buildNoLicense()->isValidConsumer();

        $this->assertFalse($isValid);
    }

    public function testBuildValidLicenseKey()
    {
        $isValid = Consumer::buildValidLicense()->isValidConsumer();

        $this->assertTrue($isValid);
    }

    private function buildConsumer()
    {
        return ConsumerBuilder::build($this->service);
    }
}
