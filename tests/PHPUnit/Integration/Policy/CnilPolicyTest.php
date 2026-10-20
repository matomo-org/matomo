<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\Policy;

use Piwik\Piwik;
use Piwik\Plugin\Manager;
use Piwik\Policy\CnilPolicy;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\Mock\Plugin\Manager as MockManager;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class CloudAwareCnilPolicy extends CnilPolicy
{
    /** @var array<string> */
    public static $activatedPlugins = [];

    protected static function getPluginManagerInstance(): Manager
    {
        $manager = new MockManager();
        $manager->setActivatedPlugins(self::$activatedPlugins);
        return $manager;
    }
}

/**
 * The Cloud plugin is never activated on CI, so the Cloud only branch of the granular
 * description needs a policy whose plugin manager can be swapped out.
 *
 * @group Core
 */
class CnilPolicyTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        // the copy under test is assembled from translations, so asserting on it needs them loaded
        Fixture::loadAllTranslations();
    }

    public function tearDown(): void
    {
        CloudAwareCnilPolicy::$activatedPlugins = [];
        Fixture::resetTranslations();
        parent::tearDown();
    }

    public function testGranularDescriptionLinksToTheDpaOnCloud(): void
    {
        CloudAwareCnilPolicy::$activatedPlugins = ['Cloud'];

        $description = CloudAwareCnilPolicy::getGranularDescription();

        $this->assertStringContainsString('https://matomo.org/matomo-cloud-dpa/', $description);
        $this->assertStringContainsString('mtm_medium=App.PrivacyManager.compliance', $description);
        $this->assertStringContainsString('<b>Matomo Cloud DPA</b>', $description);
    }

    public function testGranularDescriptionPutsTheDpaLineBeforeTheThirdPartyPluginWarning(): void
    {
        CloudAwareCnilPolicy::$activatedPlugins = ['Cloud'];

        $description = CloudAwareCnilPolicy::getGranularDescription();
        $warning = Piwik::translate('General_ComplianceCNILWarning');

        $this->assertLessThan(
            strpos($description, $warning),
            strpos($description, 'matomo-cloud-dpa'),
            'the DPA sentence must come before the third party plugin warning'
        );
    }

    public function testGranularDescriptionOmitsTheDpaOnPremise(): void
    {
        $description = CloudAwareCnilPolicy::getGranularDescription();

        $this->assertStringNotContainsString('matomo-cloud-dpa', $description);
        $this->assertStringNotContainsString('Matomo Cloud DPA', $description);
    }

    public function testLegacyDescriptionNeverMentionsTheDpa(): void
    {
        CloudAwareCnilPolicy::$activatedPlugins = ['Cloud'];

        $this->assertStringNotContainsString('matomo-cloud-dpa', CloudAwareCnilPolicy::getDescription());
    }
}
