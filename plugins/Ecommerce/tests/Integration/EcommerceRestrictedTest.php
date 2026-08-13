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

    public function testReturnsCompliantMessageWhenEcommerceDisabled(): void
    {
        $note = EcommerceRestricted::getComplianceRequirementNote($this->nonEcommerceSite);

        $this->assertEquals('Ecommerce_EcommercePolicySettingCompliantSingle', $note);
    }

    public function testReturnsNonCompliantMessageWhenPolicyNotEnforced(): void
    {
        CnilPolicy::setActiveStatus(null, false);
        $note = EcommerceRestricted::getComplianceRequirementNote($this->ecommerceSite);

        $this->assertEquals('Ecommerce_EcommercePolicySettingNonCompliantNote', $note);
    }

    public function testReturnsRequirementNoteWithLinkWhenPolicyEnforced(): void
    {
        CnilPolicy::setActiveStatus(null, true);
        $_GET = [];

        $note = EcommerceRestricted::getComplianceRequirementNote($this->ecommerceSite);

        $this->assertEquals('Ecommerce_EcommercePolicySettingRequirementNote', $note);
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
