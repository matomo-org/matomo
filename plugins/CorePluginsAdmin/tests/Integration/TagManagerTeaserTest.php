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

    public function testIsEnabledGloballyByDefault()
    {
        $this->assertTrue($this->teaser->isEnabledGlobally());
    }

    public function testDisableGlobally()
    {
        $this->teaser->disableGlobally();
        $this->assertFalse($this->teaser->isEnabledGlobally());
    }

    public function testReset()
    {
        $this->teaser->disableGlobally();
        $this->assertFalse($this->teaser->isEnabledGlobally());
        $this->teaser->reset();
        $this->assertTrue($this->teaser->isEnabledGlobally());
    }

    public function testDisableGloballyRemovesUserSettings()
    {
        $this->teaser->disableForUser();
        $this->assertFalse($this->teaser->isEnabledForUser());

        $this->teaser->disableGlobally();

        $this->assertFalse($this->teaser->isEnabledGlobally());
        // should reset user enable flags cause disabled globally anyway
        $this->assertTrue($this->teaser->isEnabledForUser());
    }

    public function testIsEnabledForCurrentUserByDefault()
    {
        $this->assertTrue($this->teaser->isEnabledForUser());
    }

    public function testDisableForUser()
    {
        $this->teaser->disableForUser();
        $this->assertFalse($this->teaser->isEnabledForUser());

        // still enabled globally
        $this->assertTrue($this->teaser->isEnabledGlobally());

        // still enabled for other user
        $otherUser = $this->makeTeaser('foobar123');
        $this->assertTrue($otherUser->isEnabledForUser());
    }

    public function testShouldShowTeaser()
    {
        $this->assertTrue($this->teaser->shouldShowTeaser());
        $this->assertTrue($this->teaser->isEnabledGlobally());
    }

    public function testShouldShowTeaserShouldNotBeShownWhenTagManagerEnabled()
    {
        Plugin\Manager::getInstance()->activatePlugin('TagManager');
        $this->assertFalse($this->teaser->shouldShowTeaser());
        // should have been disabled automatically
        $this->assertFalse($this->teaser->isEnabledGlobally());
    }
}
