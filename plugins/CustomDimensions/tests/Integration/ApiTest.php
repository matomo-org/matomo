<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomDimensions\tests\Integration;

use Piwik\Plugins\CustomDimensions\API;
use Piwik\Plugins\CustomDimensions\CustomDimensions;
use Piwik\Plugins\CustomDimensions\Dao\Configuration;
use Piwik\Plugins\CustomDimensions\tests\Integration\Dao\ConfigurationTest;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Exception;

/**
 * @group CustomDimensions
 * @group ApiTest
 * @group Plugins
 */
class ApiTest extends IntegrationTestCase
{
    /**
     * @var API
     */
    private $api;

    public function setUp(): void
    {
        parent::setUp();

        $this->api = API::getInstance();

        Fixture::createSuperUser();
        if (!Fixture::siteCreated(1)) {
            Fixture::createWebsite('2012-01-01 00:00:00');
        }

        $this->setSuperUser();
    }

    /**
     * @dataProvider getInvalidConfigForNewDimensions
     */
    public function testConfigureNewDimensionShouldFailWhenThereIsAnError($dimension)
    {
        try {
            $this->api->configureNewCustomDimension($idSite = 1, $dimension['name'], $dimension['scope'], $dimension['active'], $dimension['extractions'], $dimension['case_sensitive']);
        } catch (Exception $e) {
            self::assertStringContainsString($dimension['message'], $e->getMessage());
            return;
        }

        $this->fail('An expected exception has not been thrown');
    }

    public function getInvalidConfigForNewDimensions()
    {
        return array(
            array(array('message' => 'CustomDimensions_NameAllowedCharacters',          'name' => 'Inval/\\nam&<b>e</b>', 'scope' => CustomDimensions::SCOPE_ACTION, 'active' => '1', 'extractions' => array(), 'case_sensitive' => '1')),
            array(array('message' => "Invalid value 'anyScOPe' for 'scope'",            'name' => 'Valid Name äöü',       'scope' => 'anyScOPe',                     'active' => '1', 'extractions' => array(), 'case_sensitive' => '1')),
            array(array('message' => "Invalid value '2' for 'active' specified",        'name' => 'Valid Name äöü',       'scope' => CustomDimensions::SCOPE_ACTION, 'active' => '2', 'extractions' => array(), 'case_sensitive' => '1')),
            array(array('message' => 'extractions has to be an array',                  'name' => 'Valid Name äöü',       'scope' => CustomDimensions::SCOPE_ACTION, 'active' => '1', 'extractions' => 5, 'case_sensitive' => '1')),
            array(array('message' => "Extractions can be used only in scope 'action'",  'name' => 'Valid Name äöü',       'scope' => CustomDimensions::SCOPE_VISIT,  'active' => '1', 'extractions' => array(array('dimension' => 'url', 'pattern' => 'i(.*)')), 'case_sensitive' => '1')),
            array(array('message' => "Invalid value '4' for 'caseSensitive' specified", 'name' => 'Valid Name äöü',       'scope' => CustomDimensions::SCOPE_ACTION, 'active' => '1', 'extractions' => array(), 'case_sensitive' => '4')),
        );
    }

    public function testConfigureNewDimensionShouldReturnCreatedIdOnSuccess()
    {
        $id = $this->api->configureNewCustomDimension($idSite = 1, 'Valid Name äöü', CustomDimensions::SCOPE_ACTION, '1', array(array('dimension' => 'urlparam', 'pattern' => 'test')), '0');

        $this->assertSame(1, $id);

        // verify created
        $dimensions = $this->api->getConfiguredCustomDimensions(1);

        $expectedDimension = array(
            'idcustomdimension' => '1',
            'idsite' => '1',
            'name' => 'Valid Name äöü',
            'index' => '1',
            'scope' => 'action',
            'active' => true,
            'extractions' => array(
                array ('dimension' => 'urlparam', 'pattern' => 'test')
            ),
            'case_sensitive' => false
        );
        $this->assertSame(array($expectedDimension), $dimensions);
    }

    public function testConfigureNewDimensionShouldFailWhenNotHavingAdminPermissions()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('checkUserHasWriteAccess');

