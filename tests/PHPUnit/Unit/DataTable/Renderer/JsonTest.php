<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\DataTable\Renderer;

use Piwik\DataTable\Renderer\Json;

/**
 * @group DataTableTest
 */
class JsonTest extends RendererTestCase
{
    /**
     * @dataProvider getTestCases
     */
    public function testRender($table, string $expected, callable $rendererCallback = null)
    {
        $renderer = new Json();

        if (is_callable($rendererCallback)) {
            $rendererCallback($renderer);
        }

        $renderer->setTable($table);
        $renderer->setRenderSubTables(true);
        $rendered = $renderer->render();
        $this->assertEquals($expected, $rendered);
    }

    public function getTestCases(): iterable
    {
        yield 'render normal datatable' => [
            self::getDataTable(),
            '[{"label":"Google\u00a9","bool":false,"goals":{"idgoal=1":{"revenue":5.5,"nb_conversions":10}},"nb_uniq_visitors":11,"nb_visits":11,"nb_actions":17,"max_actions":"5","sum_visit_length":517,"bounce_count":9,"url":"http:\/\/www.google.com\/display\"and,properly","logo":".\/plugins\/Morpheus\/icons\/dist\/searchEngines\/www.google.com.png"},{"label":"Yahoo!","nb_uniq_visitors":15,"bool":true,"nb_visits":151,"nb_actions":147,"max_actions":"50","sum_visit_length":517,"bounce_count":90,"url":"http:\/\/www.yahoo.com","logo":".\/plugins\/Morpheus\/icons\/dist\/searchEngines\/www.yahoo.com.png","idsubdatatable":2,"subtable":[{"label":"sub1","count":1,"bool":false},{"label":"sub2","count":2,"bool":true}]}]',
        ];

        yield 'render normal datatable without metadata' => [
            self::getDataTable(),
            '[{"label":"Google\u00a9","bool":false,"goals":{"idgoal=1":{"revenue":5.5,"nb_conversions":10}},"nb_uniq_visitors":11,"nb_visits":11,"nb_actions":17,"max_actions":"5","sum_visit_length":517,"bounce_count":9},{"label":"Yahoo!","nb_uniq_visitors":15,"bool":true,"nb_visits":151,"nb_actions":147,"max_actions":"50","sum_visit_length":517,"bounce_count":90,"subtable":[{"label":"sub1","count":1,"bool":false},{"label":"sub2","count":2,"bool":true}]}]',
            function ($renderer) {
                $renderer->setHideMetadataFromResponse(true);
            },
        ];

        yield 'render simple datatable' => [
            self::getDataTableSimple(),
            '{"max_actions":14,"nb_uniq_visitors":57,"nb_visits":66,"nb_actions":151,"sum_visit_length":5118,"bounce_count":44}',
        ];

        yield 'render simple datatable with one row' => [
            self::getDataTableSimpleOneRow(),
            '{"value":14}',
        ];

        yield 'render simple datatable with one row having a zero value' => [
            self::getDataTableSimpleOneZeroRow(),
            '{"value":0}',
        ];

        yield 'render simple datatable with one row having a false value' => [
            self::getDataTableSimpleOneFalseRow(),
            '{"value":false}',
        ];

        yield 'render empty datatable' => [
            self::getDataTableEmpty(),
            '[]',
        ];

        yield 'render datatable with array in row metadata' => [
            self::getDataTableHavingAnArrayInRowMetadata(),
            // array in row metadata is rendered
            '[{"label":"sub1","count":1},{"label":"sub2","count":2,"test":"render"},{"label":"sub3","count":2,"test":"renderMe","testArray":"ignore"},{"label":"sub4","count":6,"testArray":["do not render"]},{"label":"sub5","count":2,"testArray":"do ignore","mymeta":"should be rendered"},{"label":"sub6","count":3,"mymeta":"renderrrrrr"}]',
        ];

        yield 'render datatable map' => [
            self::getDataTableMap(),
            '{"date1":[{"label":"Google","nb_uniq_visitors":11,"nb_visits":11,"url":"http:\/\/www.google.com","logo":".\/plugins\/Morpheus\/icons\/dist\/searchEngines\/www.google.com.png"},{"label":"Yahoo!","nb_uniq_visitors":15,"nb_visits":151,"url":"http:\/\/www.yahoo.com","logo":".\/plugins\/Morpheus\/icons\/dist\/searchEngines\/www.yahoo.com.png"}],"date2":[{"label":"Google1\u00a9","nb_uniq_visitors":110,"nb_visits":110,"url":"http:\/\/www.google.com1","logo":".\/plugins\/Morpheus\/icons\/dist\/searchEngines\/www.google.com.png1"},{"label":"Yahoo!1","nb_uniq_visitors":150,"nb_visits":1510,"url":"http:\/\/www.yahoo.com1","logo":".\/plugins\/Morpheus\/icons\/dist\/searchEngines\/www.yahoo.com.png1"}],"date3":[]}',
        ];

        yield 'render datatable map without metadata' => [
            self::getDataTableMap(),
            '{"date1":[{"label":"Google","nb_uniq_visitors":11,"nb_visits":11},{"label":"Yahoo!","nb_uniq_visitors":15,"nb_visits":151}],"date2":[{"label":"Google1\u00a9","nb_uniq_visitors":110,"nb_visits":110},{"label":"Yahoo!1","nb_uniq_visitors":150,"nb_visits":1510}],"date3":[]}',
            function ($renderer) {
                $renderer->setHideMetadataFromResponse(true);
            },
        ];

        yield 'render simple datatable map' => [
            self::getDataTableSimpleMap(),
            '{"row1":{"max_actions":14,"nb_uniq_visitors":57},"row2":{"max_actions":140,"nb_uniq_visitors":570},"row3":[]}',
        ];

        yield 'render datatable map holding simple tables with one row only' => [
            self::getDataTableSimpleOneRowMap(),
            '{"row1":14,"row2":15,"row3":[]}',
        ];

        yield 'render map of datatable maps with normal datatables' => [
            self::getDataTableMapContainsDataTableMapNormal(),
            '{"idSite":{"date1":[{"label":"Google","nb_uniq_visitors":11,"nb_visits":11,"url":"http:\/\/www.google.com","logo":".\/plugins\/Morpheus\/icons\/dist\/searchEngines\/www.google.com.png"},{"label":"Yahoo!","nb_uniq_visitors":15,"nb_visits":151,"url":"http:\/\/www.yahoo.com","logo":".\/plugins\/Morpheus\/icons\/dist\/searchEngines\/www.yahoo.com.png"}],"date2":[{"label":"Google1\u00a9","nb_uniq_visitors":110,"nb_visits":110,"url":"http:\/\/www.google.com1","logo":".\/plugins\/Morpheus\/icons\/dist\/searchEngines\/www.google.com.png1"},{"label":"Yahoo!1","nb_uniq_visitors":150,"nb_visits":1510,"url":"http:\/\/www.yahoo.com1","logo":".\/plugins\/Morpheus\/icons\/dist\/searchEngines\/www.yahoo.com.png1"}],"date3":[]}}',
        ];

        yield 'render map of datatable maps with normal datatables without metadata' => [
            self::getDataTableMapContainsDataTableMapNormal(),
            '{"idSite":{"date1":[{"label":"Google","nb_uniq_visitors":11,"nb_visits":11},{"label":"Yahoo!","nb_uniq_visitors":15,"nb_visits":151}],"date2":[{"label":"Google1\u00a9","nb_uniq_visitors":110,"nb_visits":110},{"label":"Yahoo!1","nb_uniq_visitors":150,"nb_visits":1510}],"date3":[]}}',
            function ($renderer) {
                $renderer->setHideMetadataFromResponse(true);
            },
        ];

        yield 'render map of datatable maps with simple datatables' => [
            self::getDataTableMapContainsDataTableMapSimple(),
            '{"idSite":{"row1":{"max_actions":14,"nb_uniq_visitors":57},"row2":{"max_actions":140,"nb_uniq_visitors":570},"row3":[]}}',
        ];

        yield 'render map of datatable maps with datatables having one row only' => [
            self::getDataTableMapContainsDataTableMapSimpleOneRow(),
            '{"idSite":{"row1":14,"row2":15,"row3":[]}}',
        ];

        yield 'render empty array' => [
            [],
            '[]',
        ];

        yield 'render value array' => [
            ['a', 'b', 'c'],
            '["a","b","c"]',
        ];

        yield 'render key / value array' => [
            ['a' => 'b', 'c' => 'd', 'e' => 'f', 5 => 'g'],
            '[{"a":"b","c":"d","e":"f","5":"g"}]',
        ];

        yield 'render multi dimensional key / value array' => [
            ['a' => 'b', 'c' => [1, 2, 3, 4], 'e' => ['f' => 'g', 'h' => 'i', 'j' => 'k']],
            '{"a":"b","c":[1,2,3,4],"e":{"f":"g","h":"i","j":"k"}}',
        ];

        yield 'render key / value array with one element' => [
            ['a' => 'b'],
            '[{"a":"b"}]',
        ];

        yield 'handles comparison metadata correctly' => [
            self::getComparisonDataTable(),
            '[{"nb_visits":5,"nb_random":10,"comparisons":[{"nb_visits":6,"nb_random":7},{"nb_visits":8,"nb_random":9}]}]',
        ];

        yield 'handles comparison metadata correctly when metadata hidden' => [
            self::getComparisonDataTable(),
            '[{"nb_visits":5,"nb_random":10}]',
            function ($renderer) {
                $renderer->setHideMetadataFromResponse(true);
            },
        ];
    }
}
