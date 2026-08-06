<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Ecommerce\tests\Integration;

use Piwik\Policy\CnilPolicy;
use Piwik\Plugins\Ecommerce\Settings\EcommerceRestricted;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class EcommerceRestrictedTest extends IntegrationTestCase
{
    private $ecommerceSite;
    private $nonEcommerceSite;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createSuperUser();
        $this->ecommerceSite = Fixture::createWebsite('2024-01-01 00:00:00', $ecommerce = 1);
        $this->nonEcommerceSite = Fixture::createWebsite('2024-01-02 00:00:00', $ecommerce = 0);
    }

    public function tearDown(): void
    {
        CnilPolicy::setActiveStatus(null, false);
        parent::tearDown();
    }

    /**
     * The description states what the policy requires, so it does not vary with the state of
     * the instance. Only the impact note does.
     */
    public function testReturnsTheSameDescriptionRegardlessOfEcommerceState(): void
    {
        CnilPolicy::setActiveStatus(null, true);

        $this->assertEquals(
            'Ecommerce_EcommercePolicyComplianceDescription',
            EcommerceRestricted::getComplianceRequirementNote($this->ecommerceSite)
        );
        $this->assertEquals(
            'Ecommerce_EcommercePolicyComplianceDescription',
            EcommerceRestricted::getComplianceRequirementNote($this->nonEcommerceSite)
        );

        CnilPolicy::setActiveStatus(null, false);

        $this->assertEquals(
            'Ecommerce_EcommercePolicyComplianceDescription',
            EcommerceRestricted::getComplianceRequirementNote($this->ecommerceSite)
        );
    }

    public function testReturnsNoImpactMessageWhenEcommerceDisabled(): void
    {
        $impact = EcommerceRestricted::getComplianceImpactNote($this->nonEcommerceSite);

        $this->assertEquals('Ecommerce_EcommercePolicyComplianceImpactNoEcommerceSingle', $impact);
    }

    public function testReturnsImpactMessageWhenEcommerceEnabled(): void
    {
        CnilPolicy::setActiveStatus(null, true);

        $impact = EcommerceRestricted::getComplianceImpactNote($this->ecommerceSite);

        $this->assertEquals('Ecommerce_EcommercePolicyComplianceImpact', $impact);
    }

    public function testIsCompliantWhenEcommerceDisabled(): void
    {
        CnilPolicy::setActiveStatus(null, true);

        $this->assertTrue(
            EcommerceRestricted::isCompliant(CnilPolicy::class, $this->nonEcommerceSite)
        );
    }

    public function testIsCompliantWhenEcommerceEnabledAndPolicyEnforced(): void
    {
        CnilPolicy::setActiveStatus(null, true);

        $this->assertTrue(
            EcommerceRestricted::isCompliant(CnilPolicy::class, $this->ecommerceSite)
        );
    }

    public function testIsCompliantWhenEcommerceEnabledAndPolicyNotEnforced(): void
    {
        CnilPolicy::setActiveStatus(null, false);

        $this->assertFalse(
            EcommerceRestricted::isCompliant(CnilPolicy::class, $this->ecommerceSite)
        );
    }
}
