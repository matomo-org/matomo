<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace DataTable\Renderer;

use Piwik\DataTable;
use Piwik\DataTable\Renderer\Tsv;
use Piwik\Tests\Unit\DataTable\Renderer\RendererTestCase;

/**
 * @group DataTableTest
 */
class TsvTest extends RendererTestCase
{
    /**
     * @dataProvider getTestCases
     */
    public function testRender(callable $tableCallback, string $expected, callable $rendererCallback = null)
    {
        $renderer = new Tsv();

        if (is_callable($rendererCallback)) {
            $rendererCallback($renderer);
        }

        $renderer->setTable($tableCallback());
        $renderer->convertToUnicode = false;
        $rendered = $renderer->render();
        $this->assertEquals($expected, $rendered);
    }

    public function getTestCases(): iterable
    {
        yield 'render normal datatable' => [
            function () {
                return self::getDataTable();
            },
            "label\tbool\tgoals_idgoal=1_revenue\tgoals_idgoal=1_nb_conversions\tnb_uniq_visitors\tnb_visits\tnb_actions\tmax_actions\tsum_visit_length\tbounce_count\tmetadata_url\tmetadata_logo\n" .
            "Google©\t0\t5.5\t10\t11\t11\t17\t5\t517\t9\t\"http://www.google.com/display\"\"and,properly\"\t./plugins/Morpheus/icons/dist/searchEngines/www.google.com.png\n" .
            "Yahoo!\t1\t\t\t15\t151\t147\t50\t517\t90\thttp://www.yahoo.com\t./plugins/Morpheus/icons/dist/searchEngines/www.yahoo.com.png",
        ];

        yield 'render normal datatable without metadata' => [
            function () {
                return self::getDataTable();
            },
            "label\tbool\tgoals_idgoal=1_revenue\tgoals_idgoal=1_nb_conversions\tnb_uniq_visitors\tnb_visits\tnb_actions\tmax_actions\tsum_visit_length\tbounce_count\n" .
            "Google©\t0\t5.5\t10\t11\t11\t17\t5\t517\t9\n" .
            "Yahoo!\t1\t\t\t15\t151\t147\t50\t517\t90",
            function ($renderer) {
                $renderer->setHideMetadataFromResponse(true);
            },
        ];

        yield 'render simple datatable' => [
            function () {
                return self::getDataTableSimple();
            },
            "max_actions\tnb_uniq_visitors\tnb_visits\tnb_actions\tsum_visit_length\tbounce_count\n14\t57\t66\t151\t5118\t44",
        ];

        yield 'render simple datatable with one row' => [
            function () {
                return self::getDataTableSimpleOneRow();
            },
            "value\n14",
        ];

        yield 'render simple datatable with one row having a zero value' => [
            function () {
                return self::getDataTableSimpleOneZeroRow();
            },
            "value\n0",
        ];

        yield 'render simple datatable with one row having a false value' => [
            function () {
                return self::getDataTableSimpleOneFalseRow();
            },
            "value\n0",
        ];

        yield 'render empty datatable' => [
            function () {
                return self::getDataTableEmpty();
            },
            'No data available',
        ];

        yield 'render datatable with array in row metadata' => [
            function () {
                return self::getDataTableHavingAnArrayInRowMetadata();
            },
            // array in row metadata is not rendered
            "label\tcount\tmetadata_test\tmetadata_mymeta
sub1\t1\t\t
sub2\t2\trender\t
sub3\t2\trenderMe\t
sub4\t6\t\t
sub5\t2\t\tshould be rendered
sub6\t3\t\trenderrrrrr",
        ];

        yield 'render datatable map' => [
            function () {
                return self::getDataTableMap();
            },
            "testKey\tlabel\tnb_uniq_visitors\tnb_visits\tmetadata_url\tmetadata_logo\n" .
            "date1\tGoogle\t11\t11\thttp://www.google.com\t./plugins/Morpheus/icons/dist/searchEngines/www.google.com.png\n" .
            "date1\tYahoo!\t15\t151\thttp://www.yahoo.com\t./plugins/Morpheus/icons/dist/searchEngines/www.yahoo.com.png\n" .
            "date2\tGoogle1©\t110\t110\thttp://www.google.com1\t./plugins/Morpheus/icons/dist/searchEngines/www.google.com.png1\n" .
            "date2\tYahoo!1\t150\t1510\thttp://www.yahoo.com1\t./plugins/Morpheus/icons/dist/searchEngines/www.yahoo.com.png1",
        ];

        yield 'render datatable map without metadata' => [
            function () {
                return self::getDataTableMap();
            },
            "testKey\tlabel\tnb_uniq_visitors\tnb_visits\n" .
            "date1\tGoogle\t11\t11\n" .
            "date1\tYahoo!\t15\t151\n" .
            "date2\tGoogle1©\t110\t110\n" .
            "date2\tYahoo!1\t150\t1510",
            function ($renderer) {
                $renderer->setHideMetadataFromResponse(true);
            },
        ];

        yield 'render simple datatable map' => [
            function () {
                return self::getDataTableSimpleMap();
            },
            "testKey\tmax_actions\tnb_uniq_visitors\nrow1\t14\t57\nrow2\t140\t570",
        ];

        yield 'render datatable map holding simple tables with one row only' => [
            function () {
                return self::getDataTableSimpleOneRowMap();
            },
            "testKey\tvalue\nrow1\t14\nrow2\t15",
        ];

        yield 'render map of datatable maps with normal datatables' => [
            function () {
                return self::getDataTableMapContainsDataTableMapNormal();
            },
            "parentArrayKey\ttestKey\tlabel\tnb_uniq_visitors\tnb_visits\tmetadata_url\tmetadata_logo\n" .
            "idSite\tdate1\tGoogle\t11\t11\thttp://www.google.com\t./plugins/Morpheus/icons/dist/searchEngines/www.google.com.png\n" .
            "idSite\tdate1\tYahoo!\t15\t151\thttp://www.yahoo.com\t./plugins/Morpheus/icons/dist/searchEngines/www.yahoo.com.png\n" .
            "idSite\tdate2\tGoogle1©\t110\t110\thttp://www.google.com1\t./plugins/Morpheus/icons/dist/searchEngines/www.google.com.png1\n" .
            "idSite\tdate2\tYahoo!1\t150\t1510\thttp://www.yahoo.com1\t./plugins/Morpheus/icons/dist/searchEngines/www.yahoo.com.png1",
        ];

        yield 'render map of datatable maps with normal datatables without metadata' => [
            function () {
                return self::getDataTableMapContainsDataTableMapNormal();
            },
            "parentArrayKey\ttestKey\tlabel\tnb_uniq_visitors\tnb_visits\n" .
            "idSite\tdate1\tGoogle\t11\t11\n" .
            "idSite\tdate1\tYahoo!\t15\t151\n" .
            "idSite\tdate2\tGoogle1©\t110\t110\n" .
            "idSite\tdate2\tYahoo!1\t150\t1510",
            function ($renderer) {
                $renderer->setHideMetadataFromResponse(true);
            },
        ];

        yield 'render map of datatable maps with simple datatables' => [
            function () {
                return self::getDataTableMapContainsDataTableMapSimple();
            },
            "parentArrayKey\ttestKey\tmax_actions\tnb_uniq_visitors\nidSite\trow1\t14\t57\nidSite\trow2\t140\t570",
        ];

        yield 'render map of datatable maps with datatables having one row only' => [
            function () {
                return self::getDataTableMapContainsDataTableMapSimpleOneRow();
            },
            "parentArrayKey\ttestKey\tvalue\nidSite\trow1\t14\nidSite\trow2\t15",
        ];

        yield 'render empty array' => [
            function () {
                return [];
            },
            'No data available',
        ];

        yield 'render value array' => [
            function () {
                return ['a', 'b', 'c'];
            },
            'a
b
c',
        ];

        yield 'render key / value array' => [
            function () {
                return ['a' => 'b', 'c' => 'd', 'e' => 'f', 5 => 'g'];
            },
            "a\tc\te\t5
b\td\tf\tg",
        ];

        yield 'render key / value array with one element' => [
            function () {
                return ['a' => 'b'];
            },
            "a\nb",
        ];

        yield "render formula starting with =, should be escaped with leading '" => [
            function () {
                return ['=SUM(A)' => '=SUM(A;B)'];
            },
            "'=SUM(A)\n\"'=SUM(A;B)\"",
        ];

        yield "render formula starting with +, should be escaped with leading '" => [
            function () {
                return ['+A1' => '+A2,B3'];
            },
            "'+A1\n\"'+A2,B3\"",
        ];

        yield "render formula starting with -, should be escaped with leading '" => [
            function () {
                return ['-A1' => '-A2,B3'];
            },
            "'-A1\n\"'-A2,B3\"",
        ];

        yield "render formula with leading null byte, should still be escaped with leading '" => [
            function () {
                return ["\0-A1" => '%00=SUM(A)'];
            },
            "'\0-A1\n'%00=SUM(A)",
        ];

        yield "render formula with leading null bytes, should still be escaped with leading '" => [
            function () {
                return ["\0%00\0%00=@A1" => "%00\0%00%00=SUM(A)"];
            },
            "'\0%00\0%00=@A1\n'%00\0%00%00=SUM(A)",
        ];

        yield 'renders headers and values correctly escaped' => [
            function () {
                return self::getDataTableSimpleWithCommasInCells();
            },
            "\"col, 1\"\t\"col;2\"
\"val\"\"1\"\t\"val\"\" 2\"
val	val#2"
        ];
    }

    protected static function getDataTableSimpleWithCommasInCells()
    {
        $table = new DataTable();
        $table->addRowsFromSimpleArray([
            ["col,\t1" => "val\"1", "col;2" => "val\"\t2"],
            ["col,\t1" => "val", "col;2" => "val#2"],
        ]);
        return $table;
    }
}
