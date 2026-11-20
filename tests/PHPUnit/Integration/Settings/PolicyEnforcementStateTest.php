<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Settings;

use Piwik\Common;
use Piwik\Config;
use Piwik\Db;
use Piwik\Settings\PolicyEnforcementState;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * The enforcement state of a policy-controlled setting needs three states: enforced, not
 * enforced, and "no state stored, so follow the policy". These tests cover that the storage
 * really distinguishes them, in particular that removing a state removes the row rather than
 * storing a falsy value.
 *
 * @group PolicySettings
 */
class PolicyEnforcementStateTest extends IntegrationTestCase
{
    private const PLUGIN_NAME = 'PrivacyManager';
    private const SETTING_NAME = 'FakeSetting_policy_enforced';

    /**
     * @var PolicyEnforcementState
     */
    private $state;

    public function setUp(): void
    {
        parent::setUp();

        Fixture::createWebsite('2020-01-01 00:00:00');
        Fixture::createWebsite('2020-01-01 00:00:00');

        $this->state = new PolicyEnforcementState(self::PLUGIN_NAME, self::SETTING_NAME);
    }

    public function testNoStateIsStoredInitially()
    {
        $this->assertNull($this->state->get());
        $this->assertNull($this->state->get(1));
    }

    public function testStoresAndReadsBackTheInstanceWideState()
    {
        $this->state->set(null, true);
        $this->assertTrue($this->state->get());

        $this->state->set(null, false);
        $this->assertFalse($this->state->get());
    }

    public function testStoresAndReadsBackASiteState()
    {
        $this->state->set(1, true);

        $this->assertTrue($this->state->get(1));
        $this->assertNull($this->state->get(2));
        $this->assertNull($this->state->get());
    }

    public function testInstanceAndSiteStatesAreStoredIndependently()
    {
        $this->state->set(null, true);
        $this->state->set(1, false);

        $this->assertTrue($this->state->get());
        $this->assertFalse($this->state->get(1));
        $this->assertNull($this->state->get(2));
    }

    public function testStoringFalseIsDistinguishableFromNoStateAtAll()
    {
        $this->state->set(null, false);

        $this->assertFalse($this->state->get());
        $this->assertNotNull($this->state->get());
        $this->assertSame(1, $this->countStoredInstanceRows());
    }

    public function testRemovingTheInstanceWideStateRemovesTheRow()
    {
        $this->state->set(null, true);
        $this->assertSame(1, $this->countStoredInstanceRows());

        $this->state->set(null, null);

        $this->assertNull($this->state->get());
        $this->assertSame(0, $this->countStoredInstanceRows());
    }

    public function testRemovingASiteStateRemovesTheRow()
    {
        $this->state->set(1, true);
        $this->assertSame(1, $this->countStoredSiteRows(1));

        $this->state->set(1, null);

        $this->assertNull($this->state->get(1));
        $this->assertSame(0, $this->countStoredSiteRows(1));
    }

    public function testRemovingAStateKeepsOtherScopesIntact()
    {
        $this->state->set(null, true);
        $this->state->set(1, false);

        $this->state->set(1, null);

        $this->assertTrue($this->state->get());
        $this->assertNull($this->state->get(1));
    }

    public function testRemovingAStateThatWasNeverStoredIsHarmless()
    {
        $this->state->set(null, null);

        $this->assertNull($this->state->get());
        $this->assertSame(0, $this->countStoredInstanceRows());
    }

    public function testDoesNotCollideWithTheEnforcementStateOfAnotherSetting()
    {
        $other = new PolicyEnforcementState(self::PLUGIN_NAME, 'OtherFakeSetting_policy_enforced');

        $this->state->set(null, true);

        $this->assertNull($other->get());

        $other->set(null, false);

        $this->assertTrue($this->state->get());
        $this->assertFalse($other->get());
    }

    public function testDoesNotCollideWithTheSameSettingInAnotherPlugin()
    {
        $other = new PolicyEnforcementState('Live', self::SETTING_NAME);

        $this->state->set(null, true);

        $this->assertNull($other->get());
    }

    public function testStateCanBePinnedInTheConfigFile()
    {
        $this->assertNull($this->state->get());
        $this->assertTrue($this->state->isWritableByCurrentUser());

        Config::getInstance()->{self::PLUGIN_NAME} = [self::SETTING_NAME => 1];

        $this->assertTrue($this->state->get());
        $this->assertFalse($this->state->isWritableByCurrentUser());
    }

    public function testWritingAPinnedStateIsRejected()
    {
        Config::getInstance()->{self::PLUGIN_NAME} = [self::SETTING_NAME => 1];

        $this->expectExceptionMessage('CoreAdminHome_PluginSettingChangeNotAllowed');

        $this->state->set(null, false);
    }

    private function countStoredInstanceRows(): int
    {
        return (int) Db::fetchOne(
            'SELECT COUNT(*) FROM ' . Common::prefixTable('plugin_setting')
            . ' WHERE plugin_name = ? AND user_login = ? AND setting_name = ?',
            [self::PLUGIN_NAME, '', self::SETTING_NAME]
        );
    }

    private function countStoredSiteRows(int $idSite): int
    {
        return (int) Db::fetchOne(
            'SELECT COUNT(*) FROM ' . Common::prefixTable('site_setting')
            . ' WHERE idsite = ? AND plugin_name = ? AND setting_name = ?',
            [$idSite, self::PLUGIN_NAME, self::SETTING_NAME]
        );
    }
}
