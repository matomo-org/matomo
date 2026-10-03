<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreUpdater\tests\Integration\Commands;

use Piwik\DataAccess\ArchiveTableCreator;
use Piwik\Date;
use Piwik\Db;
use Piwik\DbHelper;
use Piwik\Filesystem;
use Piwik\Option;
use Piwik\Tests\Framework\TestCase\ConsoleCommandTestCase;
use Piwik\Updater;
use Piwik\Updates\Updates_2_10_0_b5;
use Piwik\Version;

require_once PIWIK_INCLUDE_PATH . '/core/Updates/2.10.0-b5.php';

/**
 * @group CoreUpdater
 */
class UpdateTest extends ConsoleCommandTestCase
{
    public const VERSION_TO_UPDATE_FROM = '2.9.0';
    public const EXPECTED_SQL_FROM_2_10 = "UPDATE report SET reports = REPLACE(reports, 'UserSettings_getBrowserVersion', 'DevicesDetection_getBrowserVersions');";

    private $oldScriptName = null;

    public function setUp(): void
    {
        parent::setUp();

        Option::set('version_core', self::VERSION_TO_UPDATE_FROM);

        $this->oldScriptName = $_SERVER['SCRIPT_NAME'];
        $_SERVER['SCRIPT_NAME'] = $_SERVER['SCRIPT_NAME'] . " console"; // update won't execute w/o this, see Common::isRunningConsoleCommand()

        ArchiveTableCreator::clear();
        DbHelper::getTablesInstalled($forceReload = true); // force reload so internal cache in Mysql.php is refreshed
        Updates_2_10_0_b5::$archiveBlobTables = null;
    }

    public function tearDown(): void
    {
        $_SERVER['SCRIPT_NAME'] = $this->oldScriptName;

        parent::tearDown();
    }

    public function testUpdateCommandSuccessfullyExecutesUpdate()
    {
        $result = $this->applicationTester->run(array(
            'command' => 'core:update',
            '--yes' => true,
        ));

        $this->assertEquals(0, $result, $this->getCommandDisplayOutputErrorMessage());

        $this->assertDryRunExecuted($this->applicationTester->getDisplay());

        // make sure update went through
        $this->assertEquals(Version::VERSION, Option::get('version_core'));
    }

    public function testUpdateCommandDoesntExecuteSqlWhenUserSaysNo()
    {
        $this->applicationTester->setInputs(['N']);

        $result = $this->applicationTester->run(array(
            'command' => 'core:update',
        ));

        $this->assertEquals(0, $result, $this->getCommandDisplayOutputErrorMessage());

        $this->assertDryRunExecuted($this->applicationTester->getDisplay());

        // make sure update did not go through
        $this->assertEquals(self::VERSION_TO_UPDATE_FROM, Option::get('version_core'));
    }

    public function testUpdateCommandDoesNotExecuteUpdateIfPiwikUpToDate()
    {
        Option::set('version_core', Version::VERSION);

        $result = $this->applicationTester->run(array(
            'command' => 'core:update',
            '--yes' => true,
        ));

        $this->assertEquals(0, $result, $this->getCommandDisplayOutputErrorMessage());

        // check no update occurred
        self::assertStringContainsString("Everything is already up to date.", $this->applicationTester->getDisplay());
        $this->assertEquals(Version::VERSION, Option::get('version_core'));
    }

    public function testUpdateCommandReturnsCorrectExitCodeWhenErrorOccurs()
    {
        // create a blob table, then drop it manually so update 2.10.0-b10 will fail
        $tableName = ArchiveTableCreator::getBlobTable(Date::factory('2015-01-01'), true);
        Db::exec("DROP TABLE $tableName");

        $result = $this->applicationTester->run(array(
            'command' => 'core:update',
            '--yes' => true,
        ));

        $this->assertEquals(1, $result, $this->getCommandDisplayOutputErrorMessage());
        self::assertStringContainsString("Matomo could not be updated! See above for more information.", $this->applicationTester->getDisplay());
    }

    public function testCoreUpdateExceptionIsTreatedAsError()
    {
        $updatesDir = PIWIK_USER_PATH . '/tmp/core-update-error-test-' . uniqid('', true);
        Filesystem::mkdir($updatesDir);

        try {
            $updateFile = $updatesDir . '/5.10.0-b999.php';
            file_put_contents($updateFile, <<<'PHP'
<?php

namespace Piwik\Updates;

use Piwik\Updater;
use Piwik\Updates;

class Updates_5_10_0_b999 extends Updates
{
    public function getMigrations(Updater $updater): array
    {
        return array();
    }

    public function doUpdate(Updater $updater): void
    {
        throw new \Exception('Simulated core update failure');
    }
}
PHP
            );

            Option::set('version_core', '5.9.0');

            $updater = new Updater($updatesDir . '/');
            $componentsWithUpdateFile = $updater->getComponentUpdates();
            $result = $updater->updateComponents($componentsWithUpdateFile);

            $this->assertTrue($result['coreError']);
            $this->assertNotEmpty($result['errors']);
            self::assertStringContainsString('Simulated core update failure', implode("\n", $result['errors']));
            $this->assertEquals('5.9.0', Option::get('version_core'));
        } finally {
            Filesystem::unlinkRecursive($updatesDir, true);
        }
    }

    private function assertDryRunExecuted($output)
    {
        self::assertStringContainsString("Note: this is a Dry Run", $output);
        self::assertStringContainsString(self::EXPECTED_SQL_FROM_2_10, $output);
    }
}
