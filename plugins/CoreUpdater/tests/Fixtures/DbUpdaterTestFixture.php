<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreUpdater\tests\Fixtures;

use Piwik\Db;
use Piwik\Tests\Fixtures\SqlDump;

class DbUpdaterTestFixture extends SqlDump
{
    public function performSetUp($setupEnvironmentOnly = false)
    {
        $this->dumpUrl = PIWIK_INCLUDE_PATH . "/tests/UI/resources/piwik1.0.sql.gz";
        $this->dropDatabaseInSetUp = true;
        $this->resetPersistedFixture = true;

        parent::performSetUp($setupEnvironmentOnly);
    }

    public function setUp(): void
    {
        $database = $this->getDbName();
        // change collation back to utf8, otherwise migrations before 4.x might fail
        Db::exec("ALTER DATABASE $database CHARACTER SET = utf8 COLLATE = utf8_unicode_ci;");

        parent::setUp();
    }
}
