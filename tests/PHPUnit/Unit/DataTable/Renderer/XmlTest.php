<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\DataTable\Renderer;

use Piwik\DataTable;
use Piwik\DataTable\Renderer\Xml;

/**
 * @group DataTableTest
 */
class XmlTest extends RendererTestCase
{
    /**
     * @dataProvider getTestCases
     */
    public function testRender(callable $tableCallback, string $expected, ?callable $rendererCallback = null)
    {
        $renderer = new Xml();

        if (is_callable($rendererCallback)) {
            $rendererCallback($renderer);
        }

        $renderer->setTable($tableCallback());
        $renderer->setRenderSubTables(true);
        $rendered = $renderer->render();
        $this->assertEquals($expected, $rendered);
    }

    public function getTestCases(): iterable
    {
        yield 'render normal datatable' => [
            function () {
                return self::getDataTable();
            },
            '<?xml version="1.0" encoding="utf-8" ?>
<result>
	<row>
		<label>Google©</label>
		<bool>0</bool>
		<goals>
			<row idgoal=\'1\'>
				<revenue>5.5</revenue>
				<nb_conversions>10</nb_conversions>
			</row>
		</goals>
		<nb_uniq_visitors>11</nb_uniq_visitors>
		<nb_visits>11</nb_visits>
		<nb_actions>17</nb_actions>
		<max_actions>5</max_actions>
		<sum_visit_length>517</sum_visit_length>
		<bounce_count>9</bounce_count>
		<url>http://www.google.com/display&quot;and,properly</url>
		<logo>./plugins/Morpheus/icons/dist/searchEngines/www.google.com.png</logo>
	</row>
	<row>
		<label>Yahoo!</label>
		<nb_uniq_visitors>15</nb_uniq_visitors>
		<bool>1</bool>
		<nb_visits>151</nb_visits>
		<nb_actions>147</nb_actions>
		<max_actions>50</max_actions>
		<sum_visit_length>517</sum_visit_length>
		<bounce_count>90</bounce_count>
		<url>http://www.yahoo.com</url>
		<logo>./plugins/Morpheus/icons/dist/searchEngines/www.yahoo.com.png</logo>
		<idsubdatatable>2</idsubdatatable>
		<subtable>
			<row>
				<label>sub1</label>
				<count>1</count>
				<bool>0</bool>
			</row>
			<row>
				<label>sub2</label>
				<count>2</count>
				<bool>1</bool>
			</row>
		</subtable>
	</row>
</result>',
        ];

        yield 'render normal datatable without metadata' => [
            function () {
                return self::getDataTable();
            },
            '<?xml version="1.0" encoding="utf-8" ?>
<result>
	<row>
		<label>Google©</label>
		<bool>0</bool>
		<goals>
			<row idgoal=\'1\'>
				<revenue>5.5</revenue>
				<nb_conversions>10</nb_conversions>
			</row>
		</goals>
		<nb_uniq_visitors>11</nb_uniq_visitors>
		<nb_visits>11</nb_visits>
		<nb_actions>17</nb_actions>
		<max_actions>5</max_actions>
		<sum_visit_length>517</sum_visit_length>
		<bounce_count>9</bounce_count>
	</row>
	<row>
		<label>Yahoo!</label>
		<nb_uniq_visitors>15</nb_uniq_visitors>
		<bool>1</bool>
		<nb_visits>151</nb_visits>
		<nb_actions>147</nb_actions>
		<max_actions>50</max_actions>
		<sum_visit_length>517</sum_visit_length>
		<bounce_count>90</bounce_count>
		<subtable>
			<row>
				<label>sub1</label>
				<count>1</count>
				<bool>0</bool>
			</row>
			<row>
				<label>sub2</label>
				<count>2</count>
				<bool>1</bool>
			</row>
		</subtable>
	</row>
</result>',
            function ($renderer) {
                $renderer->setHideMetadataFromResponse(true);
            },
        ];

        yield 'render simple datatable' => [
            function () {
                return self::getDataTableSimple();
            },
            '<?xml version="1.0" encoding="utf-8" ?>
<result>
	<max_actions>14</max_actions>
	<nb_uniq_visitors>57</nb_uniq_visitors>
	<nb_visits>66</nb_visits>
	<nb_actions>151</nb_actions>
	<sum_visit_length>5118</sum_visit_length>
	<bounce_count>44</bounce_count>
</result>',
        ];

        yield 'render simple datatable with one row' => [
            function () {
                return self::getDataTableSimpleOneRow();
            },
            '<?xml version="1.0" encoding="utf-8" ?>
<result>14</result>',
        ];

        yield 'render simple datatable with one row having a zero value' => [
            function () {
                return self::getDataTableSimpleOneZeroRow();
            },
            '<?xml version="1.0" encoding="utf-8" ?>
<result>0</result>',
        ];

        yield 'render simple datatable with one row having a false value' => [
            function () {
                return self::getDataTableSimpleOneFalseRow();
            },
            '<?xml version="1.0" encoding="utf-8" ?>
<result>0</result>',
        ];

        yield 'render empty datatable' => [
            function () {
                return self::getDataTableEmpty();
            },
            '<?xml version="1.0" encoding="utf-8" ?>
<result />',
        ];

        yield 'render datatable with array in row metadata' => [
            function () {
                return self::getDataTableHavingAnArrayInRowMetadata();
            },
            '<?xml version="1.0" encoding="utf-8" ?>
<result>
	<row>
		<label>sub1</label>
		<count>1</count>
	</row>
	<row>
		<label>sub2</label>
		<count>2</count>
		<test>render</test>
	</row>
	<row>
		<label>sub3</label>
		<count>2</count>
		<test>renderMe</test>
		<testArray>ignore</testArray>
	</row>
	<row>
		<label>sub4</label>
		<count>6</count>
		<testArray>
		<row>do not render</row>
		</testArray>
	</row>
	<row>
		<label>sub5</label>
		<count>2</count>
		<testArray>do ignore</testArray>
		<mymeta>should be rendered</mymeta>
	</row>
	<row>
		<label>sub6</label>
		<count>3</count>
		<mymeta>renderrrrrr</mymeta>
	</row>
</result>',
        ];

        yield 'render datatable map' => [
            function () {
                return self::getDataTableMap();
            },
            '<?xml version="1.0" encoding="utf-8" ?>
<results>
	<result testKey="date1">
		<row>
			<label>Google</label>
			<nb_uniq_visitors>11</nb_uniq_visitors>
			<nb_visits>11</nb_visits>
			<url>http://www.google.com</url>
			<logo>./plugins/Morpheus/icons/dist/searchEngines/www.google.com.png</logo>
		</row>
		<row>
			<label>Yahoo!</label>
			<nb_uniq_visitors>15</nb_uniq_visitors>
			<nb_visits>151</nb_visits>
			<url>http://www.yahoo.com</url>
			<logo>./plugins/Morpheus/icons/dist/searchEngines/www.yahoo.com.png</logo>
		</row>
	</result>
	<result testKey="date2">
		<row>
			<label>Google1©</label>
			<nb_uniq_visitors>110</nb_uniq_visitors>
			<nb_visits>110</nb_visits>
			<url>http://www.google.com1</url>
			<logo>./plugins/Morpheus/icons/dist/searchEngines/www.google.com.png1</logo>
		</row>
		<row>
			<label>Yahoo!1</label>
			<nb_uniq_visitors>150</nb_uniq_visitors>
			<nb_visits>1510</nb_visits>
			<url>http://www.yahoo.com1</url>
			<logo>./plugins/Morpheus/icons/dist/searchEngines/www.yahoo.com.png1</logo>
		</row>
	</result>
	<result testKey="date3" />
</results>',
        ];

        yield 'render datatable map without metadata' => [
            function () {
                return self::getDataTableMap();
            },
            '<?xml version="1.0" encoding="utf-8" ?>
<results>
	<result testKey="date1">
		<row>
			<label>Google</label>
			<nb_uniq_visitors>11</nb_uniq_visitors>
			<nb_visits>11</nb_visits>
		</row>
		<row>
			<label>Yahoo!</label>
			<nb_uniq_visitors>15</nb_uniq_visitors>
			<nb_visits>151</nb_visits>
		</row>
	</result>
	<result testKey="date2">
		<row>
			<label>Google1©</label>
			<nb_uniq_visitors>110</nb_uniq_visitors>
			<nb_visits>110</nb_visits>
		</row>
		<row>
			<label>Yahoo!1</label>
			<nb_uniq_visitors>150</nb_uniq_visitors>
			<nb_visits>1510</nb_visits>
		</row>
	</result>
	<result testKey="date3" />
</results>',
            function ($renderer) {
                $renderer->setHideMetadataFromResponse(true);
            },
        ];

        yield 'render simple datatable map' => [
            function () {
                return self::getDataTableSimpleMap();
            },
            '<?xml version="1.0" encoding="utf-8" ?>
<results>
	<result testKey="row1">
		<max_actions>14</max_actions>
		<nb_uniq_visitors>57</nb_uniq_visitors>
	</result>
	<result testKey="row2">
		<max_actions>140</max_actions>
		<nb_uniq_visitors>570</nb_uniq_visitors>
	</result>
	<result testKey="row3" />
</results>',
        ];

        yield 'render datatable map holding simple tables with one row only' => [
            function () {
                return self::getDataTableSimpleOneRowMap();
            },
            '<?xml version="1.0" encoding="utf-8" ?>
<results>
	<result testKey="row1">14</result>
	<result testKey="row2">15</result>
	<result testKey="row3" />
</results>',
        ];

        yield 'render map of datatable maps with normal datatables' => [
            function () {
                return self::getDataTableMapContainsDataTableMapNormal();
            },
            '<?xml version="1.0" encoding="utf-8" ?>
<results>
	<result parentArrayKey="idSite">
		<result testKey="date1">
			<row>
				<label>Google</label>
				<nb_uniq_visitors>11</nb_uniq_visitors>
				<nb_visits>11</nb_visits>
				<url>http://www.google.com</url>
				<logo>./plugins/Morpheus/icons/dist/searchEngines/www.google.com.png</logo>
			</row>
			<row>
				<label>Yahoo!</label>
				<nb_uniq_visitors>15</nb_uniq_visitors>
				<nb_visits>151</nb_visits>
				<url>http://www.yahoo.com</url>
				<logo>./plugins/Morpheus/icons/dist/searchEngines/www.yahoo.com.png</logo>
			</row>
		</result>
		<result testKey="date2">
			<row>
				<label>Google1©</label>
				<nb_uniq_visitors>110</nb_uniq_visitors>
				<nb_visits>110</nb_visits>
				<url>http://www.google.com1</url>
				<logo>./plugins/Morpheus/icons/dist/searchEngines/www.google.com.png1</logo>
			</row>
			<row>
				<label>Yahoo!1</label>
				<nb_uniq_visitors>150</nb_uniq_visitors>
				<nb_visits>1510</nb_visits>
				<url>http://www.yahoo.com1</url>
				<logo>./plugins/Morpheus/icons/dist/searchEngines/www.yahoo.com.png1</logo>
			</row>
		</result>
		<result testKey="date3" />
	</result>
</results>',
        ];

        yield 'render map of datatable maps with normal datatables without metadata' => [
            function () {
                return self::getDataTableMapContainsDataTableMapNormal();
            },
            '<?xml version="1.0" encoding="utf-8" ?>
<results>
	<result parentArrayKey="idSite">
		<result testKey="date1">
			<row>
				<label>Google</label>
				<nb_uniq_visitors>11</nb_uniq_visitors>
				<nb_visits>11</nb_visits>
			</row>
			<row>
				<label>Yahoo!</label>
				<nb_uniq_visitors>15</nb_uniq_visitors>
				<nb_visits>151</nb_visits>
			</row>
		</result>
		<result testKey="date2">
			<row>
				<label>Google1©</label>
				<nb_uniq_visitors>110</nb_uniq_visitors>
				<nb_visits>110</nb_visits>
			</row>
			<row>
				<label>Yahoo!1</label>
				<nb_uniq_visitors>150</nb_uniq_visitors>
				<nb_visits>1510</nb_visits>
			</row>
		</result>
		<result testKey="date3" />
	</result>
</results>',
            function ($renderer) {
                $renderer->setHideMetadataFromResponse(true);
            },
        ];

        yield 'render map of datatable maps with simple datatables' => [
            function () {
                return self::getDataTableMapContainsDataTableMapSimple();
            },
            '<?xml version="1.0" encoding="utf-8" ?>
<results>
	<result parentArrayKey="idSite">
		<result testKey="row1">
			<max_actions>14</max_actions>
			<nb_uniq_visitors>57</nb_uniq_visitors>
		</result>
		<result testKey="row2">
			<max_actions>140</max_actions>
			<nb_uniq_visitors>570</nb_uniq_visitors>
		</result>
		<result testKey="row3" />
	</result>
</results>',
        ];

        yield 'render map of datatable maps with datatables having one row only' => [
            function () {
                return self::getDataTableMapContainsDataTableMapSimpleOneRow();
            },
            '<?xml version="1.0" encoding="utf-8" ?>
<results>
	<result parentArrayKey="idSite">
		<result testKey="row1">14</result>
		<result testKey="row2">15</result>
		<result testKey="row3" />
	</result>
</results>',
        ];

        yield 'render empty array' => [
            function () {
                return [];
            },
            '<?xml version="1.0" encoding="utf-8" ?>
<result />',
        ];

        yield 'render value array' => [
            function () {
                return ['a', 'b', 'c'];
            },
            '<?xml version="1.0" encoding="utf-8" ?>
<result>
	<row>a</row>
	<row>b</row>
	<row>c</row>
</result>',
        ];

        yield 'render key / value array' => [
            function () {
                return ['a' => 'b', 'c' => 'd', 'e' => 'f', 5 => 'g'];
            },
            '<?xml version="1.0" encoding="utf-8" ?>
<result>
	<row>
		<a>b</a>
		<c>d</c>
		<e>f</e>
		<row key="5">g</row>
	</row>
</result>',
        ];

        yield 'render multi dimensional key / value array' => [
            function () {
                return ['a' => 'b', 'c' => [1, 2, 3, 4], 'e' => ['f' => 'g', 'h' => 'i', 'j' => 'k']];
            },
            '<?xml version="1.0" encoding="utf-8" ?>
<result>
	<a>b</a>
	<c>
		<row>1</row>
		<row>2</row>
		<row>3</row>
		<row>4</row>
	</c>
	<e>
		<f>g</f>
		<h>i</h>
		<j>k</j>
	</e>
</result>',
        ];

        yield 'render key / value array with one element' => [
            function () {
                return ['a' => 'b'];
            },
            '<?xml version="1.0" encoding="utf-8" ?>
<result>
	<row>
		<a>b</a>
	</row>
</result>',
        ];

        yield 'render simple datatable with invalid column characters correctly' => [
            function () {
                return self::getDataTableSimpleWithInvalidChars();
            },
            '<?xml version="1.0" encoding="utf-8" ?>
<result>
	<col name="$%@(%">1</col>
	<col name="avbs$">2</col>
	<col name="b/">2</col>
</result>',
        ];

        yield 'render datatable with invalid column characters correctly' => [
            function () {
                return self::getDataTableWithInvalidChars();
            },
            '<?xml version="1.0" encoding="utf-8" ?>
<result>
	<row>
		<col name="$%@(%">1</col>
		<col name="avbs$">2</col>
		<col name="b/">2</col>
	</row>
</result>',
        ];

        yield 'render key / value array with keys that cannot be xml tag names' => [
            function () {
                return [
                    'idgoal=1' => 5,
                    'a"><injected/><row b="' => 1,
                    'x"><injected/>' => 2,
                    'my key' => 3,
                ];
            },
            '<?xml version="1.0" encoding="utf-8" ?>
<result>
	<row>
		<row idgoal="1">5</row>
		<row key="a&quot;&gt;&lt;injected/&gt;&lt;row b=&quot;">1</row>
		<row key="x&quot;&gt;&lt;injected/&gt;">2</row>
		<row key="my key">3</row>
	</row>
</result>',
        ];

        yield 'render datatable with array column keys that cannot be xml tag names' => [
            function () {
                return self::getDataTableWithArrayColumnKeys();
            },
            '<?xml version="1.0" encoding="utf-8" ?>
<result>
	<row>
		<label>row1</label>
		<goals>
			<row idgoal=\'1\'>
				<revenue>5</revenue>
			</row>
		</goals>
	</row>
	<row>
		<label>row2</label>
		<goals>
			<row x=\'&#039; /&gt;&lt;injected/&gt;&lt;row y=&#039;\'>
				<revenue>7</revenue>
			</row>
		</goals>
	</row>
</result>',
        ];

        yield 'render key / value array with a key holding characters xml cannot hold' => [
            function () {
                return [
                    "a\x00b\x08c\x0bd\x0ce\x1ff" => 1,
                    "tab\there" => 2,
                ];
            },
            '<?xml version="1.0" encoding="utf-8" ?>
<result>
	<row>
		<row key="abcdef">1</row>
		<row key="tab&#9;here">2</row>
	</row>
</result>',
        ];

        yield 'render key / value array with an empty value for a key rendered as a row attribute' => [
            function () {
                return ['idgoal=1' => '', 'idgoal=2' => null];
            },
            '<?xml version="1.0" encoding="utf-8" ?>
<result>
	<row>
		<row idgoal="1"/>
		<row idgoal="2"/>
	</row>
</result>',
        ];

        yield 'render datatable map with a key name and labels that cannot be xml tag names' => [
            function () {
                return self::getDataTableMapWithInvalidKeys();
            },
            '<?xml version="1.0" encoding="utf-8" ?>
<results>
	<result key="b&quot;&gt;&lt;injected/&gt;&lt;result z=&quot;">
		<row>
			<label>row1</label>
			<nb_visits>1</nb_visits>
		</row>
	</result>
	<result key="c&quot;&gt;&lt;injected/&gt;" />
</results>',
        ];

        yield 'render datatable with column names that cannot be xml tag names' => [
            function () {
                return self::getDataTableWithInvalidColumnNames();
            },
            '<?xml version="1.0" encoding="utf-8" ?>
<result>
	<row>
		<label>row1</label>
		<goals>
			<row idgoal=\'1\'>
				<revenue>5</revenue>
			</row>
				<col name="a&quot;&gt;&lt;injected/&gt;&lt;x y=&quot;">7</col>
		</goals>
	</row>
	<row>
		<label>row2</label>
		<nb_visits>1</nb_visits>
	</row>
	<row>
		<label>row3</label>
		<col name="x&quot;&gt;&lt;injected/&gt;&lt;col name=&quot;">2</col>
	</row>
</result>',
        ];

        yield 'handles comparison metadata correctly' => [
            function () {
                return self::getComparisonDataTable();
            },
            '<?xml version="1.0" encoding="utf-8" ?>
<result>
	<row>
		<nb_visits>5</nb_visits>
		<nb_random>10</nb_random>
		<comparisons>
			<row>
				<nb_visits>6</nb_visits>
				<nb_random>7</nb_random>
			</row>
			<row>
				<nb_visits>8</nb_visits>
				<nb_random>9</nb_random>
			</row>
		</comparisons>
	</row>
</result>',
        ];

        yield 'handles comparison metadata correctly when metadata hidden' => [
            function () {
                return self::getComparisonDataTable();
            },
            '<?xml version="1.0" encoding="utf-8" ?>
<result>
	<row>
		<nb_visits>5</nb_visits>
		<nb_random>10</nb_random>
	</row>
</result>',
            function ($renderer) {
                $renderer->setHideMetadataFromResponse(true);
            },
        ];
    }

