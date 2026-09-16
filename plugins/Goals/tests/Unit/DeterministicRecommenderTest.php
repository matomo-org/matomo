<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Goals\tests\Unit;

use Piwik\Piwik;
use Piwik\Plugins\Goals\Recommendations\DeterministicRecommender;
use PHPUnit\Framework\TestCase;

/**
 * @group Goals
 * @group GoalRecommendations
 */
class DeterministicRecommenderTest extends TestCase
{
    /**
     * @var DeterministicRecommender
     */
    private $recommender;

    public function setUp(): void
    {
        parent::setUp();
        $this->recommender = new DeterministicRecommender();
    }

    public function testRecommendReturnsOneGoalPerCategoryOrderedByBusinessValue(): void
    {
        $goals = $this->recommender->recommend($this->analysis([
            $this->link('/pricing', 'Pricing'),
            $this->link('/contact', 'Contact us'),
            $this->link('/signup', 'Sign up'),
            $this->link('/newsletter', 'Newsletter'),
            $this->link('/demo', 'Book a demo'),
            $this->link('/contact-sales', 'Contact sales'),
        ]));

        // the second contact page (/contact-sales) follows the category winners as runner-up
        $this->assertSame(['/signup', '/demo', '/contact', '/newsletter', '/pricing', '/contact-sales'], array_column($goals, 'pattern'));
        $this->assertSame(['signup', 'demo', 'contact', 'newsletter', 'pricing', 'contact'], array_column($goals, 'category'));
        $this->assertSame([1, 2, 3, 4, 5, 6], array_column($goals, 'priority'));

        foreach ($goals as $goal) {
            $this->assertSame('url', $goal['matchAttribute']);
            $this->assertSame('contains', $goal['patternType']);
            $this->assertFalse($goal['allowMultipleConversionsPerVisit']);
            $this->assertFalse($goal['needsSetup']);
            $this->assertNotSame('', $goal['name']);
            $this->assertNotEmpty($goal['evidence']);
        }
    }

    public function testRecommendReturnsUpToTenGoalsWithRunnersUpAfterTheCategoryWinners(): void
    {
        $goals = $this->recommender->recommend($this->analysis([
            $this->link('/planen-buchen/unterkuenfte', 'Unterkünfte buchen'),
            $this->link('/planen-buchen/bergbahntickets', 'Bergbahntickets'),
            $this->link('/kontakt', 'Kontakt'),
            $this->link('/newsletter', 'Newsletter'),
        ]));

        $patterns = array_column($goals, 'pattern');
        $this->assertCount(4, $goals);
        // one booking goal among the winners, the other booking page follows as runner-up
        $this->assertSame(['/kontakt', '/newsletter'], array_slice($patterns, 1, 2));
        $this->assertSame('booking', $goals[3]['category']);
        $this->assertLessThanOrEqual(DeterministicRecommender::MAX_RECOMMENDATIONS, count($goals));
    }

    public function testRecommendMatchesWholeTokensOnly(): void
    {
        $goals = $this->recommender->recommend($this->analysis([
            $this->link('/printed-books/design-book-5', 'Design book'),
            $this->link('/ebook', 'Free ebook'),
            $this->link('/floorplans', 'Floor plans'),
            $this->link('/blog/how-to-contact-support', 'How to contact support'),
            $this->link('/terms-and-conditions', 'Terms apply'),
            $this->link('/2026/07/people-dont-want-more-ai', 'Read more'),
            $this->link('/merci-pour-nos-enfants', 'Campagne'),
        ]));

        $this->assertSame([], $goals);
    }

    public function testRecommendAllowsActivationPagesInsideDocumentation(): void
    {
        $goals = $this->recommender->recommend($this->analysis([
            $this->link('/docs/getting-started/installation', 'Get started'),
            $this->link('/docs/guide/concepts/rendering', 'Rendering'),
        ]));

        $this->assertSame(['/docs/getting-started/installation'], array_column($goals, 'pattern'));
        $this->assertSame('activation', $goals[0]['category']);
    }

    public function testRecommendPrefersTheShallowProminentCategoryPageOverDeepMentions(): void
    {
        $goals = $this->recommender->recommend($this->analysis([
            $this->link('/spenden/service/haeufige-fragen-kontakt', 'Häufige Fragen & Kontakt', ['heroCount' => 1]),
            $this->link('/kontakt', 'Kontakt'),
        ]));

        $this->assertSame('/kontakt', $goals[0]['pattern']);
        $this->assertSame(Piwik::translate('Goals_RecommendationContactName'), $goals[0]['name']);
    }

    public function testRecommendNamesPagesThatOnlyMentionTheTokenAfterTheirLabelAndIgnoresMarketingHubs(): void
    {
        $goals = $this->recommender->recommend($this->analysis([
            $this->link('/plumbing/flat-rate-pricing', 'Upfront pricing'),
            $this->link('/why-us-/convenient-appointment-times', 'Appointments'),
            $this->link('/about/contact-us', 'Contact us'),
        ]));

        $this->assertSame(['/about/contact-us', '/plumbing/flat-rate-pricing'], array_column($goals, 'pattern'));
        $this->assertSame(Piwik::translate('Goals_RecommendationKeyPageName', ['Upfront pricing']), $goals[1]['name']);
    }

