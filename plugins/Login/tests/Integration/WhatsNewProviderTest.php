<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Login\tests\Integration;

use Piwik\Changes\Model as ChangesModel;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Plugins\Login\WhatsNewProvider;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Login
 * @group WhatsNewProvider
 * @group Plugins
 */
class WhatsNewProviderTest extends IntegrationTestCase
{
    private const INSTANCE_HOST = 'login-instance.example.com';

    /**
     * @var string|null
     */
    private $originalHttpHost;

    public function setUp(): void
    {
        parent::setUp();

        // Pin the current host so "same instance" link detection is deterministic.
        $this->originalHttpHost = $_SERVER['HTTP_HOST'] ?? null;
        $_SERVER['HTTP_HOST'] = self::INSTANCE_HOST;
    }

    public function tearDown(): void
    {
        if ($this->originalHttpHost === null) {
            unset($_SERVER['HTTP_HOST']);
        } else {
            $_SERVER['HTTP_HOST'] = $this->originalHttpHost;
        }

        parent::tearDown();
    }

    public function testReturnsOnlyTheThreeMostRecentEntriesPreservingModelOrder(): void
    {
        $provider = $this->makeProvider([
            $this->change(['title' => 'First']),
            $this->change(['title' => 'Second']),
            $this->change(['title' => 'Third']),
            $this->change(['title' => 'Fourth']),
        ]);

        $changes = $provider->getChanges();

        $this->assertCount(3, $changes);
        $this->assertSame('First', $changes[0]['title']);
        $this->assertSame('Second', $changes[1]['title']);
        $this->assertSame('Third', $changes[2]['title']);
    }

    public function testCachesResultForTheRequest(): void
    {
        $model = $this->createMock(ChangesModel::class);
        $model->expects($this->once())->method('getChangeItems')->willReturn([$this->change([])]);

        $provider = new WhatsNewProvider($model);

        $provider->getChanges();
        $provider->getChanges();
    }

    public function testKeepsEntryWithoutAnyLink(): void
    {
        $provider = $this->makeProvider([
            $this->change(['title' => 'No link', 'link' => null, 'link_name' => null]),
        ]);

        $changes = $provider->getChanges();

        $this->assertCount(1, $changes);
        $this->assertSame('No link', $changes[0]['title']);
        $this->assertSame('', $changes[0]['link']);
        $this->assertSame('', $changes[0]['link_name']);
    }

    public function testRetainsValidExternalHttpAndHttpsLinks(): void
    {
        $provider = $this->makeProvider([
            $this->change(['link' => 'https://matomo.org/blog', 'link_name' => 'Read more']),
            $this->change(['link' => 'http://example.org/news', 'link_name' => 'News']),
        ]);

        $changes = $provider->getChanges();

        $this->assertSame('https://matomo.org/blog', $changes[0]['link']);
        $this->assertSame('Read more', $changes[0]['link_name']);
        $this->assertSame('http://example.org/news', $changes[1]['link']);
    }

    /**
     * @dataProvider getUnsafeOrInternalLinks
     */
    public function testStripsUnsafeOrInternalLinkButKeepsEntry(?string $link): void
    {
        $provider = $this->makeProvider([
            $this->change(['title' => 'Kept', 'description' => 'Body', 'link' => $link, 'link_name' => 'Go here']),
        ]);

        $changes = $provider->getChanges();

        $this->assertCount(1, $changes, 'the entry itself must always stay visible');
        $this->assertSame('Kept', $changes[0]['title']);
        $this->assertSame('Body', $changes[0]['description']);
        $this->assertSame('', $changes[0]['link'], 'the CTA link must be stripped');
        $this->assertSame('', $changes[0]['link_name']);
    }

    public function getUnsafeOrInternalLinks(): array
    {
        return [
            'internal index.php'          => ['index.php?module=CoreHome&action=index'],
            'root-relative index.php'     => ['/index.php?module=CoreHome'],
            'other relative url'          => ['some/relative/path'],
            'same-instance absolute url'  => ['https://' . self::INSTANCE_HOST . '/index.php?module=CoreHome'],
            'protocol-relative url'       => ['//evil.example.org/path'],
            'invalid url'                 => ['http://'],
            'javascript scheme'           => ['javascript:alert(1)'],
            'data scheme'                 => ['data:text/html,<script>alert(1)</script>'],
            'file scheme'                 => ['file:///etc/passwd'],
            'empty link'                  => [''],
            'null link'                   => [null],
        ];
    }

    public function testStripsCtaWhenLinkNameIsMissingEvenIfUrlIsValid(): void
    {
        $provider = $this->makeProvider([
            $this->change(['link' => 'https://matomo.org', 'link_name' => null]),
        ]);

        $changes = $provider->getChanges();

        $this->assertCount(1, $changes);
        $this->assertSame('', $changes[0]['link']);
    }

    public function testAddsPluginPrefixForNonCoreBundledPluginOnly(): void
    {
        $provider = $this->makeProvider([
            $this->change(['title' => 'Marketplace', 'plugin_name' => 'SomeMarketplacePlugin']),
            $this->change(['title' => 'Core', 'plugin_name' => 'CoreHome']),
        ]);

        $changes = $provider->getChanges();

        $this->assertTrue($changes[0]['showPluginPrefix']);
        $this->assertSame('SomeMarketplacePlugin', $changes[0]['plugin_name']);
        $this->assertFalse($changes[1]['showPluginPrefix']);
    }

    public function testReturnsEmptyWhenModelHasNoChanges(): void
    {
        $provider = $this->makeProvider([]);

        $this->assertSame([], $provider->getChanges());
    }

    public function testReturnsEmptyWhenModelThrowsSoLoginStillRenders(): void
    {
        $model = $this->createMock(ChangesModel::class);
        $model->method('getChangeItems')->willThrowException(new \Exception('db down'));

        $provider = new WhatsNewProvider($model);

        $this->assertSame([], $provider->getChanges());
    }

    public function testReturnsEmptyWhenWhiteLabelIsActive(): void
    {
        $manager = PluginManager::getInstance();

        $reflection = new \ReflectionProperty(PluginManager::class, 'pluginsToLoad');
        $reflection->setAccessible(true);
        $original = $reflection->getValue($manager);

        try {
            $reflection->setValue($manager, array_merge($original, ['WhiteLabel']));
            $this->assertTrue($manager->isPluginActivated('WhiteLabel'));

            $provider = $this->makeProvider([$this->change(['title' => 'Should be hidden'])]);

            $this->assertSame([], $provider->getChanges());
        } finally {
            $reflection->setValue($manager, $original);
        }
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    private function makeProvider(array $items): WhatsNewProvider
    {
        $model = $this->createMock(ChangesModel::class);
        $model->method('getChangeItems')->willReturn($items);

        return new WhatsNewProvider($model);
    }

    /**
     * @param array<string, mixed> $overrides
     * @return array<string, mixed>
     */
    private function change(array $overrides): array
    {
        return array_merge([
            'idchange'    => 1,
            'plugin_name' => 'CoreHome',
            'version'     => '5.0.0',
            'title'       => 'A change',
            'description' => 'A description',
            'link_name'   => null,
            'link'        => null,
        ], $overrides);
    }
}
