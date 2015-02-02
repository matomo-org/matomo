<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Fixtures;

class UpdaterTestFixture extends SqlDump
{
    public function performSetUp($setupEnvironmentOnly = false)
    {
        $this->dumpUrl = PIWIK_INCLUDE_PATH . "/tests/PHPUnit/UI/resources/piwik1.0.sql.gz";
        $this->dropDatabaseInSetUp = true;
        $this->resetPersistedFixture = true;

        parent::performSetUp($setupEnvironmentOnly);
    }
}