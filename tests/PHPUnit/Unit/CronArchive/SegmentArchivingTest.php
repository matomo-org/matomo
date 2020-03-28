<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Tests\Unit\CronArchive;

use Piwik\Config;
use Piwik\Date;
use Piwik\CronArchive\SegmentArchiving;
use Piwik\Site;

/**
 * @group Core
 */
class SegmentArchivingTest extends \PHPUnit\Framework\TestCase
{
    const TEST_NOW = '2015-03-01';

    private $mockSegmentEntries;

    public function setUp(): void
    {
        Config::getInstance()->General['enabled_periods_API'] = 'day,week,month,year,range';

        Site::setSites([
            1 => [
                'idsite' => 1,
                'ts_created' => '2013-03-03 00:00:00',
            ],
        ]);

        $this->mockSegmentEntries = array(
            array(
                'ts_created' => '2014-01-01',
                'definition' => 'browserName==FF',
                'enable_only_idsite' => 1,
                'ts_last_edit' => '2014-05-05 00:22:33',
                'auto_archive' => 1,
            ),

            array(
                'ts_created' => '2014-01-01',
                'definition' => 'countryCode==us',
                'enable_only_idsite' => 1,
                'ts_last_edit' => '2014-02-02 00:33:44',
                'auto_archive' => 1,
            ),

            array(
                'ts_created' => '2012-01-01',
                'definition' => 'countryCode==us',
                'enable_only_idsite' => 1,
                'ts_last_edit' => '2014-02-03',
                'auto_archive' => 1,
            ),

            array(
                'ts_created' => '2014-01-01',
                'definition' => 'countryCode==ca',
                'enable_only_idsite' => 2,
                'ts_last_edit' => '2013-01-01',
                'auto_archive' => 1,
            ),

            array(
                'ts_created' => '2012-01-01',
                'definition' => 'countryCode==ca',
                'enable_only_idsite' => 2,
                'ts_last_edit' => '2011-01-01',
                'auto_archive' => 1,
            ),

            array(
                'ts_created' => '2011-01-01',
                'definition' => 'countryCode==ca',
                'enable_only_idsite' => 0,
                'ts_last_edit' => null,
                'auto_archive' => 1,
            ),

            array(
                'ts_created' => '2015-03-01',
                'definition' => 'pageUrl==a',
                'enable_only_idsite' => 1,
                'ts_last_edit' => '2014-01-01',
                'auto_archive' => 1,
            ),

            array(
                'ts_created' => '2015-02-01',
                'definition' => 'pageUrl==b',
                'enable_only_idsite' => 1,
                'ts_last_edit' => null,
                'auto_archive' => 1,
            ),
        );
    }

    // TODO: need to rewrite tests for this class after refactoring

    private function createUrlProviderToTest($processNewSegmentsFrom)
    {
        $mockSegmentEditorModel = $this->createPartialMock('Piwik\Plugins\SegmentEditor\Model', array('getAllSegmentsAndIgnoreVisibility'));
        $mockSegmentEditorModel->expects($this->any())->method('getAllSegmentsAndIgnoreVisibility')->will($this->returnValue($this->mockSegmentEntries));

        return new SegmentArchiving($processNewSegmentsFrom, $beginningOfTimeLastN = 7, $mockSegmentEditorModel, null, Date::factory(self::TEST_NOW));
    }
}