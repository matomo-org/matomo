<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration\DataAccess;

use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\DataAccess\ArchiveWriter;
use Piwik\Date;
use Piwik\Db;
use Piwik\Period\Factory;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Piwik\DataAccess\Model;

/**
 * @group Core
 * @group DataAccess
 */
class ModelTest extends IntegrationTestCase
{
    /**
     * @var Model
     */
    private $model;
    private $tableName = 'archive_numeric_test';

    public function setUp(): void
    {
        parent::setUp();

        $this->model = new Model();
        $this->model->createArchiveTable($this->tableName, 'archive_numeric');
    }

    public function test_insertNewArchiveId()
    {
        $this->assertAllocatedArchiveId(1);
        $this->assertAllocatedArchiveId(2);
        $this->assertAllocatedArchiveId(3);
        $this->assertAllocatedArchiveId(4);
        $this->assertAllocatedArchiveId(5);
        $this->assertAllocatedArchiveId(6);
    }

    private function assertAllocatedArchiveId($expectedId)
    {
        $id = $this->model->allocateNewArchiveId($this->tableName);

        $this->assertEquals($expectedId, $id);
    }

    public function test_getInvalidatedArchiveIdsAsOldOrOlderThan_getsCorrectArchiveIds()
    {
        $this->insertArchiveData([
            ['date1' => '2015-02-12', 'date2' => '2015-02-12', 'period' => 3, 'name' => 'done', 'value' => ArchiveWriter::DONE_OK],
            ['date1' => '2015-02-01', 'date2' => '2015-02-01', 'period' => 1, 'name' => 'done', 'value' => ArchiveWriter::DONE_INVALIDATED],
            ['date1' => '2015-02-12', 'date2' => '2015-02-12', 'period' => 1, 'name' => 'done', 'value' => ArchiveWriter::DONE_INVALIDATED],
            ['date1' => '2015-02-12', 'date2' => '2015-02-12', 'period' => 1, 'name' => 'done', 'value' => ArchiveWriter::DONE_INVALIDATED],
            ['date1' => '2015-02-12', 'date2' => '2015-02-12', 'period' => 1, 'name' => 'done', 'value' => ArchiveWriter::DONE_OK],
        ]);

        $idArchives = $this->model->getInvalidatedArchiveIdsAsOldOrOlderThan([
            'idarchive' => 7,
            'idsite' => 1,
            'date1' => '2015-02-12',
            'date2' => '2015-02-12',
            'period' => 1,
            'name' => 'done',
        ]);

        $this->assertEquals([3, 5, 6], $idArchives);
    }

    /**
     * @dataProvider getTestDataForHasChildArchivesInPeriod
     */
    public function test_hasChildArchivesInPeriod_returnsFalseIfThereIsNoChildPeriod($archivesToInsert, $idSite, $date, $period, $expected)
    {
        $this->insertArchiveData($archivesToInsert);

        $periodObj = Factory::build($period, $date);
        $result = $this->model->hasChildArchivesInPeriod($idSite, $periodObj);
        $this->assertEquals($expected, $result);
    }

    public function getTestDataForHasChildArchivesInPeriod()
    {
        return [
            // day period, no child
            [
                [
                    ['date1' => '2015-02-01', 'date2' => '2015-02-28', 'period' => 3, 'name' => 'done', 'value' => 1],
                ],
                1,
                '2015-02-03',
                'day',
                false,
            ],

            // week period, no child
            [
                [
                    ['date1' => '2015-02-01', 'date2' => '2015-02-28', 'period' => 3, 'name' => 'done', 'value' => 1],
                ],
                1,
                '2015-02-03',
                'week',
                false,
            ],

            // month period, no child
            [
                [
                    ['date1' => '2015-01-31', 'date2' => '2015-01-31', 'period' => 1, 'name' => 'done', 'value' => 1],
                    ['date1' => '2015-01-01', 'date2' => '2015-12-31', 'period' => 4, 'name' => 'done', 'value' => 1],
                ],
                1,
                '2015-02-04',
                'month',
                false,
            ],

            // year period, no child
            [
                [],
                1,
                '2015-02-03',
                'year',
                false,
            ],

            // week period, w/ child
            [
                [
                    ['date1' => '2015-02-01', 'date2' => '2015-02-28', 'period' => 3, 'name' => 'done', 'value' => 1],
                    ['date1' => '2015-01-31', 'date2' => '2015-01-31', 'period' => 1, 'name' => 'done', 'value' => 1],
                ],
                1,
                '2015-02-01',
                'week',
                true,
            ],
            [
                [
                    ['date1' => '2015-02-01', 'date2' => '2015-02-28', 'period' => 3, 'name' => 'done', 'value' => 1],
                    ['date1' => '2015-02-11', 'date2' => '2015-02-11', 'period' => 1, 'name' => 'done', 'value' => 1],
                ],
                1,
                '2015-02-10',
                'week',
                true,
            ],

            // month period, w/ child
            [
                [
                    ['date1' => '2015-02-09', 'date2' => '2015-02-15', 'period' => 2, 'name' => 'done', 'value' => 1],
                ],
                1,
                '2015-02-10',
                'month',
                true,
            ],
            [
                [
                    ['date1' => '2015-02-09', 'date2' => '2015-02-09', 'period' => 2, 'name' => 'done', 'value' => 1],
                ],
                1,
                '2015-02-10',
                'month',
                true,
            ],
            [
                [
                    ['date1' => '2015-02-01', 'date2' => '2015-02-01', 'period' => 2, 'name' => 'done', 'value' => 1],
                ],
                1,
                '2015-02-10',
                'month',
                true,
            ],

            // year period, w/ child
            [
                [
                    ['date1' => '2015-02-01', 'date2' => '2015-02-28', 'period' => 3, 'name' => 'done', 'value' => 1],
                ],
                1,
                '2015-02-04',
                'year',
                true,
            ],
            [
                [
                    ['date1' => '2015-04-01', 'date2' => '2015-04-01', 'period' => 1, 'name' => 'done', 'value' => 1],
                ],
                1,
                '2015-02-04',
                'year',
                true,
            ],

            // range period w/ day child
            [
                [
                    ['date1' => '2015-04-01', 'date2' => '2015-04-01', 'period' => 1, 'name' => 'done', 'value' => 1],
                ],
                1,
                '2015-03-30,2015-04-05',
                'range',
                true,
            ],
            [
                [
                    ['date1' => '2015-04-01', 'date2' => '2015-04-01', 'period' => 1, 'name' => 'done', 'value' => 1],
                ],
                1,
                '2015-04-01,2015-04-05',
                'range',
                true,
            ],
        ];
    }

    private function insertArchiveData($archivesToInsert)
    {
        $idarchive = 1;
        foreach ($archivesToInsert as $archive) {
            $table = ArchiveTableCreator::getNumericTable(Date::factory($archive['date1']));
            $sql = "INSERT INTO `$table` (idarchive, idsite, date1, date2, period, `name`, `value`) VALUES (?, ?, ?, ?, ?, ?, ?)";
            Db::query($sql, [$idarchive, 1, $archive['date1'], $archive['date2'], $archive['period'], $archive['name'], $archive['value']]);

            ++$idarchive;
        }
    }
}
