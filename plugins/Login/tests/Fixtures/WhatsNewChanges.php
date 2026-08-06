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
 * Records a known set of changes so the login "What's New" panel has something to render.
 *
 * The installation itself never records a change, so without this the panel is empty and every
 * assertion about it is vacuous.
 *
 * Four entries are added, oldest first. The model returns them by descending id and the provider
 * keeps only the three most recent, so the entry added first is deliberately left out - that is what
 * makes the "at most three entries" assertion meaningful rather than trivially true. The three that
 * do show cover each way a call to action can end up: an external link that renders, an internal
 * link whose call to action must be stripped, and an entry carrying no link at all.
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
     * Static so any other fixture can give its spec a What's New baseline without duplicating these
     * entries - a spec only has to set `testEnvironment.loadChanges` for the test that wants the
     * panel. Recording them is inert until it does, since the test environment otherwise swaps in
     * FakeChangesModel and no change is ever read back.
     */
    public static function recordPanelChanges(): void
    {
        // Intentionally not resolved through the container: the test environment swaps
        // Piwik\Changes\Model for FakeChangesModel, whose addChange() is a no-op. The spec sets
        // testEnvironment.loadChanges so the web request gets the real model back.
        $model = new ChangesModel();

        // Oldest first: this one falls outside the three most recent and must not be rendered.
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
     * The imprint / privacy / terms links only render for anonymous visitors once these are set, so
     * the panel baseline also covers the footer links under the sign in form.
     */
    private function setUpTermsAndPrivacy(): void
    {
        $settings = new SystemSettings();
        $settings->privacyPolicyUrl->setValue('matomo.org');
        $settings->termsAndConditionUrl->setValue('matomo.org');
        $settings->save();
    }
}
