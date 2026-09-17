<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\PrivacyManager\tests\Integration;

use Piwik\Changes\Model as ChangesModel;
use Piwik\Config;
use Piwik\Plugins\PrivacyManager\PrivacyManager;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * The What's New entry announcing the granular compliance settings is only shown while the feature
 * it announces is available.
 *
 * @group PrivacyManager
 * @group Plugins
 */
class WhatsNewChangeTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        (new ChangesModel())->addChanges('PrivacyManager');
    }

    public function testChangesFileAnnouncesTheGranularComplianceSettingsOnce(): void
    {
        $titles = array_column($this->getChangesFile(), 'title');

        // The filter matches the entry by title, so the two must not drift apart.
        self::assertCount(1, array_keys($titles, PrivacyManager::CHANGE_TITLE_GRANULAR_COMPLIANCE, true));
    }

    public function testGranularComplianceChangeIsShownWhenTheFeatureIsEnabled(): void
    {
        $this->setGranularComplianceFeature('enabled');

        self::assertContains(PrivacyManager::CHANGE_TITLE_GRANULAR_COMPLIANCE, $this->getChangeTitles());
    }

    public function testGranularComplianceChangeIsHiddenWhenTheFeatureIsDisabled(): void
    {
        $this->setGranularComplianceFeature('disabled');

        self::assertNotContains(PrivacyManager::CHANGE_TITLE_GRANULAR_COMPLIANCE, $this->getChangeTitles());
    }

    public function testFilteringKeepsTheOtherChangesAsAList(): void
    {
        $this->setGranularComplianceFeature('disabled');

        $changes = (new ChangesModel())->getChangeItems();

        self::assertNotEmpty($changes);
        self::assertSame(range(0, count($changes) - 1), array_keys($changes));
        self::assertContains('User Opt-Out Improvements', array_column($changes, 'title'));
    }

    /**
     * @return array<int, string>
     */
    private function getChangeTitles(): array
    {
        // A new model per call: the change items are cached per instance.
        return array_column((new ChangesModel())->getChangeItems(), 'title');
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function getChangesFile(): array
    {
        $changes = json_decode(file_get_contents(__DIR__ . '/../../changes.json'), true);

        self::assertIsArray($changes);

        return $changes;
    }

    private function setGranularComplianceFeature(string $state): void
    {
        Config::getInstance()->FeatureFlags = ['GranularPrivacyCompliance_feature' => $state];
    }
}
