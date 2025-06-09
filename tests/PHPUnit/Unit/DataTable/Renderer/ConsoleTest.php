<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\DataTable\Renderer;

use Piwik\DataTable\Renderer\Console;

/**
 * @group DataTableTest
 */
class ConsoleTest extends RendererTestCase
{
    /**
     * @dataProvider getTestCases
     */
    public function testRender($tableCallback, string $expected, callable $rendererCallback = null)
    {
        $renderer = new Console();

        if (is_callable($rendererCallback)) {
            $rendererCallback($renderer);
        }

        $renderer->setTable($tableCallback());
        $rendered = $renderer->render();
        $this->assertEquals($expected, $rendered);
    }

    public function getTestCases(): iterable
    {
        yield 'render normal datatable' => [
            function () {
                return self::getDataTable();
            },
            "- 1 ['label' => 'Google&copy;', 'bool' => , 'goals' => array (
  'idgoal=1' => 
  array (
    'revenue' => 5.5,
    'nb_conversions' => 10,
  ),
), 'nb_uniq_visitors' => 11, 'nb_visits' => 11, 'nb_actions' => 17, 'max_actions' => '5', 'sum_visit_length' => 517, 'bounce_count' => 9] ['url' => 'http://www.google.com/display\"and,properly', 'logo' => './plugins/Morpheus/icons/dist/searchEngines/www.google.com.png'] [idsubtable = ]<br />
- 2 ['label' => 'Yahoo!', 'nb_uniq_visitors' => 15, 'bool' => 1, 'nb_visits' => 151, 'nb_actions' => 147, 'max_actions' => '50', 'sum_visit_length' => 517, 'bounce_count' => 90] ['url' => 'http://www.yahoo.com', 'logo' => './plugins/Morpheus/icons/dist/searchEngines/www.yahoo.com.png'] [idsubtable = 2]<br />
#- 1 ['label' => 'sub1', 'count' => 1, 'bool' => ] [] [idsubtable = ]<br />
#- 2 ['label' => 'sub2', 'count' => 2, 'bool' => 1] [] [idsubtable = ]<br />
",
        ];

        yield 'render normal datatable without metadata' => [
            function () {
                return self::getDataTable();
            },
            "- 1 ['label' => 'Google&copy;', 'bool' => , 'goals' => array (
  'idgoal=1' => 
  array (
    'revenue' => 5.5,
    'nb_conversions' => 10,
  ),
), 'nb_uniq_visitors' => 11, 'nb_visits' => 11, 'nb_actions' => 17, 'max_actions' => '5', 'sum_visit_length' => 517, 'bounce_count' => 9]<br />
- 2 ['label' => 'Yahoo!', 'nb_uniq_visitors' => 15, 'bool' => 1, 'nb_visits' => 151, 'nb_actions' => 147, 'max_actions' => '50', 'sum_visit_length' => 517, 'bounce_count' => 90]<br />
#- 1 ['label' => 'sub1', 'count' => 1, 'bool' => ]<br />
#- 2 ['label' => 'sub2', 'count' => 2, 'bool' => 1]<br />
",
            function ($renderer) {
                $renderer->setHideMetadataFromResponse(true);
            },
        ];

        yield 'render simple datatable' => [
            function () {
                return self::getDataTableSimple();
            },
            "- 1 ['max_actions' => 14, 'nb_uniq_visitors' => 57, 'nb_visits' => 66, 'nb_actions' => 151, 'sum_visit_length' => 5118, 'bounce_count' => 44] [] [idsubtable = ]<br />\n",
        ];

        yield 'render simple datatable with one row' => [
            function () {
                return self::getDataTableSimpleOneRow();
            },
            "- 1 ['nb_visits' => 14] [] [idsubtable = ]<br />\n",
        ];

        yield 'render simple datatable with one row having a zero value' => [
            function () {
                return self::getDataTableSimpleOneZeroRow();
            },
            "- 1 ['nb_visits' => 0] [] [idsubtable = ]<br />\n",
        ];

        yield 'render simple datatable with one row having a false value' => [
            function () {
                return self::getDataTableSimpleOneFalseRow();
            },
            "- 1 ['is_excluded' => ] [] [idsubtable = ]<br />\n",
        ];

        yield 'render empty datatable' => [
            function () {
                return self::getDataTableEmpty();
            },
            "Empty table<br />\n",
        ];

        yield 'render datatable with array in row metadata' => [
            function () {
                return self::getDataTableHavingAnArrayInRowMetadata();
            },
            // array in row metadata is not rendered
            "- 1 ['label' => 'sub1', 'count' => 1] [] [idsubtable = ]<br />
- 2 ['label' => 'sub2', 'count' => 2] ['test' => 'render'] [idsubtable = ]<br />
- 3 ['label' => 'sub3', 'count' => 2] ['test' => 'renderMe', 'testArray' => 'ignore'] [idsubtable = ]<br />
- 4 ['label' => 'sub4', 'count' => 6] ['testArray' => array (
  0 => 'do not render',
)] [idsubtable = ]<br />
- 5 ['label' => 'sub5', 'count' => 2] ['testArray' => 'do ignore', 'mymeta' => 'should be rendered'] [idsubtable = ]<br />
- 6 ['label' => 'sub6', 'count' => 3] ['mymeta' => 'renderrrrrr'] [idsubtable = ]<br />
",
        ];

        yield 'render datatable map' => [
            function () {
                return self::getDataTableMap();
            },
            "Set<hr />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>date1</b><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- 1 ['label' => 'Google', 'nb_uniq_visitors' => 11, 'nb_visits' => 11] ['url' => 'http://www.google.com', 'logo' => './plugins/Morpheus/icons/dist/searchEngines/www.google.com.png'] [idsubtable = ]<br />
- 2 ['label' => 'Yahoo!', 'nb_uniq_visitors' => 15, 'nb_visits' => 151] ['url' => 'http://www.yahoo.com', 'logo' => './plugins/Morpheus/icons/dist/searchEngines/www.yahoo.com.png'] [idsubtable = ]<br />
<hr />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>date2</b><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- 1 ['label' => 'Google1&copy;', 'nb_uniq_visitors' => 110, 'nb_visits' => 110] ['url' => 'http://www.google.com1', 'logo' => './plugins/Morpheus/icons/dist/searchEngines/www.google.com.png1'] [idsubtable = ]<br />
- 2 ['label' => 'Yahoo!1', 'nb_uniq_visitors' => 150, 'nb_visits' => 1510] ['url' => 'http://www.yahoo.com1', 'logo' => './plugins/Morpheus/icons/dist/searchEngines/www.yahoo.com.png1'] [idsubtable = ]<br />
<hr />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>date3</b><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Empty table<br />
<hr />",
        ];

        yield 'render datatable map without metadata' => [
            function () {
                return self::getDataTableMap();
            },
            "Set<hr />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>date1</b><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- 1 ['label' => 'Google', 'nb_uniq_visitors' => 11, 'nb_visits' => 11]<br />
- 2 ['label' => 'Yahoo!', 'nb_uniq_visitors' => 15, 'nb_visits' => 151]<br />
<hr />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>date2</b><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- 1 ['label' => 'Google1&copy;', 'nb_uniq_visitors' => 110, 'nb_visits' => 110]<br />
- 2 ['label' => 'Yahoo!1', 'nb_uniq_visitors' => 150, 'nb_visits' => 1510]<br />
<hr />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>date3</b><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Empty table<br />
<hr />",
            function ($renderer) {
                $renderer->setHideMetadataFromResponse(true);
            },
        ];

        yield 'render simple datatable map' => [
            function () {
                return self::getDataTableSimpleMap();
            },
            "Set<hr />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>row1</b><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- 1 ['max_actions' => 14, 'nb_uniq_visitors' => 57] [] [idsubtable = ]<br />
<hr />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>row2</b><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- 1 ['max_actions' => 140, 'nb_uniq_visitors' => 570] [] [idsubtable = ]<br />
<hr />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>row3</b><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Empty table<br />
<hr />",
        ];

        yield 'render datatable map holding simple tables with one row only' => [
            function () {
                return self::getDataTableSimpleOneRowMap();
            },
            "Set<hr />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>row1</b><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- 1 ['nb_visits' => 14] [] [idsubtable = ]<br />
<hr />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>row2</b><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- 1 ['nb_visits' => 15] [] [idsubtable = ]<br />
<hr />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>row3</b><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Empty table<br />
<hr />",
        ];

        yield 'render map of datatable maps with normal datatables' => [
            function () {
                return self::getDataTableMapContainsDataTableMapNormal();
            },
            "Set<hr />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>idSite</b><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Set<hr />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>date1</b><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- 1 ['label' => 'Google', 'nb_uniq_visitors' => 11, 'nb_visits' => 11] ['url' => 'http://www.google.com', 'logo' => './plugins/Morpheus/icons/dist/searchEngines/www.google.com.png'] [idsubtable = ]<br />
- 2 ['label' => 'Yahoo!', 'nb_uniq_visitors' => 15, 'nb_visits' => 151] ['url' => 'http://www.yahoo.com', 'logo' => './plugins/Morpheus/icons/dist/searchEngines/www.yahoo.com.png'] [idsubtable = ]<br />
<hr />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>date2</b><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- 1 ['label' => 'Google1&copy;', 'nb_uniq_visitors' => 110, 'nb_visits' => 110] ['url' => 'http://www.google.com1', 'logo' => './plugins/Morpheus/icons/dist/searchEngines/www.google.com.png1'] [idsubtable = ]<br />
- 2 ['label' => 'Yahoo!1', 'nb_uniq_visitors' => 150, 'nb_visits' => 1510] ['url' => 'http://www.yahoo.com1', 'logo' => './plugins/Morpheus/icons/dist/searchEngines/www.yahoo.com.png1'] [idsubtable = ]<br />
<hr />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>date3</b><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Empty table<br />
<hr /><hr />",
        ];

        yield 'render map of datatable maps with normal datatables without metadata' => [
            function () {
                return self::getDataTableMapContainsDataTableMapNormal();
            },
            "Set<hr />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>idSite</b><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Set<hr />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>date1</b><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- 1 ['label' => 'Google', 'nb_uniq_visitors' => 11, 'nb_visits' => 11]<br />
- 2 ['label' => 'Yahoo!', 'nb_uniq_visitors' => 15, 'nb_visits' => 151]<br />
<hr />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>date2</b><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- 1 ['label' => 'Google1&copy;', 'nb_uniq_visitors' => 110, 'nb_visits' => 110]<br />
- 2 ['label' => 'Yahoo!1', 'nb_uniq_visitors' => 150, 'nb_visits' => 1510]<br />
<hr />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>date3</b><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Empty table<br />
<hr /><hr />",
            function ($renderer) {
                $renderer->setHideMetadataFromResponse(true);
            },
        ];

        yield 'render map of datatable maps with simple datatables' => [
            function () {
                return self::getDataTableMapContainsDataTableMapSimple();
            },
            "Set<hr />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>idSite</b><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Set<hr />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>row1</b><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- 1 ['max_actions' => 14, 'nb_uniq_visitors' => 57] [] [idsubtable = ]<br />
<hr />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>row2</b><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- 1 ['max_actions' => 140, 'nb_uniq_visitors' => 570] [] [idsubtable = ]<br />
<hr />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>row3</b><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Empty table<br />
<hr /><hr />",
        ];

        yield 'render map of datatable maps with datatables having one row only' => [
            function () {
                return self::getDataTableMapContainsDataTableMapSimpleOneRow();
            },
            "Set<hr />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>idSite</b><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Set<hr />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>row1</b><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- 1 ['nb_visits' => 14] [] [idsubtable = ]<br />
<hr />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>row2</b><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- 1 ['nb_visits' => 15] [] [idsubtable = ]<br />
<hr />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>row3</b><br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Empty table<br />
<hr /><hr />",
        ];

        yield 'render empty array' => [
            function () {
                return [];
            },
            "Empty table<br />\n",
        ];

        yield 'render value array' => [
            function () {
                return ['a', 'b', 'c'];
            },
            "- 1 ['0' => 'a'] [] [idsubtable = ]<br />
- 2 ['0' => 'b'] [] [idsubtable = ]<br />
- 3 ['0' => 'c'] [] [idsubtable = ]<br />
",
        ];

        yield 'render key / value array' => [
            function () {
                return ['a' => 'b', 'c' => 'd', 'e' => 'f', 5 => 'g'];
            },
            "- 1 ['a' => 'b', 'c' => 'd', 'e' => 'f', '5' => 'g'] [] [idsubtable = ]<br />\n",
        ];

        yield 'render key / value array with one element' => [
            function () {
                return ['a' => 'b'];
            },
            "- 1 ['a' => 'b'] [] [idsubtable = ]<br />\n",
        ];
    }
}