    public function testRecommendClassifiesFormsByFieldTypesWithoutVocabulary(): void
    {
        $goals = $this->recommender->recommend($this->analysis([], ['pagesCrawled' => 10, 'forms' => [
            $this->form(['email'], 'Absenden', ['/', '/a', '/b', '/c']),
            $this->form(['text', 'text', 'tel', 'email', 'textarea'], 'Senden', ['/kontakt']),
            $this->form(['text', 'email', 'textarea'], 'Senden', ['/anfrage-stellen']),
            $this->form(['text', 'email', 'password', 'password', 'checkbox'], 'Weiter', ['/konto-erstellen']),
            $this->form(['email', 'password'], 'Log in', ['/inloggen']),
            $this->form(['date', 'date', 'number'], 'Suchen', ['/zimmer']),
        ]]));

        $byCategory = array_column($goals, 'pattern', 'category');
        $newsletter = $goals[array_search('newsletter', array_column($goals, 'category'), true)];
        // site wide email form: newsletter event goal that needs setup
        $this->assertSame('newsletter-signup', $byCategory['newsletter']);
        $this->assertSame('event_name', $newsletter['matchAttribute']);
        $this->assertTrue($newsletter['needsSetup']);
        // forms with their own page become URL goals on that page
        $this->assertSame('/kontakt', $byCategory['contact']);
        // a message form on an enquiry page is a quote request
        $this->assertSame('/anfrage-stellen', $byCategory['demo']);
        $this->assertSame('/konto-erstellen', $byCategory['signup']);
        $this->assertSame('/zimmer', $byCategory['booking']);
        $this->assertArrayNotHasKey('/inloggen', array_flip($byCategory));
    }

    public function testRecommendPutsTheFormOnItsCategoryPageWhenLinkedFromSeveralPages(): void
    {
        $goals = $this->recommender->recommend($this->analysis([], ['pagesCrawled' => 20, 'forms' => [
            $this->form(['text', 'email', 'textarea'], 'Send', ['/contact', '/about']),
        ]]));

        $this->assertSame([['contact', 'url', '/contact']], array_map(function (array $goal): array {
            return [$goal['category'], $goal['matchAttribute'], $goal['pattern']];
        }, $goals));
    }

    /**
     * @dataProvider getPlatforms
     */
    public function testRecommendAddsPlatformUrlsTheCrawlCannotReach(string $platform, array $expectedPatterns): void
    {
        $goals = $this->recommender->recommend($this->analysis([], ['platform' => $platform]));

        $this->assertSame($expectedPatterns, array_column($goals, 'pattern'));
        $this->assertSame('platform', $goals[0]['source']);
        $this->assertSame(Piwik::translate('Goals_RecommendationPurchaseName'), $goals[0]['name']);
    }

    public function getPlatforms(): array
    {
        return [
            'shopify' => ['shopify', ['/thank_you', '/checkouts/', '/cart']],
            'woocommerce' => ['woocommerce', ['/checkout/order-received/', '/checkout', '/cart']],
            'shopware' => ['shopware', ['/checkout/finish', '/checkout/confirm', '/checkout/cart']],
            'sfcc' => ['sfcc', ['Order-Confirm', 'Checkout-Begin', 'Cart-Show']],
        ];
    }

    public function testRecommendOffersAddToCartEventWhenProductsExistButNoCartOrPlatform(): void
    {
        $pages = [];
        for ($i = 1; $i <= 5; $i++) {
            $pages[] = ['path' => '/gb/en/product/item-' . $i, 'title' => '', 'heading' => '', 'types' => ['Product'], 'hasAddToCart' => true];
        }
        $goals = $this->recommender->recommend($this->analysis([], ['pages' => $pages, 'pagesCrawled' => 5]));

        $this->assertSame([['cart', 'event_name', 'add-to-cart', true], ['product', 'url', '/product/', false]], array_map(function (array $goal): array {
            return [$goal['category'], $goal['matchAttribute'], $goal['pattern'], $goal['needsSetup']];
        }, $goals));
    }

    public function testRecommendBoostsCategoriesThatFitTheOrganisationType(): void
    {
        $links = [$this->link('/spenden', 'Spenden'), $this->link('/register', 'Register', ['buttonLikeCount' => 3])];
        $plain = $this->recommender->recommend($this->analysis($links));
        $ngo = $this->recommender->recommend($this->analysis($links, ['pages' => [
            ['path' => '/', 'title' => '', 'heading' => '', 'types' => ['NGO'], 'hasAddToCart' => false],
        ]]));

        $this->assertSame('/register', $plain[0]['pattern']);
        $this->assertSame('/spenden', $ngo[0]['pattern']);
    }

