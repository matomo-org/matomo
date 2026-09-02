<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\WebsiteMeasurable\tests\Integration;

use Piwik\Container\StaticContainer;
use Piwik\Plugin\SettingsProvider;
use Piwik\Policy\CnilPolicy;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * Ecommerce is the one setting a compliance policy restricts without determining: the site stays an
 * ecommerce site, so the field keeps its stored value and stays editable, and only says where the
 * restriction comes from.
 *
 * @group WebsiteMeasurable
 * @group Plugins
 */
class EcommercePolicyNoteTest extends IntegrationTestCase
{
    /** @var int */
    private $idSite;

    public function setUp(): void
    {
        parent::setUp();

        $this->idSite = Fixture::createWebsite('2024-01-01 00:00:00', $ecommerce = 1);
    }

    public function tearDown(): void
    {
        CnilPolicy::setActiveStatus(null, false);
        CnilPolicy::setActiveStatus($this->idSite, false);

        parent::tearDown();
    }

    public function testNoNoteIsShownWhileNoPolicyIsEnforced(): void
    {
        $inlineHelp = $this->getEcommerceInlineHelp();

        $this->assertStringNotContainsString('EcommercePolicyRestrictedNote', $inlineHelp);
    }

    public function testInstanceWideEnforcementIsReportedOnTheWebsitesOwnSetting(): void
    {
        CnilPolicy::setActiveStatus(null, true);

        $inlineHelp = $this->getEcommerceInlineHelp();

        // asserted on the translation keys, which the test environment leaves untranslated
        $this->assertStringContainsString('Ecommerce_EcommercePolicyRestrictedNoteInstance', $inlineHelp);
        $this->assertStringContainsString('module=PrivacyManager', $inlineHelp);
        $this->assertStringContainsString('action=compliance', $inlineHelp);
        $this->assertStringContainsString('idSite=' . $this->idSite, $inlineHelp);
    }

    public function testWebsiteLevelEnforcementIsReportedAsSuch(): void
    {
        CnilPolicy::setActiveStatus($this->idSite, true);

        $inlineHelp = $this->getEcommerceInlineHelp();

        $this->assertStringContainsString('Ecommerce_EcommercePolicyRestrictedNoteWebsite', $inlineHelp);
    }

    public function testTheFieldKeepsItsStoredValueAndStaysEditable(): void
    {
        CnilPolicy::setActiveStatus(null, true);

        $ecommerce = $this->getEcommerceSetting();

        // the policy restricts what ecommerce tracking collects, not whether ecommerce is on
        $this->assertSame(1, (int) $ecommerce->getValue());
        $this->assertArrayNotHasKey('disabled', $ecommerce->configureField()->uiControlAttributes);
    }

    private function getEcommerceSetting()
    {
        /** @var SettingsProvider $provider */
        $provider = StaticContainer::get(SettingsProvider::class);

        return $provider->getMeasurableSettings('WebsiteMeasurable', $this->idSite)->ecommerce;
    }

    private function getEcommerceInlineHelp(): string
    {
        return $this->getEcommerceSetting()->configureField()->inlineHelp;
    }
}
