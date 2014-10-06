<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DBStats;

use Piwik\Piwik;
use Piwik\Plugins\CoreVisualizations\Visualizations\Graph;
use Piwik\Plugins\CoreVisualizations\Visualizations\HtmlTable;

class DBStats extends \Piwik\Plugin
{
    const TIME_OF_LAST_TASK_RUN_OPTION = 'dbstats_time_of_last_cache_task_run';

    /**
     * @see Piwik\Plugin::getListHooksRegistered
     */
    public function getListHooksRegistered()
    {
        return array(
            'AssetManager.getStylesheetFiles' => 'getStylesheetFiles',
            "TestingEnvironment.addHooks"     => 'setupTestEnvironment'
        );
    }

    public function getStylesheetFiles(&$stylesheets)
    {
        $stylesheets[] = "plugins/DBStats/stylesheets/dbStatsTable.less";
    }

    public function setupTestEnvironment($environment)
    {
        Piwik::addAction("MySQLMetadataProvider.createDao", function (&$dao) {
            require_once dirname(__FILE__) . "/tests/Mocks/MockDataAccess.php";
            $dao = new Mocks\MockDataAccess();
        });
    }
}
