<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CoreUpdater\tests\Fixtures;

use Piwik\Config;
use Piwik\Tests\Fixtures\SqlDump;

class DbUpdaterTestFixture extends SqlDump
{
    public function performSetUp($setupEnvironmentOnly = false)
    {
        $this->dumpUrl = PIWIK_INCLUDE_PATH . "/tests/UI/resources/piwik1.0.sql.gz";
        $this->dropDatabaseInSetUp = true;
        $this->resetPersistedFixture = true;

        Config::getInstance()->database['charset'] = 'utf8';
        Config::getInstance()->database_tests['charset'] = 'utf8';
        Config::getInstance()->forceSave();

        parent::performSetUp($setupEnvironmentOnly);
    }
}
