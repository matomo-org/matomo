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
    private const RATIONALE = 'Ecommerce_EcommercePolicyComplianceDescriptionRationale';

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createSuperUser();
    }

    public function tearDown(): void
    {
        CnilPolicy::setActiveStatus(null, false);
        parent::tearDown();
    }

    public function testDescriptionSaysEcommerceIsEnabledForSiteUsingEcommerce(): void
    {
        $idSite = $this->createEcommerceSite();

        $this->assertEquals(
            $this->description('Ecommerce_EcommercePolicyComplianceDescription'),
            EcommerceRestricted::getWhatItDoes($idSite)
        );
    }

    public function testDescriptionSaysEcommerceIsNotEnabledForSiteNotUsingEcommerce(): void
    {
        $idSite = $this->createNonEcommerceSite();

        $this->assertEquals(
            $this->description('Ecommerce_EcommercePolicyComplianceDescriptionNoEcommerceSingle'),
            EcommerceRestricted::getWhatItDoes($idSite)
        );
    }

    public function testDescriptionSaysEcommerceIsEnabledForAllSitesWhenAnySiteUsesEcommerce(): void
    {
        $this->createNonEcommerceSite();
        $this->createEcommerceSite();

        $this->assertEquals(
            $this->description('Ecommerce_EcommercePolicyComplianceDescription'),
            EcommerceRestricted::getWhatItDoes(null)
        );
    }

    public function testDescriptionSaysEcommerceIsNotEnabledForAllSitesWhenNoSiteUsesEcommerce(): void
    {
        $this->createNonEcommerceSite();
        $this->createNonEcommerceSite();

        $this->assertEquals(
            $this->description('Ecommerce_EcommercePolicyComplianceDescriptionNoEcommerceAll'),
            EcommerceRestricted::getWhatItDoes(null)
        );
    }

    public function testReturnsNoImpactMessageWhenEcommerceDisabled(): void
    {
        $idSite = $this->createNonEcommerceSite();

        $this->assertEquals(
            'Ecommerce_EcommercePolicyComplianceImpactNoEcommerceSingle',
            EcommerceRestricted::getImpact($idSite)
        );
    }

    public function testReturnsImpactMessageWhenEcommerceEnabled(): void
    {
        CnilPolicy::setActiveStatus(null, true);
        $idSite = $this->createEcommerceSite();

        $this->assertEquals(
            'Ecommerce_EcommercePolicyComplianceImpact',
            EcommerceRestricted::getImpact($idSite)
        );
    }

    public function testReturnsNoImpactMessageForAllSitesWhenNoSiteUsesEcommerce(): void
    {
        $this->createNonEcommerceSite();
        $this->createNonEcommerceSite();

        $this->assertEquals(
            'Ecommerce_EcommercePolicyComplianceImpactNoEcommerceAll',
            EcommerceRestricted::getImpact(null)
        );
    }

    /**
     * The description and the impact are two columns of the same row, so they must never describe
     * different states. Both take their keys from getComplianceStateTranslationKeys().
     *
     * @dataProvider getComplianceStateScenarios
     */
    public function testDescriptionAndImpactAlwaysDescribeTheSameState(
        array $ecommerceFlags,
        bool $forAllSites,
        string $expectedSuffix
    ): void {
        $idSite = null;

        foreach ($ecommerceFlags as $hasEcommerce) {
            $idSite = $hasEcommerce ? $this->createEcommerceSite() : $this->createNonEcommerceSite();
        }

        $idSiteToCheck = $forAllSites ? null : $idSite;

        $this->assertEquals(
            $this->description('Ecommerce_EcommercePolicyComplianceDescription' . $expectedSuffix),
            EcommerceRestricted::getWhatItDoes($idSiteToCheck)
        );
        $this->assertEquals(
            'Ecommerce_EcommercePolicyComplianceImpact' . $expectedSuffix,
            EcommerceRestricted::getImpact($idSiteToCheck)
        );
    }

    public function getComplianceStateScenarios(): iterable
    {
        yield 'single site using ecommerce' => [[true], false, ''];
        yield 'single site not using ecommerce' => [[false], false, 'NoEcommerceSingle'];
        yield 'all sites, one uses ecommerce' => [[false, true], true, ''];
        yield 'all sites, none use ecommerce' => [[false, false], true, 'NoEcommerceAll'];
    }

    public function testIsCompliantWhenEcommerceDisabled(): void
    {
        CnilPolicy::setActiveStatus(null, true);
        $idSite = $this->createNonEcommerceSite();

        $this->assertTrue(EcommerceRestricted::isCompliant(CnilPolicy::class, $idSite));
    }

    public function testIsCompliantWhenEcommerceEnabledAndPolicyEnforced(): void
    {
        CnilPolicy::setActiveStatus(null, true);
        $idSite = $this->createEcommerceSite();

        $this->assertTrue(EcommerceRestricted::isCompliant(CnilPolicy::class, $idSite));
    }

    public function testIsCompliantWhenEcommerceEnabledAndPolicyNotEnforced(): void
    {
        CnilPolicy::setActiveStatus(null, false);
        $idSite = $this->createEcommerceSite();

        $this->assertFalse(EcommerceRestricted::isCompliant(CnilPolicy::class, $idSite));
    }

    /**
     * Translations are not loaded in integration tests, so Piwik::translate() returns the key. The
     * description is built from two keys joined by the paragraph separator.
     */
    private function description(string $key): string
    {
        return $key . '<br /><br />' . self::RATIONALE;
    }

    private function createEcommerceSite(): int
    {
        return Fixture::createWebsite('2024-01-01 00:00:00', $ecommerce = 1);
    }

    private function createNonEcommerceSite(): int
    {
        return Fixture::createWebsite('2024-01-02 00:00:00', $ecommerce = 0);
    }
}
