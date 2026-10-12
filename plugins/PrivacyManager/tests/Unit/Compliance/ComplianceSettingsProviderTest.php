<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\PrivacyManager\tests\Unit\Compliance;

use Piwik\Plugins\PrivacyManager\Compliance\ComplianceSettingsProvider;

/**
 * @group PrivacyManager
 * @group Plugins
 */
class ComplianceSettingsProviderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ComplianceSettingsProvider
     */
    private $provider;

    public function setUp(): void
    {
        parent::setUp();

        $this->provider = new ComplianceSettingsProvider();
    }

    public function testDiffPolicySettingsReturnsNothingWhenEverySettingIsUnchanged(): void
    {
        $payload = $this->payload([
            $this->setting('A.One', false, ComplianceSettingsProvider::STATUS_NON_COMPLIANT),
            $this->setting('A.Two', true, ComplianceSettingsProvider::STATUS_ENFORCED),
        ]);

        $this->assertSame([], $this->provider->diffPolicySettings($payload, $payload));
    }

    public function testDiffPolicySettingsReportsASettingThatStartedBeingEnforced(): void
    {
        $before = $this->payload([
            $this->setting('A.One', false, ComplianceSettingsProvider::STATUS_NON_COMPLIANT),
            $this->setting('A.Two', false, ComplianceSettingsProvider::STATUS_NON_COMPLIANT),
        ]);
        $after = $this->payload([
            $this->setting('A.One', true, ComplianceSettingsProvider::STATUS_ENFORCED),
            $this->setting('A.Two', false, ComplianceSettingsProvider::STATUS_NON_COMPLIANT),
        ]);

        $this->assertSame([
            [
                'id' => 'A.One',
                'name' => 'A.One name',
                'enforced' => true,
                'previousEnforced' => false,
                'status' => ComplianceSettingsProvider::STATUS_ENFORCED,
                'previousStatus' => ComplianceSettingsProvider::STATUS_NON_COMPLIANT,
            ],
        ], $this->provider->diffPolicySettings($before, $after));
    }

    public function testDiffPolicySettingsReportsAStatusChangeWithoutAnEnforcementChange(): void
    {
        $before = $this->payload([
            $this->setting('A.One', false, ComplianceSettingsProvider::STATUS_NON_COMPLIANT),
        ]);
        $after = $this->payload([
            $this->setting('A.One', false, ComplianceSettingsProvider::STATUS_COMPLIANT),
        ]);

        $changes = $this->provider->diffPolicySettings($before, $after);

        $this->assertCount(1, $changes);
        $this->assertSame(ComplianceSettingsProvider::STATUS_COMPLIANT, $changes[0]['status']);
        $this->assertSame(ComplianceSettingsProvider::STATUS_NON_COMPLIANT, $changes[0]['previousStatus']);
    }

    public function testDiffPolicySettingsIgnoresASettingThatIsOnlyPresentInOnePayload(): void
    {
        $before = $this->payload([
            $this->setting('A.One', false, ComplianceSettingsProvider::STATUS_NON_COMPLIANT),
        ]);
        $after = $this->payload([
            $this->setting('A.One', false, ComplianceSettingsProvider::STATUS_NON_COMPLIANT),
            $this->setting('A.Two', true, ComplianceSettingsProvider::STATUS_ENFORCED),
        ]);

        $this->assertSame([], $this->provider->diffPolicySettings($before, $after));
        $this->assertSame([], $this->provider->diffPolicySettings($after, $before));
    }

    public function testDiffPolicySettingsMatchesSettingsOnTheirIdRatherThanTheirPosition(): void
    {
        $before = $this->payload([
            $this->setting('A.One', false, ComplianceSettingsProvider::STATUS_NON_COMPLIANT),
            $this->setting('A.Two', true, ComplianceSettingsProvider::STATUS_ENFORCED),
        ]);
        $after = $this->payload([
            $this->setting('A.Two', true, ComplianceSettingsProvider::STATUS_ENFORCED),
            $this->setting('A.One', false, ComplianceSettingsProvider::STATUS_NON_COMPLIANT),
        ]);

        $this->assertSame([], $this->provider->diffPolicySettings($before, $after));
    }

    /**
     * An externally managed setting reports a null enforcement state, which must not be
     * confused with "not enforced" when it starts or stops being compliant.
     */
    public function testDiffPolicySettingsReportsAnExternallyManagedSettingWithoutEnforcementState(): void
    {
        $before = $this->payload([
            $this->setting('Core.ThirdPartyCookies', null, ComplianceSettingsProvider::STATUS_NON_COMPLIANT),
        ]);
        $after = $this->payload([
            $this->setting('Core.ThirdPartyCookies', null, ComplianceSettingsProvider::STATUS_ON_BY_DEFAULT),
        ]);

        $changes = $this->provider->diffPolicySettings($before, $after);

        $this->assertCount(1, $changes);
        $this->assertNull($changes[0]['enforced']);
        $this->assertNull($changes[0]['previousEnforced']);
    }

    /**
     * @param array<int, array<string, mixed>> $settings
     * @return array<string, mixed>
     */
    private function payload(array $settings): array
    {
        return [
            'policy' => 'cnil_v1',
            'settings' => $settings,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function setting(string $id, ?bool $enforced, string $status): array
    {
        return [
            'id' => $id,
            'name' => $id . ' name',
            'enforced' => $enforced,
            'status' => $status,
        ];
    }
}
