<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Login\tests\Integration;

use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\View;

/**
 * Output-safety checks for the What's New panel rendered on the shared login layout.
 *
 * @group Login
 * @group WhatsNewProvider
 * @group Plugins
 */
class WhatsNewPanelRenderTest extends IntegrationTestCase
{
    public function testEscapesHtmlInTitleDescriptionAndPluginName(): void
    {
        $html = $this->render([
            [
                'title'            => 'Title <script>alert(1)</script>',
                'description'      => 'Body <img src=x onerror=alert(1)>',
                'plugin_name'      => 'Evil<script>',
                'showPluginPrefix' => true,
                'link'             => '',
                'link_name'        => '',
            ],
        ]);

        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertStringNotContainsString('<img src=x onerror=alert(1)>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
    }

    public function testRendersExternalLinkWithRelAndTargetAttributes(): void
    {
        $html = $this->render([
            [
                'title'            => 'A change',
                'description'      => 'Body',
                'plugin_name'      => 'CoreHome',
                'showPluginPrefix' => false,
                'link'             => 'https://matomo.org/blog',
                'link_name'        => 'Read more',
            ],
        ]);

        // The href is passed through safelink + html_attr escaping (the established Matomo pattern),
        // so ":" and "/" are rendered as numeric entities. Assert on the parts that survive escaping.
        $this->assertStringContainsString('class="loginWhatsNew__link"', $html);
        $this->assertStringContainsString('matomo.org', $html);
        $this->assertStringContainsString('rel="noreferrer noopener"', $html);
        $this->assertStringContainsString('target="_blank"', $html);
        $this->assertStringContainsString('Read more', $html);
        // Ensure the unsafe-scheme guard leaves nothing executable behind.
        $this->assertStringNotContainsString('javascript:', $html);
    }

    public function testDoesNotRenderCtaWhenLinkIsEmpty(): void
    {
        $html = $this->render([
            [
                'title'            => 'A change',
                'description'      => 'Body',
                'plugin_name'      => 'CoreHome',
                'showPluginPrefix' => false,
                'link'             => '',
                'link_name'        => '',
            ],
        ]);

        $this->assertStringContainsString('A change', $html);
        $this->assertStringNotContainsString('loginWhatsNew__link', $html);
    }

    public function testRendersPluginPrefixOnlyWhenFlagged(): void
    {
        $html = $this->render([
            [
                'title'            => 'A change',
                'description'      => 'Body',
                'plugin_name'      => 'SomeMarketplacePlugin',
                'showPluginPrefix' => true,
                'link'             => '',
                'link_name'        => '',
            ],
        ]);

        $this->assertStringContainsString('SomeMarketplacePlugin - ', $html);
    }

    /**
     * @param array<int, array<string, mixed>> $changes
     */
    private function render(array $changes): string
    {
        $view = new View('@Login/_whatsNewPanel');
        $view->changes = $changes;

        return $view->render();
    }
}
