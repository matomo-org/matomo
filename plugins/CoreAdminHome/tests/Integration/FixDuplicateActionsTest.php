<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreAdminHome\tests\Integration;

use Piwik\Common;
use Piwik\Console;
use Piwik\Db;
use Piwik\Plugins\CoreAdminHome\tests\Fixture\DuplicateActions;
use Piwik\Tests\Framework\TestCase\IntegrationTestCase;
use Symfony\Component\Console\Tester\ApplicationTester;

/**
 * @group Core
 */
class FixDuplicateActionsTest extends IntegrationTestCase
{
    /**
     * @var DuplicateActions
     */
    public static $fixture = null;

    /**
     * @var ApplicationTester
     */
    protected $applicationTester = null;

    public function setUp(): void
    {
        parent::setUp();

        $application = new Console();
        $application->setAutoExit(false);

        $this->applicationTester = new ApplicationTester($application);
    }

    public function testFixDuplicateLogActionsCorrectlyRemovesDuplicatesAndFixesReferencesInOtherTables()
    {
        $result = $this->applicationTester->run(array(
            'command' => 'core:fix-duplicate-log-actions',
            '--invalidate-archives' => 0,
            '-vvv' => false
        ));

        $this->assertEquals(0, $result, "Command failed: " . $this->applicationTester->getDisplay());

        $this->assertDuplicateActionsRemovedFromLogActionTable();
        $this->assertDuplicatesFixedInLogLinkVisitActionTable();
        $this->assertDuplicatesFixedInLogConversionTable();
        $this->assertDuplicatesFixedInLogConversionItemTable();

        self::assertStringContainsString("Found and deleted 7 duplicate action entries", $this->applicationTester->getDisplay());

        $expectedAffectedArchives = array(
            array('idsite' => '1', 'server_time' => '2012-01-01'),
            array('idsite' => '2', 'server_time' => '2013-01-01'),
            array('idsite' => '1', 'server_time' => '2012-02-01'),
            array('idsite' => '2', 'server_time' => '2012-01-09'),
            array('idsite' => '2', 'server_time' => '2012-03-01'),
        );
        foreach ($expectedAffectedArchives as $archive) {
            self::assertStringContainsString("[ idSite = {$archive['idsite']}, date = {$archive['server_time']} ]", $this->applicationTester->getDisplay());
        }
    }

    private function assertDuplicateActionsRemovedFromLogActionTable()
    {
        $actions = Db::fetchAll("SELECT idaction, name FROM " . Common::prefixTable('log_action'));
        $expectedActions = array(
            array('idaction' => 1, 'name' => 'action1'),
            array('idaction' => 4, 'name' => 'action2'),
            array('idaction' => 5, 'name' => 'ACTION2'),
            array('idaction' => 6, 'name' => 'action4'),
            array('idaction' => 8, 'name' => 'action5'),
        );
        $this->assertEquals($expectedActions, $actions);
    }

    private function assertDuplicatesFixedInLogLinkVisitActionTable()
    {
        $columns = array(
            'idaction_url_ref',
            'idaction_name_ref',
            'idaction_name',
            'idaction_url',
            'idaction_event_action',
            'idaction_event_category',
            'idaction_content_interaction',
            'idaction_content_name',
            'idaction_content_piece',
            'idaction_content_target'
        );
        $rows = Db::fetchAll("SELECT " . implode(',', $columns) . " FROM " . Common::prefixTable('log_link_visit_action'));
        $expectedRows = array(
            array(
                'idaction_url_ref' => '1',
                'idaction_name_ref' => '1',
                'idaction_name' => '1',
                'idaction_url' => '4',
                'idaction_event_action' => '5',
                'idaction_event_category' => '6',
                'idaction_content_interaction' => '5',
                'idaction_content_name' => '8',
                'idaction_content_piece' => '4',
                'idaction_content_target' => '6'
            ),
            array(
                'idaction_url_ref' => '1',
                'idaction_name_ref' => '1',
                'idaction_name' => '5',
                'idaction_url' => '5',
                'idaction_event_action' => '4',
                'idaction_event_category' => '6',
                'idaction_content_interaction' => '5',
                'idaction_content_name' => '5',
                'idaction_content_piece' => '6',
                'idaction_content_target' => '6'
            )
        );
        $this->assertEquals($expectedRows, $rows);
    }

    private function assertDuplicatesFixedInLogConversionTable()
    {
        $rows = Db::fetchAll("SELECT idaction_url FROM " . Common::prefixTable('log_conversion'));
        $expectedRows = array(
            array('idaction_url' => 4),
            array('idaction_url' => 5)
        );
        $this->assertEquals($expectedRows, $rows);
    }

    private function assertDuplicatesFixedInLogConversionItemTable()
    {
        $columns = array(
            'idaction_sku',
            'idaction_name',
            'idaction_category',
            'idaction_category2',
            'idaction_category3',
            'idaction_category4',
            'idaction_category5'
        );
        $rows = Db::fetchAll("SELECT " . implode(',', $columns) . " FROM " . Common::prefixTable('log_conversion_item'));
        $expectedRows = array(
            array(
                'idaction_sku' => '1',
                'idaction_name' => '1',
                'idaction_category' => '1',
                'idaction_category2' => '4',
                'idaction_category3' => '5',
                'idaction_category4' => '6',
                'idaction_category5' => '5'
            ),
            array(
                'idaction_sku' => '1',
                'idaction_name' => '1',
                'idaction_category' => '5',
                'idaction_category2' => '5',
                'idaction_category3' => '8',
                'idaction_category4' => '4',
                'idaction_category5' => '6'
            )
        );
        $this->assertEquals($expectedRows, $rows);
    }
}

FixDuplicateActionsTest::$fixture = new DuplicateActions();
