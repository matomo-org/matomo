<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreUpdater\Test\Fixtures;

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
}
