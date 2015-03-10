<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Integration;

use Piwik\CronArchive;
use Piwik\Archive\ArchiveInvalidator;
use Piwik\Date;
use Piwik\Db;
use Piwik\Plugins\CoreAdminHome\tests\Framework\Mock\API;
use Piwik\Tests\Framework\Fixture;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;

class TestCronArchive extends CronArchive
{

    protected function checkPiwikUrlIsValid()
    {

    }

    protected function initPiwikHost($piwikUrl = false)
    {

    }
}

/**
 * @group Archiver
 * @group CronArchive
 */
class CronArchiveTest extends IntegrationTestCase
{
    public function setUp()
    {
        parent::setUp();

        Fixture::createWebsite('2014-12-12 00:01:02');
        Fixture::createWebsite('2014-12-12 00:01:02');
    }

    public function test_getColumnNamesFromTable()
    {
        $ar = new ArchiveInvalidator();
        $ar->rememberToInvalidateArchivedReportsLater(1, Date::factory('2014-04-05'));
        $ar->rememberToInvalidateArchivedReportsLater(2, Date::factory('2014-04-05'));
        $ar->rememberToInvalidateArchivedReportsLater(2, Date::factory('2014-04-06'));

        $api = API::getInstance();

        ob_start();
        $cronarchive = new TestCronArchive(Fixture::getRootUrl() . 'tests/PHPUnit/proxy/index.php');
        $cronarchive->setApiToInvalidateArchivedReport($api);
        $cronarchive->init();
        ob_end_clean();

        $expectedInvalidations = array(
            array(array(1,2), '2014-04-05'),
            array(array(2), '2014-04-06')
        );

        $this->assertEquals($expectedInvalidations, $api->getInvalidatedReports());
    }
}
