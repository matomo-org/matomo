<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Dashboard\tests\Integration;

use Piwik\Plugins\Dashboard\Dashboard;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

/**
 * @group Dashboard
 * @group Plugins
 */
class DashboardTest extends IntegrationTestCase
{
    /**
     * @var Dashboard
     */
    private $dashboard;

    public function setUp(): void
    {
        parent::setUp();

        $this->dashboard = new Dashboard();
    }

    public function testDecodeLayoutKeepsValidWidgetUniqueIds()
    {
        $layout = '[[{"uniqueId":"widgetLivewidget","parameters":{"module":"Live","action":"widget"}}]]';

        $decoded = $this->dashboard->decodeLayout($layout);

        $this->assertSame('widgetLivewidget', $decoded[0][0]->uniqueId);
    }

    /**
     * The allowed character set matches the output of WidgetsList::getWidgetUniqueId().
     */
    public function testDecodeLayoutKeepsAllValidUniqueIdCharacters()
    {
        $uniqueId = 'widgetVisitsSummaryget.forceView-1_viewDataTable+sparklines';
        $layout = '[[{"uniqueId":"' . $uniqueId . '","parameters":{"module":"VisitsSummary","action":"get"}}]]';

        $decoded = $this->dashboard->decodeLayout($layout);

        $this->assertSame($uniqueId, $decoded[0][0]->uniqueId);
    }

    /**
     * @dataProvider getLayoutsWithUnexpectedUniqueIdCharacters
     */
    public function testDecodeLayoutStripsUnexpectedCharactersFromUniqueIds(string $layout, string $expected)
    {
        $decoded = $this->dashboard->decodeLayout($layout);

        $this->assertSame($expected, $decoded[0][0]->uniqueId);
    }

    public function getLayoutsWithUnexpectedUniqueIdCharacters(): array
    {
        return [
            // entity encoded characters are restored while decoding and then removed
            ['[[{"uniqueId":"widget&lt;a&gt;b","parameters":{"module":"Live","action":"widget"}}]]', 'widgetab'],
            // unicode escaped characters are resolved by json_decode and then removed
            ['[[{"uniqueId":"widget\\u0022x","parameters":{"module":"Live","action":"widget"}}]]', 'widgetx'],
            // spaces, brackets and other punctuation are removed
            ['[[{"uniqueId":"widget [a] b#c","parameters":{"module":"Live","action":"widget"}}]]', 'widgetabc'],
        ];
    }

    public function testDecodeLayoutSanitizesEveryColumnAndWidget()
    {
        $layout = '[[{"uniqueId":"a [1]","parameters":{"module":"Live","action":"widget"}},'
            . '{"uniqueId":"b [2]","parameters":{"module":"Live","action":"widget"}}],'
            . '[{"uniqueId":"c [3]","parameters":{"module":"Live","action":"widget"}}]]';

        $decoded = $this->dashboard->decodeLayout($layout);

        $this->assertSame('a1', $decoded[0][0]->uniqueId);
        $this->assertSame('b2', $decoded[0][1]->uniqueId);
        $this->assertSame('c3', $decoded[1][0]->uniqueId);
    }

    public function testDecodeLayoutSanitizesUniqueIdsWithinColumnsObject()
    {
        $layout = '{"config":{"layout":"100"},'
            . '"columns":[[{"uniqueId":"widget [x]","parameters":{"module":"Live","action":"widget"}}]]}';

        $decoded = $this->dashboard->decodeLayout($layout);

        $this->assertSame('widgetx', $decoded->columns[0][0]->uniqueId);
    }

    public function testDecodeLayoutSanitizesColumnsEncodedAsObject()
    {
        // columns encoded as an object rather than an array must not skip sanitization
        $layout = '{"config":{"layout":"100"},'
            . '"columns":{"0":[{"uniqueId":"widget [x]","parameters":{"module":"Live","action":"widget"}}]}}';

        $decoded = $this->dashboard->decodeLayout($layout);

        $columns = (array) $decoded->columns;
        $this->assertSame('widgetx', $columns[0][0]->uniqueId);
    }

    public function testDecodeLayoutReducesNonStringUniqueIdToEmptyString()
    {
        $layout = '[[{"uniqueId":["a","[x]"],"parameters":{"module":"Live","action":"widget"}}]]';

        $decoded = $this->dashboard->decodeLayout($layout);

        $this->assertSame('', $decoded[0][0]->uniqueId);
    }
}