        $this->setUser();
        $this->api->configureNewCustomDimension($idSite = 1, 'Valid Name äöü', CustomDimensions::SCOPE_VISIT, '1', array(array('dimension' => 'urlparam', 'pattern' => 'test')));
    }

    /**
     * @dataProvider getInvalidConfigForExistingDimensions
     */
    public function testConfigureExistingCustomDimensionShouldFailWhenThereIsAnError($dimension)
    {
        try {
            $this->test_configureNewDimension_shouldReturnCreatedIdOnSuccess();
            $this->api->configureExistingCustomDimension($dimension['id'], $idSite = 1, $dimension['name'], $dimension['active'], $dimension['extractions']);
        } catch (Exception $e) {
            self::assertStringContainsString($dimension['message'], $e->getMessage());
            return;
        }

        $this->fail('An expected exception has not been thrown');
    }

    public function getInvalidConfigForExistingDimensions()
    {
        return array(
            array(array('message' => "CustomDimensions_ExceptionDimensionDoesNotExist", 'id' => '999', 'name' => 'Valid Name äöü', 'active' => '1', 'extractions' => array())),
            array(array('message' => 'CustomDimensions_NameAllowedCharacters',          'id' => '1',   'name' => 'Inval/\\nam&<b>e</b>', 'active' => '1', 'extractions' => array())),
            array(array('message' => "Invalid value '2' for 'active' specified",        'id' => '1',   'name' => 'Valid Name äöü', 'active' => '2', 'extractions' => array())),
            array(array('message' => 'extractions has to be an array',                  'id' => '1',   'name' => 'Valid Name äöü', 'active' => '1', 'extractions' => 5)),
        );
    }

    public function testConfigureExistingCustomDimensionShouldReturnNothingOnSuccess()
    {
        $this->test_configureNewDimension_shouldReturnCreatedIdOnSuccess();
        $return = $this->api->configureExistingCustomDimension($id = 1, $idSite = 1, 'New Valid Name äöü', '0', array(array('dimension' => 'urlparam', 'pattern' => 'newtest')), $caseSensitive = true);

        $this->assertNull($return);

        // verify updated
        $dimensions = $this->api->getConfiguredCustomDimensions(1);
        $this->assertCount(1, $dimensions);
        $this->assertSame('New Valid Name äöü', $dimensions[0]['name']);
        $this->assertFalse($dimensions[0]['active']);
        $this->assertTrue($dimensions[0]['case_sensitive']);
        $this->assertSame('newtest', $dimensions[0]['extractions'][0]['pattern']);
    }

    public function testConfigureExistingCustomDimensionShouldNotChangeCaseSensitiveIfNoValuePassed()
    {
        $this->test_configureNewDimension_shouldReturnCreatedIdOnSuccess();

        // verify created with false
        $dimensions = $this->api->getConfiguredCustomDimensions(1);
        $this->assertFalse($dimensions[0]['case_sensitive']);

        $return = $this->api->configureExistingCustomDimension($id = 1, $idSite = 1, 'New Valid Name äöü', '0', array(array('dimension' => 'urlparam', 'pattern' => 'newtest')));

        $this->assertNull($return);

        // verify after update still false
        $dimensions = $this->api->getConfiguredCustomDimensions(1);
        $this->assertFalse($dimensions[0]['case_sensitive']);
    }

    public function testConfigureExistingCustomDimensionShouldThrowExceptionWhenTryingToSetExtractionsForNonActionScope()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Extractions can be used only in scope \'action\'');

        $id = $this->api->configureNewCustomDimension($idSite = 1, 'Name', CustomDimensions::SCOPE_VISIT, '1');
        $this->api->configureExistingCustomDimension($id, $idSite, 'Name', '0', array(array('dimension' => 'urlparam', 'pattern' => 'newtest')));
    }

    public function testConfigureExistingCustomDimensionShouldFailWhenNotHavingAdminPermissions()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('checkUserHasWriteAccess');

        $this->setUser();
        $this->api->configureExistingCustomDimension($id = 1, $idSite = 1, 'New Valid Name äöü', '0', array(array('dimension' => 'urlparam', 'pattern' => 'newtest')));
    }

    public function testGetConfiguredCustomDimensionsShouldFailWhenNotHavingAdminPermissions()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('checkUserHasViewAccess');

        $this->setAnonymousUser();
        $this->api->getConfiguredCustomDimensions($idSite = 1);
    }

    public function testGetAvailableScopesShouldFailWhenNotHavingAdminPermissions()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('checkUserHasViewAccess');

        $this->setAnonymousUser();
        $this->api->getAvailableScopes($idSite = 1);
    }

    public function testGetAvailableExtractionDimensionsShouldFailWhenNotHavingAdminPermissions()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('checkUserHasSomeWriteAccess');

        $this->setUser();
        $this->api->getAvailableExtractionDimensions();
    }

    public function testGetCustomDimensionShouldFailWhenNotHavingViewPermissions()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('checkUserHasViewAccess');

        $this->setAnonymousUser();
        $this->api->getCustomDimension($idDimension = 1, $idSite = 1, $period = 'day', $date = 'today');
    }

    public function testGetConfiguredCustomDimensionsHavingScopeShouldFindEntriesHavingScopeAndSite()
    {
        ConfigurationTest::createManyCustomDimensionCasesFor(new Configuration());

        $dimensions = $this->api->getConfiguredCustomDimensionsHavingScope($idSite = 1, $scope = 'action');

        $this->assertCount(2, $dimensions);

        foreach ($dimensions as $dimension) {
            $this->assertSame('1', $dimension['idsite']);
            $this->assertSame('action', $dimension['scope']);
            $this->assertTrue(is_bool($dimension['active']));
        }

        $dimensions = $this->api->getConfiguredCustomDimensionsHavingScope($idSite = 1, $scope = 'visit');

        $this->assertCount(3, $dimensions);

        foreach ($dimensions as $dimension) {
            $this->assertSame('1', $dimension['idsite']);
            $this->assertSame('visit', $dimension['scope']);
            $this->assertTrue(is_bool($dimension['active']));
        }

        // nothing matches
        $dimensions = $this->api->getConfiguredCustomDimensionsHavingScope($idSite = 1, $scope = 'nothing');
        $this->assertSame(array(), $dimensions);
    }

    public function provideContainerConfig()
    {
        return array(
            'Piwik\Access' => new FakeAccess()
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
}
