<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\UserId;

use Piwik\API\Request;
use Piwik\Db;

/**
 * Plugin adds a new Users report showing all unique user IDs and some aggregated data
 */
class UserId extends \Piwik\Plugin
{
    /**
     * Register event observers
     *
     * @return array
     */
    public function registerEvents()
    {
        return array(
            // Run user IDs reindex after archiving cron is finished
            'CronArchive.end' => 'runReindexCron',
            // Add plugin's custom JS files
            'AssetManager.getJavaScriptFiles' => 'getJavaScriptFiles',
            // Add translations for the client side JS
            'Translate.getClientSideTranslationKeys' => 'getClientSideTranslationKeys'
        );
    }

    /**
     * Event observer. Listens to the CronArchive.end event and runs user IDs reindex after
     * archiving cron is finished
     */
    public function runReindexCron()
    {
        Request::processRequest('UserId.reindex');
    }

    /**
     * Plugin installation. Creates a DB table holding indexed user IDs and some aggregated data
     *
     * @throws \Exception
     */
    public function install()
    {
        parent::install();

        $model = new Model();
        try {
            $sql = "CREATE TABLE {$model->getUserIdsTable()} (
                        user_id VARCHAR(200) NOT NULL,
                        idsite INT UNSIGNED NOT NULL,
                        last_visit_id INT UNSIGNED NOT NULL,
                        first_visit_time DATETIME NOT NULL,
                        last_visit_time DATETIME NOT NULL,
                        total_visits INT UNSIGNED NOT NULL,
                        total_actions INT UNSIGNED NOT NULL,
                        total_searches INT UNSIGNED NOT NULL,
                        total_events INT UNSIGNED NOT NULL,
                        idvisitor binary(8) NOT NULL,
                        PRIMARY KEY (idsite, user_id),
                        KEY last_visit_id (last_visit_id)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            Db::exec($sql);
        } catch (Exception $e) {
            // ignore error if table already exists (1050 code is for 'table already exists')
            if (!Db::get()->isErrNo($e, '1050')) {
                throw $e;
            }
        }
    }

    /**
     * Plugin un-installation. Removes the created user_ids DB table
     */
    public function uninstall()
    {
        parent::uninstall();
        $model = new Model();
        Db::dropTables($model->getUserIdsTable());
    }

    /**
     * Add a custom JS to the page. It adds possibility to open visitor details popover for each
     * user ID in a report table
     *
     * @param $jsFiles
     */
    public function getJavaScriptFiles(&$jsFiles)
    {
        $jsFiles[] = "plugins/UserId/javascripts/rowaction.js";
    }

    /**
     * Add translations for the client side JS
     *
     * @param $translationKeys
     */
    public function getClientSideTranslationKeys(&$translationKeys)
    {
        $translationKeys[] = "Live_ViewVisitorProfile";
    }
}
