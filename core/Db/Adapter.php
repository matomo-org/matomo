<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik
 */

/**
 * @package Piwik
 * @subpackage Piwik_Db
 */
class Piwik_Db_Adapter
{
    /**
     * Create adapter
     *
     * @param string $adapterName database adapter name
     * @param array $dbInfos database connection info
     * @param bool $connect
     * @return Piwik_Db_Adapter_Interface
     */
    public static function factory($adapterName, & $dbInfos, $connect = true)
    {
        if ($connect) {
            if ($dbInfos['port'][0] == '/') {
                $dbInfos['unix_socket'] = $dbInfos['port'];
                unset($dbInfos['host']);
                unset($dbInfos['port']);
            }

            // not used by Zend Framework
            unset($dbInfos['tables_prefix']);
            unset($dbInfos['adapter']);
            unset($dbInfos['schema']);
        }

        $className = self::getAdapterClassName($adapterName);
        Piwik_Loader::loadClass($className);

        /*
         * 5.2.1 fixes various bugs with references that caused PDO_MYSQL getConnection()
         * to clobber $dbInfos. (#33282, #35106, #39944)
         */
        if (version_compare(PHP_VERSION, '5.2.1') < 0) {
            $adapter = new $className(array_map('trim', $dbInfos));
        } else {
            $adapter = new $className($dbInfos);
        }

        if ($connect) {
            $adapter->getConnection();

            Zend_Db_Table::setDefaultAdapter($adapter);
            // we don't want the connection information to appear in the logs
            $adapter->resetConfig();
        }

        return $adapter;
    }

    /**
     * Get adapter class name
     *
     * @param string $adapterName
     * @return string
     */
    private static function getAdapterClassName($adapterName)
    {
        return 'Piwik_Db_Adapter_' . str_replace(' ', '_', ucwords(str_replace('_', ' ', strtolower($adapterName))));
    }

    /**
     * Get default port for named adapter
     *
     * @param string $adapterName
     * @return int
     */
    public static function getDefaultPortForAdapter($adapterName)
    {
        $className = self::getAdapterClassName($adapterName);
        return call_user_func(array($className, 'getDefaultPort'));
    }

    /**
     * Get list of adapters
     *
     * @return array
     */
    public static function getAdapters()
    {
        static $adapterNames = array(
            // currently supported by Piwik
            'Pdo_Mysql',
            'Mysqli',

            // other adapters supported by Zend_Db
//			'Pdo_Pgsql',
//			'Pdo_Mssql',
//			'Sqlsrv',
//			'Pdo_Ibm',
//			'Db2',
//			'Pdo_Oci',
//			'Oracle',
        );

        $adapters = array();

        foreach ($adapterNames as $adapterName) {
            $className = 'Piwik_Db_Adapter_' . $adapterName;
            if (call_user_func(array($className, 'isEnabled'))) {
                $adapters[strtoupper($adapterName)] = call_user_func(array($className, 'getDefaultPort'));
            }
        }

        return $adapters;
    }
}
