<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Login\tests\Fixtures;

use Piwik\Changes\Model as ChangesModel;
use Piwik\Plugins\PrivacyManager\SystemSettings;
use Piwik\Tests\Framework\Fixture;

/**
 * Records four changes, oldest first, so the login "What's New" panel has something to render -
 * the installation itself never records any. The provider keeps the three most recent, so the
 * first is deliberately left out, and the three that show cover each link outcome: external
 * (kept), internal (stripped), none.
 */
class WhatsNewChanges extends Fixture
{
    public $dateTime = '2013-01-23 01:23:45';
    public $idSite = 1;

    public function setUp(): void
    {
        $this->setUpWebsite();
        self::recordPanelChanges();
        $this->setUpTermsAndPrivacy();
    }

    public function tearDown(): void
    {
        // empty
    }

    private function setUpWebsite(): void
    {
        if (!self::siteCreated($this->idSite)) {
            $idSite = self::createWebsite($this->dateTime);
            $this->assertSame($this->idSite, $idSite);
        }
    }

    /**
     * Static so other fixtures can reuse these entries. Inert until a spec sets
     * `testEnvironment.loadChanges`, which is what swaps FakeChangesModel back for the real one.
     */
    public static function recordPanelChanges(): void
    {
        // Not resolved through the container: FakeChangesModel::addChange() is a no-op.
        $model = new ChangesModel();

        // Oldest first, so this one falls outside the three most recent.
        $model->addChange('CoreHome', [
            'version'     => '5.0.0',
            'title'       => 'This entry is left out of the panel',
            'description' => 'Only the three most recent entries are shown on the login page.',
        ]);

        $model->addChange('CoreHome', [
            'version'     => '5.0.1',
            'title'       => 'An entry without a call to action',
            'description' => 'Entries stay visible even when they carry no link at all.',
        ]);

        $model->addChange('CoreHome', [
            'version'     => '5.0.2',
            'title'       => 'An entry linking back to this instance',
            'description' => 'The call to action is stripped, the entry itself is kept.',
            'link_name'   => 'Open the dashboard',
            'link'        => 'index.php?module=CoreHome&action=index',
        ]);

        $model->addChange('CoreHome', [
            'version'     => '5.0.3',
            'title'       => 'An entry with an external call to action',
            'description' => 'Only genuinely external links keep their call to action.',
            'link_name'   => 'Read the announcement',
            'link'        => 'https://matomo.org/changelog/',
        ]);
    }

    /**
     * The footer links only render for anonymous visitors once these are set, so the panel
     * baseline covers them too.
     */
    private function setUpTermsAndPrivacy(): void
    {
        $settings = new SystemSettings();
        $settings->privacyPolicyUrl->setValue('matomo.org');
        $settings->termsAndConditionUrl->setValue('matomo.org');
        $settings->save();
    }
}
