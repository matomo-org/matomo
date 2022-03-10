<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DBStats;

use Piwik\Piwik;
use Piwik\Plugins\DBStats\tests\Mocks\MockDataAccess;

class DBStats extends \Piwik\Plugin
{
    const TIME_OF_LAST_TASK_RUN_OPTION = 'dbstats_time_of_last_cache_task_run';

    /**
     * @see \Piwik\Plugin::registerEvents
     */
    public function registerEvents()
    {
        return array(
            "TestingEnvironment.addHooks" => 'setupTestEnvironment',
            'AssetManager.getStylesheetFiles'        => 'getStylesheetFiles',
        );
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = 'plugins/DBStats/stylesheets/dbstats.less';
    }

    public function setupTestEnvironment($environment)
    {
        Piwik::addAction("MySQLMetadataProvider.createDao", function (&$dao) {
            $dao = new MockDataAccess();
        });
    }
}
