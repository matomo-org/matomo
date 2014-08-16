<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Db;

use Piwik\Config;
use Piwik\Piwik;
use Piwik\SettingsServer;
use Piwik\Tracker;

/**
 * This class creates Db objects.
 */
class DbFactory
{
    /**
     * @param array|null $config Optional parameter to override the default config
     *
     * @return Db
     */
    public function createDb(array $config = null)
    {
        return new Db($this->createConnection($config));
    }

    private function createConnection(array $config = null)
    {
        if (SettingsServer::isTrackerApiRequest()) {
            try {
                return Tracker::getDatabase();
            } catch (\Exception $e) {
                // Proceed to create the standard Db object
            }
        }

        $config = self::getDbConfig($config);

        return @Adapter::factory($config['adapter'], $config);
    }

    public static function getDbConfig(array $dbConfig = null)
    {
        $config = Config::getInstance();

        if (is_null($dbConfig)) {
            $dbConfig = $config->database;
        }

        /**
         * Triggered before a database connection is established.
         *
         * This event can be used to change the settings used to establish a connection.
         *
         * @param array *$dbInfos Reference to an array containing database connection info,
         *                        including:
         *
         *                        - **host**: The host name or IP address to the MySQL database.
         *                        - **username**: The username to use when connecting to the
         *                                        database.
         *                        - **password**: The password to use when connecting to the
         *                                       database.
         *                        - **dbname**: The name of the Piwik MySQL database.
         *                        - **port**: The MySQL database port to use.
         *                        - **adapter**: either `'PDO\MYSQL'` or `'MYSQLI'`
         *                        - **type**: The MySQL engine to use, for instance 'InnoDB'
         */
        Piwik::postEvent('Db.getDatabaseConfig', array(&$dbConfig));

        $dbConfig['profiler'] = $config->Debug['enable_sql_profiler'];

        return $dbConfig;
    }
}