    /**
     * An array key is not always an identifier. It can originate from report data, so it must be
     * rendered as data and never as markup.
     *
     * @dataProvider getKeysThatCannotBeXmlTagNames
     */
    public function testRenderDoesNotAllowArrayKeysToBecomeMarkup(string $key)
    {
        $renderer = new Xml();
        $renderer->setTable([$key => 1, 'nested' => [$key => ['nb_visits' => 1]]]);

        $rendered = $renderer->render();

        self::assertStringNotContainsString('<injected', $rendered);
        $document = self::assertValidXml($rendered);

        // the key is still part of the response unchanged, either as the attribute name and value it
        // is made of or as the value of a generic `key` attribute
        $attributes = $document->row->attributes();
        self::assertCount(1, $attributes);

        $renderedKey = null;
        foreach ($attributes as $name => $value) {
            $renderedKey = 'key' === $name ? (string) $value : $name . '=' . $value;
        }

        self::assertSame($key, $renderedKey);
    }

    public function getKeysThatCannotBeXmlTagNames(): iterable
    {
        yield 'closes the row and adds an element' => ['a"><injected/><row b="'];
        yield 'closes a single quoted attribute' => ["a=' /><injected/><row b='"];
        yield 'adds an element without using an equals sign' => ['a"><injected/>'];
        yield 'adds an attribute to the row' => ['a onmouseover=alert(1)'];
        yield 'contains a line break' => ["a\n<injected/>"];
        yield 'starts with an equals sign' => ['="><injected/>'];
    }

