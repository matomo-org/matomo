<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik;
use Piwik\Columns\Updater as ColumnUpdater;

/**
 * Load and execute all relevant, incremental update scripts for Piwik core and plugins, and bump the component version numbers for completed updates.
 *
 */
class Updater
{
    const INDEX_CURRENT_VERSION = 0;
    const INDEX_NEW_VERSION = 1;

    public $pathUpdateFileCore;
    public $pathUpdateFilePlugins;
    private $componentsToCheck = array();
    private $hasMajorDbUpdate = false;
    private $updatedClasses = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->pathUpdateFileCore = PIWIK_INCLUDE_PATH . '/core/Updates/';
        $this->pathUpdateFilePlugins = PIWIK_INCLUDE_PATH . '/plugins/%s/Updates/';

        ColumnUpdater::setUpdater($this);
    }

    /**
     * Add component to check
     *
     * @param string $name
     * @param string $version
     */
    public function addComponentToCheck($name, $version)
    {
        $this->componentsToCheck[$name] = $version;
    }

    /**
     * Record version of successfully completed component update
     *
     * @param string $name
     * @param string $version
     */
    public static function recordComponentSuccessfullyUpdated($name, $version)
    {
        try {
            Option::set(self::getNameInOptionTable($name), $version, $autoLoad = 1);
        } catch (\Exception $e) {
            // case when the option table is not yet created (before 0.2.10)
        }
    }

    /**
     * Retrieve the current version of a recorded component
     * @param string $name
     * @return false|string
     * @throws \Exception
     */
    public static function getCurrentRecordedComponentVersion($name)
    {
        try {
            $currentVersion = Option::get(self::getNameInOptionTable($name));
        } catch (\Exception $e) {
            // mysql error 1146: table doesn't exist
            if (Db::get()->isErrNo($e, '1146')) {
                // case when the option table is not yet created (before 0.2.10)
                $currentVersion = false;
            } else {
                // failed for some other reason
                throw $e;
            }
        }

        return $currentVersion;
    }

    /**
     * Returns the flag name to use in the option table to record current schema version
     * @param string $name
     * @return string
     */
    private static function getNameInOptionTable($name)
    {
        return 'version_' . $name;
    }

    /**
     * Returns a list of components (core | plugin) that need to run through the upgrade process.
     *
     * @return array( componentName => array( file1 => version1, [...]), [...])
     */
    public function getComponentsWithUpdateFile()
    {
        $this->componentsWithNewVersion = $this->getComponentsWithNewVersion();
        $this->componentsWithUpdateFile = $this->loadComponentsWithUpdateFile();
        return $this->componentsWithUpdateFile;
    }

    /**
     * Component has a new version?
     *
     * @param string $componentName
     * @return bool TRUE if compoment is to be updated; FALSE if not
     */
    public function hasNewVersion($componentName)
    {
        return isset($this->componentsWithNewVersion) &&
        isset($this->componentsWithNewVersion[$componentName]);
    }

    /**
     * Does one of the new versions involve a major database update?
     * Note: getSqlQueriesToExecute() must be called before this method!
     *
     * @return bool
     */
    public function hasMajorDbUpdate()
    {
        return $this->hasMajorDbUpdate;
    }

    /**
     * Returns the list of SQL queries that would be executed during the update
     *
     * @return array of SQL queries
     * @throws \Exception
     */
    public function getSqlQueriesToExecute()
    {
        $queries = array();
        $classNames = array();

        foreach ($this->componentsWithUpdateFile as $componentName => $componentUpdateInfo) {
            foreach ($componentUpdateInfo as $file => $fileVersion) {
                require_once $file; // prefixed by PIWIK_INCLUDE_PATH

                $className = $this->getUpdateClassName($componentName, $fileVersion);
                if (!class_exists($className, false)) {
                    throw new \Exception("The class $className was not found in $file");
                }

                if (in_array($className, $classNames)) {
                    continue; // prevent from getting updates from Piwik\Columns\Updater multiple times
                }

                $classNames[] = $className;

                $queriesForComponent = call_user_func(array($className, 'getSql'));
                foreach ($queriesForComponent as $query => $error) {
                    $queries[] = $query . ';';
                }
                $this->hasMajorDbUpdate = $this->hasMajorDbUpdate || call_user_func(array($className, 'isMajorUpdate'));
            }
            // unfortunately had to extract this query from the Option class
            $queries[] = 'UPDATE `' . Common::prefixTable('option') . '` '.
    				'SET option_value = \'' . $fileVersion . '\' '.
    				'WHERE option_name = \'' . self::getNameInOptionTable($componentName) . '\';';
        }
        return $queries;
    }

    public function getUpdateClassName($componentName, $fileVersion)
    {
        $suffix = strtolower(str_replace(array('-', '.'), '_', $fileVersion));
        $className = 'Updates_' . $suffix;

        if ($componentName == 'core') {
            return '\\Piwik\\Updates\\' . $className;
        }

        if (ColumnUpdater::isDimensionComponent($componentName)) {
            return '\\Piwik\\Columns\\Updater';
        }

        return '\\Piwik\\Plugins\\' . $componentName . '\\' . $className;
    }

    /**
     * Update the named component
     *
     * @param string $componentName 'core', or plugin name
     * @throws \Exception|UpdaterErrorException
     * @return array of warning strings if applicable
     */
    public function update($componentName)
    {
        $warningMessages = array();

        foreach ($this->componentsWithUpdateFile[$componentName] as $file => $fileVersion) {
            try {
                require_once $file; // prefixed by PIWIK_INCLUDE_PATH

                $className = $this->getUpdateClassName($componentName, $fileVersion);
                if (!in_array($className, $this->updatedClasses) && class_exists($className, false)) {
                    // update()
                    call_user_func(array($className, 'update'));
                    // makes sure to call Piwik\Columns\Updater only once as one call updates all dimensions at the same
                    // time for better performance
                    $this->updatedClasses[] = $className;
                }

                self::recordComponentSuccessfullyUpdated($componentName, $fileVersion);
            } catch (UpdaterErrorException $e) {
                throw $e;
            } catch (\Exception $e) {
                $warningMessages[] = $e->getMessage();
            }
        }

        // to debug, create core/Updates/X.php, update the core/Version.php, throw an Exception in the try, and comment the following line
        self::recordComponentSuccessfullyUpdated($componentName, $this->componentsWithNewVersion[$componentName][self::INDEX_NEW_VERSION]);
        return $warningMessages;
    }

    /**
     * Construct list of update files for the outdated components
     *
     * @return array( componentName => array( file1 => version1, [...]), [...])
     */
    private function loadComponentsWithUpdateFile()
    {
        $componentsWithUpdateFile = array();

        foreach ($this->componentsWithNewVersion as $name => $versions) {
            $currentVersion = $versions[self::INDEX_CURRENT_VERSION];
            $newVersion = $versions[self::INDEX_NEW_VERSION];

            if ($name == 'core') {
                $pathToUpdates = $this->pathUpdateFileCore . '*.php';
            } elseif (ColumnUpdater::isDimensionComponent($name)) {
                $componentsWithUpdateFile[$name][PIWIK_INCLUDE_PATH . '/core/Columns/Updater.php'] = $newVersion;
            } else {
                $pathToUpdates = sprintf($this->pathUpdateFilePlugins, $name) . '*.php';
            }

            if (!empty($pathToUpdates)) {
                $files = _glob($pathToUpdates);
                if ($files == false) {
                    $files = array();
                }

                foreach ($files as $file) {
                    $fileVersion = basename($file, '.php');
                    if ( // if the update is from a newer version
                        version_compare($currentVersion, $fileVersion) == -1
                        // but we don't execute updates from non existing future releases
                        && version_compare($fileVersion, $newVersion) <= 0
                    ) {
                        $componentsWithUpdateFile[$name][$file] = $fileVersion;
                    }
                }
            }

            if (isset($componentsWithUpdateFile[$name])) {
                // order the update files by version asc
                uasort($componentsWithUpdateFile[$name], "version_compare");
            } else {
                // there are no update file => nothing to do, update to the new version is successful
                self::recordComponentSuccessfullyUpdated($name, $newVersion);
            }
        }

        return $componentsWithUpdateFile;
    }

    /**
     * Construct list of outdated components
     *
     * @throws \Exception
     * @return array array( componentName => array( oldVersion, newVersion), [...])
     */
    public function getComponentsWithNewVersion()
    {
        $componentsToUpdate = array();

        // we make sure core updates are processed before any plugin updates
        if (isset($this->componentsToCheck['core'])) {
            $coreVersions = $this->componentsToCheck['core'];
            unset($this->componentsToCheck['core']);
            $this->componentsToCheck = array_merge(array('core' => $coreVersions), $this->componentsToCheck);
        }

        $recordedCoreVersion = self::getCurrentRecordedComponentVersion('core');
        if ($recordedCoreVersion === false) {
            // This should not happen
            $recordedCoreVersion = Version::VERSION;
            self::recordComponentSuccessfullyUpdated('core', $recordedCoreVersion);
        }

        foreach ($this->componentsToCheck as $name => $version) {
            $currentVersion = self::getCurrentRecordedComponentVersion($name);

            if (ColumnUpdater::isDimensionComponent($name)) {
                $isComponentOutdated = $currentVersion !== $version;
            } else {
                // note: when versionCompare == 1, the version in the DB is newer, we choose to ignore
                $isComponentOutdated = version_compare($currentVersion, $version) == -1;
            }

            if ($isComponentOutdated || $currentVersion === false) {
                $componentsToUpdate[$name] = array(
                    self::INDEX_CURRENT_VERSION => $currentVersion,
                    self::INDEX_NEW_VERSION     => $version
                );
            }
        }
        return $componentsToUpdate;
    }

    /**
     * Performs database update(s)
     *
     * @param string $file Update script filename
     * @param array $sqlarray An array of SQL queries to be executed
     * @throws UpdaterErrorException
     */
    static function updateDatabase($file, $sqlarray)
    {
        foreach ($sqlarray as $update => $ignoreError) {
            self::executeMigrationQuery($update, $ignoreError, $file);
        }
    }

    /**
     * Executes a database update query.
     *
     * @param string $updateSql Update SQL query.
     * @param int|false $errorToIgnore A MySQL error code to ignore.
     * @param string $file The Update file that's calling this method.
     */
    public static function executeMigrationQuery($updateSql, $errorToIgnore, $file)
    {
        try {
            Db::exec($updateSql);
        } catch (\Exception $e) {
            self::handleQueryError($e, $updateSql, $errorToIgnore, $file);
        }
    }

    /**
     * Handle an error that is thrown from a database query.
     *
     * @param \Exception $e the exception thrown.
     * @param string $updateSql Update SQL query.
     * @param int|false $errorToIgnore A MySQL error code to ignore.
     * @param string $file The Update file that's calling this method.
     * @throws UpdaterErrorException
     */
    public static function handleQueryError($e, $updateSql, $errorToIgnore, $file)
    {
        if (($errorToIgnore === false)
            || !self::isDbErrorOneOf($e, $errorToIgnore)
        ) {
            $message = $file . ":\nError trying to execute the query '" . $updateSql . "'.\nThe error was: " . $e->getMessage();
            throw new UpdaterErrorException($message);
        }
    }

    /**
     * Returns whether an exception is a DB error with a code in the $errorCodesToIgnore list.
     *
     * @param int $error
     * @param int|int[] $errorCodesToIgnore
     * @return boolean
     */
    public static function isDbErrorOneOf($error, $errorCodesToIgnore)
    {
        $errorCodesToIgnore = is_array($errorCodesToIgnore) ? $errorCodesToIgnore : array($errorCodesToIgnore);
        foreach ($errorCodesToIgnore as $code) {
            if (Db::get()->isErrNo($error, $code)) {
                return true;
            }
        }
        return false;
    }
}

/**
 * Exception thrown by updater if a non-recoverable error occurs
 *
 */
class UpdaterErrorException extends \Exception
{
}
