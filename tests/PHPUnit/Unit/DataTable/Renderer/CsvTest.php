<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\DataTable\Renderer;

use Piwik\DataTable;
use Piwik\DataTable\Renderer\Csv;

/**
 * @group DataTableTest
 */
class CsvTest extends RendererTestCase
{
    /**
     * @dataProvider getTestCases
     */
    public function testRender(callable $tableCallback, string $expected, callable $rendererCallback = null)
    {
        $renderer = new Csv();

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
            "label,bool,goals_idgoal=1_revenue,goals_idgoal=1_nb_conversions,nb_uniq_visitors,nb_visits,nb_actions,max_actions,sum_visit_length,bounce_count,metadata_url,metadata_logo\n" .
            "Google©,0,5.5,10,11,11,17,5,517,9,\"http://www.google.com/display\"\"and,properly\",./plugins/Morpheus/icons/dist/searchEngines/www.google.com.png\n" .
            "Yahoo!,1,,,15,151,147,50,517,90,http://www.yahoo.com,./plugins/Morpheus/icons/dist/searchEngines/www.yahoo.com.png",
        ];

        yield 'render normal datatable without metadata' => [
            function () {
                return self::getDataTable();
            },
            "label,bool,goals_idgoal=1_revenue,goals_idgoal=1_nb_conversions,nb_uniq_visitors,nb_visits,nb_actions,max_actions,sum_visit_length,bounce_count\n" .
            "Google©,0,5.5,10,11,11,17,5,517,9\n" .
            "Yahoo!,1,,,15,151,147,50,517,90",
            function ($renderer) {
                $renderer->setHideMetadataFromResponse(true);
            },
        ];

        yield 'render simple datatable' => [
            function () {
                return self::getDataTableSimple();
            },
            "max_actions,nb_uniq_visitors,nb_visits,nb_actions,sum_visit_length,bounce_count\n14,57,66,151,5118,44",
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
            "label,count,metadata_test,metadata_mymeta
sub1,1,,
sub2,2,render,
sub3,2,renderMe,
sub4,6,,
sub5,2,,should be rendered
sub6,3,,renderrrrrr",
        ];

        yield 'render datatable map' => [
            function () {
                return self::getDataTableMap();
            },
            "testKey,label,nb_uniq_visitors,nb_visits,metadata_url,metadata_logo\n" .
            "date1,Google,11,11,http://www.google.com,./plugins/Morpheus/icons/dist/searchEngines/www.google.com.png\n" .
            "date1,Yahoo!,15,151,http://www.yahoo.com,./plugins/Morpheus/icons/dist/searchEngines/www.yahoo.com.png\n" .
            "date2,Google1©,110,110,http://www.google.com1,./plugins/Morpheus/icons/dist/searchEngines/www.google.com.png1\n" .
            "date2,Yahoo!1,150,1510,http://www.yahoo.com1,./plugins/Morpheus/icons/dist/searchEngines/www.yahoo.com.png1",
        ];

        yield 'render datatable map without metadata' => [
            function () {
                return self::getDataTableMap();
            },
            "testKey,label,nb_uniq_visitors,nb_visits\n" .
            "date1,Google,11,11\n" .
            "date1,Yahoo!,15,151\n" .
            "date2,Google1©,110,110\n" .
            "date2,Yahoo!1,150,1510",
            function ($renderer) {
                $renderer->setHideMetadataFromResponse(true);
            },
        ];

        yield 'render simple datatable map' => [
            function () {
                return self::getDataTableSimpleMap();
            },
            "testKey,max_actions,nb_uniq_visitors\nrow1,14,57\nrow2,140,570",
        ];

        yield 'render datatable map holding simple tables with one row only' => [
            function () {
                return self::getDataTableSimpleOneRowMap();
            },
            "testKey,value\nrow1,14\nrow2,15",
        ];

        yield 'render map of datatable maps with normal datatables' => [
            function () {
                return self::getDataTableMapContainsDataTableMapNormal();
            },
            "parentArrayKey,testKey,label,nb_uniq_visitors,nb_visits,metadata_url,metadata_logo\n" .
            "idSite,date1,Google,11,11,http://www.google.com,./plugins/Morpheus/icons/dist/searchEngines/www.google.com.png\n" .
            "idSite,date1,Yahoo!,15,151,http://www.yahoo.com,./plugins/Morpheus/icons/dist/searchEngines/www.yahoo.com.png\n" .
            "idSite,date2,Google1©,110,110,http://www.google.com1,./plugins/Morpheus/icons/dist/searchEngines/www.google.com.png1\n" .
            "idSite,date2,Yahoo!1,150,1510,http://www.yahoo.com1,./plugins/Morpheus/icons/dist/searchEngines/www.yahoo.com.png1",
        ];

        yield 'render map of datatable maps with normal datatables without metadata' => [
            function () {
                return self::getDataTableMapContainsDataTableMapNormal();
            },
            "parentArrayKey,testKey,label,nb_uniq_visitors,nb_visits\n" .
            "idSite,date1,Google,11,11\n" .
            "idSite,date1,Yahoo!,15,151\n" .
            "idSite,date2,Google1©,110,110\n" .
            "idSite,date2,Yahoo!1,150,1510",
            function ($renderer) {
                $renderer->setHideMetadataFromResponse(true);
            },
        ];

        yield 'render map of datatable maps with simple datatables' => [
            function () {
                return self::getDataTableMapContainsDataTableMapSimple();
            },
            "parentArrayKey,testKey,max_actions,nb_uniq_visitors\nidSite,row1,14,57\nidSite,row2,140,570",
        ];

        yield 'render map of datatable maps with datatables having one row only' => [
            function () {
                return self::getDataTableMapContainsDataTableMapSimpleOneRow();
            },
            "parentArrayKey,testKey,value\nidSite,row1,14\nidSite,row2,15",
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
            'a,c,e,5
b,d,f,g',
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
            '"col,1"#"col;2"
"val""1"#"val"",2"
val#"val#2"',
            function ($renderer) {
                $renderer->setSeparator('#');
            },
        ];
    }

    protected static function getDataTableSimpleWithCommasInCells()
    {
        $table = new DataTable();
        $table->addRowsFromSimpleArray([
            ["col,1" => "val\"1", "col;2" => "val\",2"],
            ["col,1" => "val", "col;2" => "val#2"],
        ]);
        return $table;
    }
}
