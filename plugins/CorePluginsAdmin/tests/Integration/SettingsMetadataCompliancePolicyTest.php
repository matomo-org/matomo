<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CorePluginsAdmin\tests\Integration;

use Piwik\Plugins\CorePluginsAdmin\SettingsMetadata;
use Piwik\Plugins\Live\SystemSettings;
use Piwik\Policy\CnilPolicy;
use Piwik\Policy\Exceptions\CompliancePolicyViolationException;
use Piwik\Policy\PolicyManager;
use Piwik\Settings\Interfaces\PolicyComparisonInterface;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * How a settings form behaves for a setting a compliance policy controls: it has to show the value
 * the policy puts in effect rather than the stored one, stop offering to change it, and refuse a
 * value that would break the policy.
 *
 * @group CorePluginsAdmin
 * @group Plugins
 */
class SettingsMetadataCompliancePolicyTest extends IntegrationTestCase
{
    /** @var SettingsMetadata */
    private $settingsMetadata;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createWebsite('2020-01-01 00:00:00');

        $this->settingsMetadata = new SettingsMetadata();
    }

    public function tearDown(): void
    {
        CnilPolicy::setActiveStatus(null, false);

        parent::tearDown();
    }

    public function testStoredValueIsShownWhileNoPolicyIsEnforced(): void
    {
        $setting = $this->getVisitorLogSetting();
        $setting->setValue(false);

        $result = $this->settingsMetadata->formatSetting($setting);

        $this->assertFalse($result['value']);
        $this->assertArrayNotHasKey('extraMetadata', $result);
        $this->assertArrayNotHasKey('disabled', $result['uiControlAttributes']);
    }

    public function testEnforcedValueIsShownAndTheFieldIsLocked(): void
    {
        $setting = $this->getVisitorLogSetting();
        $setting->setValue(false);

        CnilPolicy::setActiveStatus(null, true);

        $result = $this->settingsMetadata->formatSetting($setting);

        // the policy requires the visits log to be off, so that is what the screen has to show
        $this->assertTrue($result['value']);
        $this->assertSame('disabled', $result['uiControlAttributes']['disabled']);

        $controlled = $result['extraMetadata']['compliancePolicyControlled'][CnilPolicy::getName()];
        $this->assertTrue($controlled['requiredValue']);
        $this->assertTrue($controlled['effectiveValue']);
        $this->assertSame(PolicyComparisonInterface::POLICY_CONSTRAINT_EXACT, $controlled['constraintType']);
        $this->assertSame(PolicyComparisonInterface::ENFORCEMENT_SCOPE_INSTANCE, $controlled['scope']);
        $this->assertSame(CnilPolicy::getTitle(), $controlled['policyTitle']);
    }

    public function testWebsiteLevelEnforcementIsReportedAsSuch(): void
    {
        CnilPolicy::setActiveStatus(1, true);

        $controlling = PolicyManager::getCompliancePoliciesControllingASetting(
            'disable_visitor_log',
            1,
            PolicyManager::SETTING_TYPE_SYSTEM
        );

        $this->assertSame(
            PolicyComparisonInterface::ENFORCEMENT_SCOPE_SITE,
            $controlling[CnilPolicy::getName()]['scope']
        );

        CnilPolicy::setActiveStatus(1, false);
    }

    public function testAValueThePolicyEnforcesIsNotStoredOverTheUsersOwn(): void
    {
        $settings = new SystemSettings();
        $this->getVisitorLogSetting($settings)->setValue(false);
        $settings->save();

        CnilPolicy::setActiveStatus(null, true);

        // the locked field posts the enforced value back; storing it would silently replace the
        // value the user had configured before the policy started applying
        $this->settingsMetadata->setPluginSettings(
            ['Live' => new SystemSettings()],
            ['Live' => [['name' => 'disable_visitor_log', 'value' => true]]]
        );

        CnilPolicy::setActiveStatus(null, false);

        $this->assertFalse($this->getVisitorLogSetting()->getValue());
    }

    public function testANonCompliantValueIsRejected(): void
    {
        CnilPolicy::setActiveStatus(null, true);

        $this->expectException(CompliancePolicyViolationException::class);

        $this->settingsMetadata->setPluginSettings(
            ['Live' => new SystemSettings()],
            ['Live' => [['name' => 'disable_visitor_log', 'value' => false]]]
        );
    }

    private function getVisitorLogSetting(?SystemSettings $settings = null)
    {
        return ($settings ?? new SystemSettings())->disableVisitorLog;
    }
}
