<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CorePluginsAdmin\tests\Integration;

use Piwik\Plugin;
use Piwik\Plugins\CorePluginsAdmin\Model\TagManagerTeaser;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group CorePluginsAdmin
 * @group ApiTest
 * @group Api
 * @group Plugins
 */
class TagManagerTeaserTest extends IntegrationTestCase
{
    /**
     * @var TagManagerTeaser
     */
    private $teaser;

    public function setUp(): void
    {
        parent::setUp();

        Plugin\Manager::getInstance()->deactivatePlugin('TagManager');

        $this->teaser = $this->makeTeaser('mylogin');
    }

    private function makeTeaser($login)
    {
        return new TagManagerTeaser($login);
    }

    public function test_isEnabledGloballyByDefault()
    {
        $this->assertTrue($this->teaser->isEnabledGlobally());
    }

    public function test_disableGlobally()
    {
        $this->teaser->disableGlobally();
        $this->assertFalse($this->teaser->isEnabledGlobally());
    }

    public function test_reset()
    {
        $this->teaser->disableGlobally();
        $this->assertFalse($this->teaser->isEnabledGlobally());
        $this->teaser->reset();
        $this->assertTrue($this->teaser->isEnabledGlobally());
    }

    public function test_disableGlobally_removesUserSettings()
    {
        $this->teaser->disableForUser();
        $this->assertFalse($this->teaser->isEnabledForUser());

        $this->teaser->disableGlobally();

        $this->assertFalse($this->teaser->isEnabledGlobally());
        // should reset user enable flags cause disabled globally anyway
        $this->assertTrue($this->teaser->isEnabledForUser());
    }

    public function test_isEnabledForCurrentUserByDefault()
    {
        $this->assertTrue($this->teaser->isEnabledForUser());
    }

    public function test_disableForUser()
    {
        $this->teaser->disableForUser();
        $this->assertFalse($this->teaser->isEnabledForUser());

        // still enabled globally
        $this->assertTrue($this->teaser->isEnabledGlobally());

        // still enabled for other user
        $otherUser = $this->makeTeaser('foobar123');
        $this->assertTrue($otherUser->isEnabledForUser());
    }

    public function test_shouldShowTeaser()
    {
        $this->assertTrue($this->teaser->shouldShowTeaser());
        $this->assertTrue($this->teaser->isEnabledGlobally());
    }

    public function test_shouldShowTeaser_shouldNotBeShownWhenTagManagerEnabled()
    {
        Plugin\Manager::getInstance()->activatePlugin('TagManager');
        $this->assertFalse($this->teaser->shouldShowTeaser());
        // should have been disabled automatically
        $this->assertFalse($this->teaser->isEnabledGlobally());
    }
}
