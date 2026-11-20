<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\DebugView\tests\Unit;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\DebugView\HitFlattener;

/**
 * @group DebugView
 * @group DebugViewHitFlattenerTest
 * @group Plugins
 */
class HitFlattenerTest extends TestCase
{
    /**
     * @var HitFlattener
     */
    private $flattener;

    public function setUp(): void
    {
        parent::setUp();
        $this->flattener = new HitFlattener();
    }

    /**
     * @dataProvider getDeriveTypeTestData
     */
    public function testDeriveType(array $query, string $expected)
    {
        $this->assertSame($expected, $this->flattener->deriveType($query));
    }

    public function getDeriveTypeTestData(): array
    {
        return [
            'pageview via action_name' => [['action_name' => 'Home', 'url' => 'http://x/'], 'pageview'],
            'pageview via url only'    => [['url' => 'http://x/'], 'pageview'],
            'event'                    => [['e_c' => 'Videos', 'e_a' => 'play', 'url' => 'http://x/'], 'event'],
            'event with action only'   => [['e_a' => 'play'], 'event'],
            'site search'              => [['search' => 'shoes', 'url' => 'http://x/'], 'search'],
            'search wins over pageview' => [['search' => 'shoes', 'action_name' => 'Page'], 'search'],
            'download'                 => [['download' => 'http://x/f.zip', 'url' => 'http://x/f.zip'], 'download'],
            'outlink'                  => [['link' => 'http://ext/', 'url' => 'http://x/'], 'outlink'],
            'goal'                     => [['idgoal' => '2', 'url' => 'http://x/'], 'goal'],
            'ecommerce order'          => [['idgoal' => '0', 'ec_id' => 'A100'], 'ecommerceOrder'],
            'abandoned cart'           => [['idgoal' => '0', 'ec_items' => '[]'], 'ecommerceAbandonedCart'],
            'content'                  => [['c_n' => 'Banner', 'c_p' => 'img.jpg'], 'content'],
            'ping'                     => [['ping' => '1', 'url' => 'http://x/'], 'ping'],
            'ping wins over download'  => [['ping' => '1', 'download' => 'http://x/f'], 'ping'],
            'download wins over event' => [['download' => 'http://x/f', 'e_c' => 'c'], 'download'],
            'empty request'            => [[], 'other'],
            'unrecognised params'      => [['idsite' => '1', 'rec' => '1'], 'other'],
        ];
    }

    /**
     * @dataProvider getPluginActionTypeTestData
     */
    public function testDeriveTypePrefersTheRecordedPluginActionType(int $actionType, string $expected)
    {
        // the query alone would look like a pageview; the tracker-recorded
        // action type wins
        $query = ['url' => 'http://x/', 'action_name' => 'Page'];

        $this->assertSame($expected, $this->flattener->deriveType($query, $actionType));
    }

    public function getPluginActionTypeTestData(): array
    {
        return [
            'media'             => [94, 'media'],
            'form'              => [95, 'form'],
            'session recording' => [96, 'sessionRecording'],
            'crash'             => [110, 'crash'],
        ];
    }

    public function testDeriveTypeFallsBackToParamsForCoreActionTypes()
    {
        $query = ['url' => 'http://x/', 'action_name' => 'Page'];

        $this->assertSame('pageview', $this->flattener->deriveType($query, 1));
        $this->assertSame('pageview', $this->flattener->deriveType($query, null));
        $this->assertSame('event', $this->flattener->deriveType(['e_c' => 'c', 'e_a' => 'a'], 10));
    }

    public function testDeriveTypeIgnoresUnknownActionTypes()
    {
        $this->assertSame('pageview', $this->flattener->deriveType(['url' => 'http://x/'], 999));
        $this->assertSame('other', $this->flattener->deriveType([], 999));
    }

    public function testToScalarStringKeepsScalarsAndDropsEverythingElse()
    {
        $this->assertSame('a', $this->flattener->toScalarString('a'));
        $this->assertSame('7', $this->flattener->toScalarString(7));
        $this->assertSame('1.5', $this->flattener->toScalarString(1.5));
        $this->assertSame('', $this->flattener->toScalarString(['x']));
        $this->assertSame('', $this->flattener->toScalarString(null));
        $this->assertSame('', $this->flattener->toScalarString(true));
    }

    public function testDeriveTypeToleratesArrayValuedParameters()
    {
        // PHP parses e.g. ping[]=1 into an array — attacker-controlled input
        // must never reach scalar string functions
        $this->assertSame('other', $this->flattener->deriveType(['ping' => ['1']]));
        $this->assertSame('download', $this->flattener->deriveType(['download' => ['http://x/f']]));
        $this->assertSame('pageview', $this->flattener->deriveType(['idgoal' => ['2'], 'url' => 'http://x/']));
    }

    public function testBuildTitleToleratesArrayValuedParameters()
    {
        // array values are dropped from the title parts instead of crashing
        $this->assertSame('play', $this->flattener->buildTitle(['e_c' => ['poison'], 'e_a' => 'play'], 'event'));
        $this->assertSame('Banner', $this->flattener->buildTitle(['c_n' => 'Banner', 'c_i' => ['x']], 'content'));
        $this->assertStringEndsWith('#0', $this->flattener->buildTitle(['idgoal' => ['9']], 'goal'));

        // every candidate malformed: falls back to the translated type name
        $title = $this->flattener->buildTitle(['e_c' => ['poison'], 'action_name' => ['x']], 'event');
        $this->assertIsString($title);
        $this->assertNotSame('', $title);
    }

