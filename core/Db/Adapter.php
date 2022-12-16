<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Db;

use Zend_Db_Table;

/**
 * Base class for all Matomo database adapters.
 *
 * Should only contain code that will apply for all database types, code specific to one database should be placed in
 * that adapter's class in \Db\Adapter\Pdo\[dbclass]
 */
class Adapter
{
    /**
     * Create adapter
     *
     * @param string $adapterName database adapter name
     * @param array $dbInfos database connection info
     * @param bool $connect
     *
     * @return AdapterInterface
     */
    public static function factory(string $adapterName, array &$dbInfos, bool $connect = true): AdapterInterface
    {
        if ($connect) {
            if (isset($dbInfos['port']) && is_string($dbInfos['port']) && $dbInfos['port'][0] === '/') {
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

        // make sure not to pass any references otherwise they will modify $dbInfos
        $infos = array();
        foreach ($dbInfos as $key => $val) {
            $infos[$key] = $val;
        }

        $adapter = new $className($infos);

        if ($connect) {
            try {
                $adapter->getConnection();

                Zend_Db_Table::setDefaultAdapter($adapter);
                // we don't want the connection information to appear in the logs
                $adapter->resetConfig();
            } catch(\Exception $e) {
                // we don't want certain exceptions to leak information
                $msg = $adapter::overriddenExceptionMessage($e->getMessage());
                if ('' !== $msg) {
                    throw new \Exception($msg);
                }

                throw $e;
            }
        }

        return $adapter;
    }

    /**
     * Get adapter class name
     *
     * @param string $adapterName
     * @return string
     * @throws \Exception
     */
    private static function getAdapterClassName(string $adapterName): string
    {
        $className = 'Piwik\Db\Adapter\\' . str_replace(' ', '\\', ucwords(str_replace(array('_', '\\'), ' ', strtolower($adapterName))));
        if (!class_exists($className)) {
            throw new \Exception(sprintf("Adapter '%s' is not valid. Maybe check that your Matomo configuration files in config/*.ini.php are readable by the webserver.", $adapterName));
        }
        return $className;
    }

    /**
     * Get default port for named adapter
     *
     * @param string $adapterName
     *
     * @return int
     */
    public static function getDefaultPortForAdapter(string $adapterName): int
    {
        $className = self::getAdapterClassName($adapterName);
        return (int) call_user_func(array($className, 'getDefaultPort'));
    }

    /**
     * Get list of adapters
     *
     * @return array
     */
    public static function getAdapters(): array
    {
        static $adapterNames = [
            // currently supported by Piwik
            'Pdo\Mysql',
            'Mysqli',
            // other adapters supported by Zend_Db
        ];

        $adapters = array();

        foreach ($adapterNames as $adapterName) {
            $className = '\Piwik\Db\Adapter\\' . $adapterName;
            if (call_user_func(array($className, 'isEnabled'))) {
                $adapters[strtoupper($adapterName)] = call_user_func(array($className, 'getDefaultPort'));
            }
        }

        return $adapters;
    }

    /**
     * Checks if the adapter is recommended by Matomo or not.
     *
     * @param string $adapterName
     *
     * @return bool
     */
    public static function isRecommendedAdapter(string $adapterName): bool
    {
        return call_user_func([self::getAdapterClassName($adapterName), 'isRecommendedAdapter']);
    }

}
