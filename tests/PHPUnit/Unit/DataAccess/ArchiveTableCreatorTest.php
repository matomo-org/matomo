<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Tests\Unit\DataAccess;

use Piwik\DataAccess\ArchiveTableCreator;

/**
 * @group Core
 */
class ArchiveTableCreatorTest extends \PHPUnit_Framework_TestCase
{
    private $tables;

    public function setUp()
    {
        parent::setUp();

        $this->tables = array(
            'archive_numeric_2015_02',
            'archive_blob_2015_05',
            'garbage',
            'archive_numeric_2014_03',
            'archive_blob_2015_01',
            'archive_blob_2015_02',
            'aslkdfjsd',
            'prefixed_archive_numeric_2012_01',
        );
    }

    public function tearDown()
    {
        ArchiveTableCreator::clear();

        parent::tearDown();
    }

    /**
     * @dataProvider getTestDataForGetTablesArchivesInstalled
     */
    public function test_getTablesArchivesInstalled_CorrectlyFiltersTableNames($type, $expectedTables)
    {
        ArchiveTableCreator::$tablesAlreadyInstalled = $this->tables;

        $tables = ArchiveTableCreator::getTablesArchivesInstalled($type);

        $this->assertEquals($expectedTables, $tables);
    }

    public function getTestDataForGetTablesArchivesInstalled()
    {
        return array(
            array(
                ArchiveTableCreator::BLOB_TABLE,
                array(
                    'archive_blob_2015_05',
                    'archive_blob_2015_01',
                    'archive_blob_2015_02',
                ),
            ),

            array(
                ArchiveTableCreator::NUMERIC_TABLE,
                array(
                    'archive_numeric_2015_02',
                    'archive_numeric_2014_03',
                    'prefixed_archive_numeric_2012_01',
                ),
            ),

            array(
                'qewroufsjdlf',
                array(),
            ),

            array(
                '',
                array(
                    'archive_numeric_2015_02',
                    'archive_blob_2015_05',
                    'archive_numeric_2014_03',
                    'archive_blob_2015_01',
                    'archive_blob_2015_02',
                    'prefixed_archive_numeric_2012_01',
                ),
            ),

            array(
                null,
                array(
                    'archive_numeric_2015_02',
                    'archive_blob_2015_05',
                    'archive_numeric_2014_03',
                    'archive_blob_2015_01',
                    'archive_blob_2015_02',
                    'prefixed_archive_numeric_2012_01',
                ),
            ),
        );
    }
}