    public function testBuildSubtitleToleratesArrayValuedParameters()
    {
        $this->assertSame('', $this->flattener->buildSubtitle(['url' => ['x']], 'pageview'));
        $this->assertSame('', $this->flattener->buildSubtitle(['download' => ['x']], 'download'));
    }

    public function testBuildTitleForGoalIgnoresPageFields()
    {
        $title = $this->flattener->buildTitle(['idgoal' => '3', 'action_name' => 'Page'], 'goal');

        $this->assertStringContainsString('#3', $title);
        $this->assertStringNotContainsString('Page', $title);
    }

    public function testBuildTitleForPingUsesTheUrl()
    {
        $this->assertSame(
            'http://x/',
            $this->flattener->buildTitle(['ping' => '1', 'url' => 'http://x/'], 'ping')
        );
    }

    public function testBuildTitleForAbandonedCartFallsBackToNonEmptyLabel()
    {
        $this->assertNotSame('', $this->flattener->buildTitle(['idgoal' => '0'], 'ecommerceAbandonedCart'));
    }

    public function testBuildTitleForMediaUsesMediaTitle()
    {
        $this->assertSame('My Video', $this->flattener->buildTitle(['ma_ti' => 'My Video'], 'media'));
    }

    public function testBuildTitleForFormUsesFormName()
    {
        $this->assertSame('Checkout', $this->flattener->buildTitle(['fa_name' => 'Checkout'], 'form'));
    }

    public function testBuildTitleForCrashUsesTheErrorMessage()
    {
        $this->assertSame(
            'TypeError: x is undefined',
            $this->flattener->buildTitle(['cra' => 'TypeError: x is undefined'], 'crash')
        );
    }

    public function testBuildTitleForPluginTypesFallsBackToNonEmptyLabel()
    {
        foreach (['media', 'form', 'sessionRecording', 'crash'] as $type) {
            $this->assertNotSame('', $this->flattener->buildTitle([], $type));
        }
    }

    public function testBuildTitleForEventJoinsCategoryActionName()
    {
        $query = ['e_c' => 'Videos', 'e_a' => 'play', 'e_n' => 'intro'];

        $this->assertSame('Videos – play – intro', $this->flattener->buildTitle($query, 'event'));
    }

    public function testBuildTitleForEventWithoutNameOmitsIt()
    {
        $query = ['e_c' => 'Videos', 'e_a' => 'play'];

        $this->assertSame('Videos – play', $this->flattener->buildTitle($query, 'event'));
    }

    public function testBuildTitleForSearchUsesKeyword()
    {
        $this->assertSame('shoes', $this->flattener->buildTitle(['search' => 'shoes'], 'search'));
    }

    public function testBuildTitleForGoalContainsTheGoalId()
    {
        $title = $this->flattener->buildTitle(['idgoal' => '7'], 'goal');

        $this->assertStringContainsString('#7', $title);
        $this->assertNotSame('#7', $title);
    }

    public function testBuildTitleForEcommerceOrderUsesOrderId()
    {
        $this->assertSame('A100', $this->flattener->buildTitle(['idgoal' => '0', 'ec_id' => 'A100'], 'ecommerceOrder'));
    }

    public function testBuildTitleForContentJoinsNameAndInteraction()
    {
        $query = ['c_n' => 'Banner', 'c_i' => 'click'];

        $this->assertSame('Banner – click', $this->flattener->buildTitle($query, 'content'));
    }

    public function testBuildTitleForPageviewPrefersActionNameOverUrl()
    {
        $query = ['action_name' => 'My Page', 'url' => 'http://example.org/'];

        $this->assertSame('My Page', $this->flattener->buildTitle($query, 'pageview'));
        $this->assertSame('http://example.org/', $this->flattener->buildTitle(['url' => 'http://example.org/'], 'pageview'));
    }

    public function testBuildTitleFallsBackToNonEmptyTypeLabel()
    {
        $this->assertNotSame('', $this->flattener->buildTitle([], 'download'));
        $this->assertNotSame('', $this->flattener->buildTitle([], 'other'));
        $this->assertNotSame('', $this->flattener->buildTitle([], 'someVendorType'));
    }

    public function testBuildSubtitleUsesSearchCategoryForSearches()
    {
        $query = ['search' => 'shoes', 'search_cat' => 'products', 'url' => 'http://x/'];

        $this->assertSame('products', $this->flattener->buildSubtitle($query, 'search'));
    }

    public function testBuildSubtitleUsesTheTargetUrlPerType()
    {
        $this->assertSame(
            'http://x/f.zip',
            $this->flattener->buildSubtitle(['download' => 'http://x/f.zip', 'url' => 'http://p/'], 'download')
        );
        $this->assertSame(
            'http://ext/',
            $this->flattener->buildSubtitle(['link' => 'http://ext/', 'url' => 'http://p/'], 'outlink')
        );
        $this->assertSame(
            'http://p/',
            $this->flattener->buildSubtitle(['url' => 'http://p/'], 'pageview')
        );
        $this->assertSame('', $this->flattener->buildSubtitle([], 'pageview'));
    }
}
