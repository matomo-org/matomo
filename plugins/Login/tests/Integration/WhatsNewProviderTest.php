<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Login\tests\Integration;

use Piwik\Access;
use Piwik\Cache;
use Piwik\Changes\Model as ChangesModel;
use Piwik\Container\StaticContainer;
use Piwik\Log\LoggerInterface;
use Piwik\Piwik;
use Piwik\Plugin\Manager as PluginManager;
use Piwik\Plugins\Login\WhatsNewProvider;
use Piwik\Tests\Framework\Mock\FakeAccess;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Login
 * @group WhatsNewProvider
 * @group Plugins
 */
class WhatsNewProviderTest extends IntegrationTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        // The provider caches the processed entries in the lazy cache under a fixed id, so an entry
        // left behind by another test would be picked up here.
        Cache::flushAll();
    }

    public function tearDown(): void
    {
        Cache::flushAll();

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

        $provider = $this->provider($model);

        $provider->getChanges();
        $provider->getChanges();
    }

    public function testCachesProcessedEntriesAcrossRequestsForLoggedOutVisitors(): void
    {
        $model = $this->createMock(ChangesModel::class);
        $model->expects($this->once())->method('getChangeItems')->willReturn([$this->change([])]);

        // Two separate providers stand in for two separate requests; only the first may read the
        // changes table.
        $this->assertCount(1, $this->getChangesAsLoggedOutVisitor($this->provider($model)));
        $this->assertCount(1, $this->getChangesAsLoggedOutVisitor($this->provider($model)));
    }

    /**
     * The panel also shows on the confirm password and 2FA screens, and a plugin can filter the list
     * per user through `Changes.filterChanges`, so a logged in request must never fill the cache a
     * logged out visitor reads.
     */
    public function testALoggedInRequestDoesNotFillTheCacheALoggedOutVisitorReads(): void
    {
        $model = $this->createMock(ChangesModel::class);
        // Both build their own list: the logged in one because it may not save, the logged out one
        // because it must find nothing saved.
        $model->expects($this->exactly(2))->method('getChangeItems')->willReturn([$this->change([])]);

        $this->assertFalse(Piwik::isUserIsAnonymous(), 'this only means anything as a logged in request');
        $this->assertCount(1, $this->provider($model)->getChanges());

        $this->assertCount(1, $this->getChangesAsLoggedOutVisitor($this->provider($model)));
    }

    /**
     * A logged in request saves nothing at all, so two different users can never serve each other a
     * list that was filtered for one of them.
     */
    public function testNeverFillsTheCacheForALoggedInRequest(): void
    {
        $model = $this->createMock(ChangesModel::class);
        $model->expects($this->exactly(2))->method('getChangeItems')->willReturn([$this->change([])]);

        $this->assertFalse(Piwik::isUserIsAnonymous(), 'this only means anything as a logged in request');

        $this->assertCount(1, $this->provider($model)->getChanges());
        $this->assertCount(1, $this->provider($model)->getChanges());
    }

    /**
     * Reading the cache is fine for anyone, so the confirm password and 2FA screens stay as cheap as
     * the login page.
     */
    public function testALoggedInRequestReusesWhatALoggedOutVisitorCached(): void
    {
        $model = $this->createMock(ChangesModel::class);
        $model->expects($this->once())->method('getChangeItems')->willReturn([$this->change([])]);

        $this->assertCount(1, $this->getChangesAsLoggedOutVisitor($this->provider($model)));
        $this->assertCount(1, $this->provider($model)->getChanges());
    }

    /**
     * End to end against the real model and the real changes table, mirroring what the
     * WhatsNewChanges UI fixture records - the mocked tests above can't catch a break between the
     * provider, the model, the cache and the database.
     */
    public function testReturnsTheThreeMostRecentEntriesFromTheRealChangesTable(): void
    {
        $model = new ChangesModel();
        $model->addChange('CoreHome', ['version' => '5.0.0', 'title' => 'Left out', 'description' => 'd']);
        $model->addChange('CoreHome', ['version' => '5.0.1', 'title' => 'No link', 'description' => 'd']);
        $model->addChange('CoreHome', [
            'version'     => '5.0.2',
            'title'       => 'Internal link',
            'description' => 'd',
            'link_name'   => 'Dashboard',
            'link'        => 'index.php?module=CoreHome&action=index',
        ]);
        $model->addChange('CoreHome', [
            'version'     => '5.0.3',
            'title'       => 'External link',
            'description' => 'd',
            'link_name'   => 'Announcement',
            'link'        => 'https://matomo.org/changelog/',
        ]);

        $changes = $this->provider(new ChangesModel())->getChanges();

        $this->assertSame(
            ['External link', 'Internal link', 'No link'],
            array_column($changes, 'title'),
            'the three most recent entries, newest first'
        );
        $this->assertSame('https://matomo.org/changelog/', $changes[0]['link']);
        $this->assertSame('', $changes[1]['link'], 'the internal link must be stripped');
        $this->assertSame('', $changes[2]['link']);
    }

    public function testDoesNotCacheAnEmptyResult(): void
    {
        $model = $this->createMock(ChangesModel::class);
        // An empty result must not be pinned: on a fresh install the login page can be reached
        // before any change is recorded, and the panel has to appear as soon as one exists. Checked
        // logged out, the only request that saves anything.
        $model->expects($this->exactly(2))->method('getChangeItems')->willReturn([]);

        $this->assertSame([], $this->getChangesAsLoggedOutVisitor($this->provider($model)));
        $this->assertSame([], $this->getChangesAsLoggedOutVisitor($this->provider($model)));
    }

    /**
     * One link per case on purpose: seeding several at once means MAX_ENTRIES silently drops all but
     * the first three, so any case past the third would never actually be exercised.
     *
     * @dataProvider getAllowedHostLinks
     */
    public function testKeepsLinksOnTheAllowedHostAndItsSubdomains(string $link): void
    {
        $provider = $this->makeProvider([
            $this->change(['link' => $link, 'link_name' => 'Read more']),
        ]);

        $changes = $provider->getChanges();

        $this->assertCount(1, $changes);
        $this->assertSame($link, $changes[0]['link'], 'the call to action should have been kept');
        $this->assertSame('Read more', $changes[0]['link_name']);
    }

    public function getAllowedHostLinks(): array
    {
        return [
            'apex host'          => ['https://matomo.org/changelog/'],
            'subdomain'          => ['https://plugins.matomo.org/SomePlugin'],
            'www subdomain'      => ['https://www.matomo.org'],
            'different casing'   => ['https://MATOMO.org/faq/'],
            'trailing root dot'  => ['https://matomo.org./faq/'],
            'explicit port'      => ['https://matomo.org:8443/faq/'],
            'plain http'         => ['http://matomo.org/news'],
        ];
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
            'absolute url on this instance' => ['https://analytics.example.com/index.php?module=CoreHome'],
            'any other external host'     => ['https://example.org/news'],
            // Look-alikes: neither is a subdomain of an allowed host.
            'allowed host as a suffix'    => ['https://evil-matomo.org/phishing'],
            'allowed host as a prefix'    => ['https://matomo.org.example.com/phishing'],
            'protocol-relative url'       => ['//evil.example.org/path'],
            'invalid url'                 => ['http://'],
            'javascript scheme'           => ['javascript:alert(1)'],
            'data scheme'                 => ['data:text/html,<script>alert(1)</script>'],
            'file scheme'                 => ['file:///etc/passwd'],
            'empty link'                  => [''],
            'null link'                   => [null],
        ];
    }

    public function testStoresTheTrimmedLinkSoTheHrefStaysUsable(): void
    {
        $provider = $this->makeProvider([
            $this->change(['link' => '  https://matomo.org/changelog/  ', 'link_name' => '  Read more  ']),
        ]);

        $changes = $provider->getChanges();

        // The check runs on the trimmed URL, so storing the raw one would leave `|safelink` to reject
        // it and the template to render an empty href.
        $this->assertSame('https://matomo.org/changelog/', $changes[0]['link']);
        $this->assertSame('Read more', $changes[0]['link_name']);
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

        $provider = $this->provider($model);

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

        return $this->provider($model);
    }

    private function provider(ChangesModel $model): WhatsNewProvider
    {
        return new WhatsNewProvider($model, StaticContainer::get(LoggerInterface::class));
    }

    /**
     * Runs the provider as a logged out visitor, which is what a hit on the login page looks like -
     * IntegrationTestCase grants super user access in setUp(). Undone in a finally, so nothing leaks
     * into the next test.
     *
     * @return array<int, array<string, mixed>>
     */
    private function getChangesAsLoggedOutVisitor(WhatsNewProvider $provider): array
    {
        $realAccess = Access::getInstance();
        $anonymous = new FakeAccess($superUser = false, $idSitesAdmin = [], $idSitesView = [], $identity = 'anonymous');
        StaticContainer::getContainer()->set('Piwik\Access', $anonymous);

        try {
            $this->assertTrue(Piwik::isUserIsAnonymous(), 'the swap should have made this logged out');

            return $provider->getChanges();
        } finally {
            StaticContainer::getContainer()->set('Piwik\Access', $realAccess);
        }
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