    public function testRecommendAggregatesDownloadsAndSkipsForeignAndLegalFiles(): void
    {
        $goals = $this->recommender->recommend($this->analysis([], ['downloads' => [
            $this->download('https://example.com/files/guide.pdf', 'Guide'),
            $this->download('https://example.com/files/report.pdf', 'Report'),
            $this->download('https://example.com/files/widerrufsformular.pdf', 'Widerruf'),
            $this->download('https://other.example/paper.pdf', 'External paper'),
        ]]));

        $this->assertSame([['file', '.pdf', true]], array_map(function (array $goal): array {
            return [$goal['matchAttribute'], $goal['pattern'], $goal['allowMultipleConversionsPerVisit']];
        }, $goals));
        $this->assertSame(Piwik::translate('Goals_RecommendationDownloadTypeName', ['PDF']), $goals[0]['name']);
    }

    public function testRecommendClassifiesOutlinksByPlatformSiblingRedirectorBrandAndLabel(): void
    {
        $goals = $this->recommender->recommend($this->analysis([], ['pagesCrawled' => 10, 'externalLinks' => [
            $this->external('facebook.com', 'Facebook'),
            $this->external('opencollective.com', 'Become a sponsor', 'https://opencollective.com/nuxtjs'),
            $this->external('go.example.com', '', 'https://go.example.com/discord'),
            $this->external('support.example.com', 'Support'),
            $this->external('example.giftpro.co.uk', 'Gift vouchers'),
            $this->external('wordpress.org', 'Get the plugin', 'https://wordpress.org/plugins/example/'),
            $this->external('partner.example.org', 'Faire un don', 'https://partner.example.org/x', 3),
            $this->external('github.com', 'Star us'),
        ]]));

        $byPattern = array_column($goals, 'category', 'pattern');
        ksort($byPattern);
        $this->assertSame([
            'example.giftpro.co.uk' => 'partner',
            'go.example.com/discord' => 'community',
            'opencollective.com' => 'sponsor',
            'partner.example.org' => 'donate',
            'wordpress.org' => 'partner',
        ], $byPattern);
        $this->assertArrayNotHasKey('facebook.com', $byPattern);
        $this->assertArrayNotHasKey('support.example.com', $byPattern);
        $this->assertArrayNotHasKey('github.com', $byPattern);
        $this->assertSame(Piwik::translate('Goals_RecommendationSponsorName', ['opencollective.com']), $goals[array_search('opencollective.com', array_column($goals, 'pattern'), true)]['name']);
    }

    public function testRecommendNeverReturnsContactLinkOrSocialGoals(): void
    {
        $goals = $this->recommender->recommend($this->analysis([], [
            'contactLinks' => [['href' => 'mailto:sales@example.com', 'labels' => ['Email'], 'sourcePages' => [], 'count' => 3]],
            'externalLinks' => [$this->external('instagram.com', 'Instagram'), $this->external('x.com', 'X')],
        ]));

        $this->assertSame([], $goals);
    }

    /**
     * @param array<int, array<string, mixed>> $links
     * @param array<string, mixed> $extra
     * @return array<string, mixed>
     */
    private function analysis(array $links, array $extra = []): array
    {
        return array_merge([
            'url' => 'https://www.example.com/',
            'technologies' => [],
            'platform' => null,
            'pagesCrawled' => 10,
            'pages' => [],
            'links' => $links,
            'forms' => [],
            'downloads' => [],
            'externalLinks' => [],
        ], $extra);
    }

    /**
     * @param array<string, mixed> $extra
     * @return array<string, mixed>
     */
    private function link(string $target, string $text, array $extra = []): array
    {
        return array_merge([
            'linkText' => $text,
            'linkTarget' => $target,
            'labelSamples' => [$text],
            'pageCount' => 5,
            'occurrenceCount' => 5,
            'buttonLikeCount' => 0,
            'heroCount' => 0,
            'areas' => ['nav'],
            'exampleUrls' => ['https://www.example.com' . $target],
        ], $extra);
    }

    /**
     * @param string[] $fieldTypes
     * @param string[] $pages
     * @return array<string, mixed>
     */
    private function form(array $fieldTypes, string $submit, array $pages): array
    {
        return [
            'action' => $pages[0],
            'fields' => $fieldTypes,
            'fieldTypes' => $fieldTypes,
            'submitTexts' => [$submit],
            'sourcePages' => array_map(function (string $path): string {
                return 'https://www.example.com' . $path;
            }, $pages),
            'count' => count($pages),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function download(string $href, string $label): array
    {
        return ['href' => $href, 'labels' => [$label], 'sourcePages' => ['https://www.example.com/'], 'examples' => [$href], 'count' => 1];
    }

    /**
     * @return array<string, mixed>
     */
    private function external(string $host, string $label, string $href = '', int $pages = 2): array
    {
        $href = $href ?: 'https://' . $host . '/';

        return ['host' => $host, 'href' => $href, 'labels' => array_filter([$label]), 'sourcePages' => array_fill(0, $pages, 'https://www.example.com/'), 'examples' => [$href], 'count' => $pages];
    }
}
