<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\DataAccess;

use Piwik\DataAccess\ArchiveTableCreator;

/**
 * @group Core
 */
class ArchiveTableCreatorTest extends \PHPUnit\Framework\TestCase
{
    private $tables;

    public function setUp(): void
    {
        parent::setUp();

        $this->tables = array(
            'archive_numeric_2015_02',
            'archive_blob_2015_05',
            'archive_numeric_2014_03',
            'archive_blob_2015_01',
            'archive_blob_2015_02',
        );
    }

    public function tearDown(): void
    {
        ArchiveTableCreator::clear();

        parent::tearDown();
    }

    /**
     * @dataProvider getTestDataForGetTablesArchivesInstalled
     */
    public function testGetTablesArchivesInstalledCorrectlyFiltersTableNames($type, $expectedTables)
    {
        ArchiveTableCreator::$tablesAlreadyInstalled = $this->tables;

        $tables = ArchiveTableCreator::getTablesArchivesInstalled($type);

        $this->assertEquals($expectedTables, $tables);
    }

    public function getTestDataForGetTablesArchivesInstalled(): array
    {
        return [
            [
                ArchiveTableCreator::BLOB_TABLE,
                [
                    'archive_blob_2015_05',
                    'archive_blob_2015_01',
                    'archive_blob_2015_02',
                ],
            ],

            [
                ArchiveTableCreator::NUMERIC_TABLE,
                [
                    'archive_numeric_2015_02',
                    'archive_numeric_2014_03',
                ],
            ],

            [
                'qewroufsjdlf',
                [],
            ],

            [
                '',
                [
                    'archive_numeric_2015_02',
                    'archive_blob_2015_05',
                    'archive_numeric_2014_03',
                    'archive_blob_2015_01',
                    'archive_blob_2015_02',
                ],
            ],

            [
                null,
                [
                    'archive_numeric_2015_02',
                    'archive_blob_2015_05',
                    'archive_numeric_2014_03',
                    'archive_blob_2015_01',
                    'archive_blob_2015_02',
                ],
            ],
        ];
    }

    /**
     * @dataProvider getTestDataForGetLatestArchiveTableInstalled
     */
    public function testGetLatestArchiveTableInstalled($type, $expectedLatestTable)
    {
        ArchiveTableCreator::$tablesAlreadyInstalled = $this->tables;

        $latestTable = ArchiveTableCreator::getLatestArchiveTableInstalled($type);

        $this->assertEquals($expectedLatestTable, $latestTable);
    }

    public function getTestDataForGetLatestArchiveTableInstalled(): array
    {
        return [
            [ArchiveTableCreator::BLOB_TABLE, 'archive_blob_2015_05'],
            [ArchiveTableCreator::NUMERIC_TABLE, 'archive_numeric_2015_02'],
            ['qewroufsjdlf', ''],
            ['', 'archive_blob_2015_05'],
            [null, 'archive_blob_2015_05'],
        ];
    }
}
