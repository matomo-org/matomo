<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Marketplace\tests\Integration;

use Piwik\Plugins\Marketplace\API;
use Piwik\Plugins\Marketplace\LicenseKey;
use Piwik\Plugins\Marketplace\tests\Framework\Mock\Service;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\Plugins\Marketplace\Api\Service\Exception as ServiceException;
use Exception;

/**
 * @group Marketplace
 * @group ApiTest
 * @group Api
 * @group Plugins
 */
class ApiTest extends IntegrationTestCase
{
    /**
     * @var API
     */
    private $api;

    /**
     * @var Service
     */
    private $service;

    public function setUp()
    {
        parent::setUp();

        API::unsetInstance();
        $this->api = API::getInstance();

        Fixture::createSuperUser();
        if (!Fixture::siteCreated(1)) {
            Fixture::createWebsite('2012-01-01 00:00:00');
        }

        $this->setSuperUser();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage checkUserHasSuperUserAccess
     */
    public function test_deleteLicenseKey_requiresSuperUserAccess_IfUser()
    {
        $this->setUser();
        $this->api->deleteLicenseKey();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage checkUserHasSuperUserAccess
     */
    public function test_deleteLicenseKey_requiresSuperUserAccess_IfAnonymous()
    {
        $this->setAnonymousUser();
        $this->api->deleteLicenseKey();
    }

    public function test_deleteLicenseKey_shouldRemoveAnExistingKey()
    {
        $this->buildLicenseKey()->set('key');
        $this->assertHasLicenseKey();

        $this->api->deleteLicenseKey();

        $this->assertNotHasLicenseKey();
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage checkUserHasSuperUserAccess
     */
    public function test_saveLicenseKey_requiresSuperUserAccess_IfUser()
    {
        $this->setUser();
        $this->api->saveLicenseKey('key');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage checkUserHasSuperUserAccess
     */
    public function test_saveLicenseKey_requiresSuperUserAccess_IfAnonymous()
    {
        $this->setAnonymousUser();
        $this->api->saveLicenseKey('key');
    }

    /**
     * @expectedException \Exception
     * @expectedExceptionMessage Marketplace_ExceptionLinceseKeyIsNotValid
     */
    public function test_saveLicenseKey_shouldThrowException_IfTokenIsNotValid()
    {
        $this->service->returnFixture('v2.0_consumer_validate-access_token-notexistingtoken.json');
        $this->api->saveLicenseKey('key');
    }

    public function test_saveLicenseKey_shouldCallTheApiTheCorrectWay()
    {
        $this->service->returnFixture('v2.0_consumer-access_token-valid_but_expired.json');

        try {
            $this->api->saveLicenseKey('key123');
        } catch (Exception $e) {

        }

        // make sure calls API the correct way
        $this->assertSame('consumer/validate', $this->service->action);
        $this->assertSame(array(), $this->service->params);
        $this->assertSame('key123', $this->service->getAccessToken());
        $this->assertNotHasLicenseKey();
    }

    public function test_saveLicenseKey_shouldActuallySaveToken_IfValid()
    {
        $this->service->returnFixture('v2.0_consumer_validate-access_token-consumer1_paid2_custom1.json');
        $success = $this->api->saveLicenseKey('123licensekey');
        $this->assertTrue($success);

        $this->assertHasLicenseKey();
        $this->assertSame('123licensekey', $this->buildLicenseKey()->get());
    }

    /**
     * @expectedExceptionMessage Host not reachable
     * @expectedException \Piwik\Plugins\Marketplace\Api\Service\Exception
     */
    public function test_saveLicenseKey_shouldThrowException_IfConnectionToMarketplaceFailed()
    {
        $this->service->throwException(new ServiceException('Host not reachable', ServiceException::HTTP_ERROR));
        $success = $this->api->saveLicenseKey('123licensekey');
        $this->assertTrue($success);

        $this->assertHasLicenseKey();
        $this->assertSame('123licensekey', $this->buildLicenseKey()->get());
    }

    public function provideContainerConfig()
    {
        $this->service = new Service();

        return array(
            'Piwik\Access' => new FakeAccess(),
            'Piwik\Plugins\Marketplace\Api\Service' => $this->service
        );
    }

    protected function setSuperUser()
    {
        FakeAccess::clearAccess(true);
    }

    protected function setUser()
    {
        FakeAccess::clearAccess(false);
        FakeAccess::$idSitesView = array(1);
        FakeAccess::$idSitesAdmin = array();
        FakeAccess::$identity = 'aUser';
    }

    protected function setAnonymousUser()
    {
        FakeAccess::clearAccess();
        FakeAccess::$identity = 'anonymous';
    }

    protected function buildLicenseKey()
    {
        return new LicenseKey();
    }

    private function assertHasLicenseKey()
    {
        $this->assertTrue($this->buildLicenseKey()->has());
    }

    private function assertNotHasLicenseKey()
    {
        $this->assertFalse($this->buildLicenseKey()->has());
    }

}