    private static function assertValidXml(string $xml): \SimpleXMLElement
    {
        $useInternalErrors = libxml_use_internal_errors(true);
        $document = simplexml_load_string($xml);

        $errors = [];
        foreach (libxml_get_errors() as $error) {
            $errors[] = trim($error->message) . ' @' . $error->line . ':' . $error->column;
        }

        libxml_clear_errors();
        libxml_use_internal_errors($useInternalErrors);

        self::assertNotFalse($document, 'Rendered xml is not valid: ' . implode("\n", $errors) . "\n" . $xml);

        return $document;
    }

    private static function getDataTableWithArrayColumnKeys(): DataTable
    {
        $table = new DataTable();
        $table->addRowsFromArray([
            [DataTable\Row::COLUMNS => ['label' => 'row1', 'goals' => ['idgoal=1' => ['revenue' => 5]]]],
            [DataTable\Row::COLUMNS => ['label' => 'row2', 'goals' => ["x=' /><injected/><row y='" => ['revenue' => 7]]]],
        ]);
        return $table;
    }

    private static function getDataTableMapWithInvalidKeys(): DataTable\Map
    {
        $map = new DataTable\Map();
        $map->setKeyName('a"><injected/><x y="');

        $table = new DataTable();
        $table->addRowsFromArray([[DataTable\Row::COLUMNS => ['label' => 'row1', 'nb_visits' => 1]]]);

        $map->addTable($table, 'b"><injected/><result z="');
        $map->addTable(new DataTable(), 'c"><injected/>');

        return $map;
    }

    /**
     * The format used for a table is decided by the columns of its first row, so the first row here
     * has columns that can all be used as tag names while a later row has one that cannot.
     */
    private static function getDataTableWithInvalidColumnNames(): DataTable
    {
        $table = new DataTable();
        $table->addRowsFromArray([
            [DataTable\Row::COLUMNS => ['label' => 'row1', 'goals' => [
                'idgoal=1' => ['revenue' => 5],
                'a"><injected/><x y="' => 7,
            ]]],
            [DataTable\Row::COLUMNS => ['label' => 'row2', 'nb_visits' => 1]],
            [DataTable\Row::COLUMNS => ['label' => 'row3', 'x"><injected/><col name="' => 2]],
        ]);
        return $table;
    }

    private static function getDataTableSimpleWithInvalidChars(): DataTable\Simple
    {
        $table = new DataTable\Simple();
        $table->addRowsFromSimpleArray(
            array("$%@(%" => 1, "avbs$" => 2, "b/" => 2)
        );
        return $table;
    }

    private static function getDataTableWithInvalidChars(): DataTable
    {
        $table = new DataTable();
        $table->addRowsFromSimpleArray(
            array("$%@(%" => 1, "avbs$" => 2, "b/" => 2)
        );
        return $table;
    }
}
