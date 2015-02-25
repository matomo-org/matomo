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
use Piwik\Exception\DatabaseSchemaIsNewerThanCodebaseException;

/**
 * Load and execute all relevant, incremental update scripts for Piwik core and plugins, and bump the component version numbers for completed updates.
 *
 */
class Updater
{
    const INDEX_CURRENT_VERSION = 0;
    const INDEX_NEW_VERSION = 1;

    private $pathUpdateFileCore;
    private $pathUpdateFilePlugins;
    private $hasMajorDbUpdate = false;
    private $updatedClasses = array();
    private $componentsWithNewVersion = array();
    private $componentsWithUpdateFile = array();

    /**
     * TODO
     */
    private static $activeInstance;

    /**
     * Constructor
     * TODO
     * @param string|null $pathUpdateFileCore
     * @param string|null $pathUpdateFilePlugins
     */
    public function __construct($pathUpdateFileCore = null, $pathUpdateFilePlugins = null)
    {
        $this->pathUpdateFileCore = $pathUpdateFileCore ?: PIWIK_INCLUDE_PATH . '/core/Updates/';
        $this->pathUpdateFilePlugins = $pathUpdateFilePlugins ?: PIWIK_INCLUDE_PATH . '/plugins/%s/Updates/';

        self::$activeInstance = $this;
    }

    /**
     * TODO
     */
    public function markComponentSuccessfullyUpdated($name, $version)
    {
        try {
            Option::set(self::getNameInOptionTable($name), $version, $autoLoad = 1);
        } catch (\Exception $e) {
            // case when the option table is not yet created (before 0.2.10)
        }
    }

    /**
     * TODO
     */
    public function getCurrentComponentVersion($name)
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
     * This method ensures that Piwik Platform cannot be running when using a NEWER database
     */
    public function throwIfPiwikVersionIsOlderThanDBSchema()
    {
        $dbSchemaVersion = $this->getCurrentRecordedComponentVersion('core');
        $current = Version::VERSION;
        if(-1 === version_compare($current, $dbSchemaVersion)) {
            $messages = array(
                Piwik::translate('General_ExceptionDatabaseVersionNewerThanCodebase', array($current, $dbSchemaVersion)),
                Piwik::translate('General_ExceptionDatabaseVersionNewerThanCodebaseWait'),
                // we cannot fill in the Super User emails as we are failing before Authentication was ready
                Piwik::translate('General_ExceptionContactSupportGeneric', array('', ''))
            );
            throw new DatabaseSchemaIsNewerThanCodebaseException(implode(" ", $messages));
        }
    }


    /**
     * Returns a list of components (core | plugin) that need to run through the upgrade process.
     *
     * TODO: modify
     *
     * @return array( componentName => array( file1 => version1, [...]), [...])
     */
    public function getComponentsWithUpdateFile($componentsToCheck)
    {
        $this->componentsWithNewVersion = $this->getComponentsWithNewVersion($componentsToCheck);
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
        return isset($this->componentsWithNewVersion[$componentName]);
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

                $queriesForComponent = call_user_func(array($className, 'getSql'), $this);
                foreach ($queriesForComponent as $query => $error) {
                    $queries[] = $query . ';';
                }
                $this->hasMajorDbUpdate = $this->hasMajorDbUpdate || call_user_func(array($className, 'isMajorUpdate'));
            }
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
                    call_user_func(array($className, 'update'), $this);
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
     * TODO modify
     *
     * @throws \Exception
     * @return array array( componentName => array( oldVersion, newVersion), [...])
     */
    public function getComponentsWithNewVersion($componentsToCheck)
    {
        $componentsToUpdate = array();

        // we make sure core updates are processed before any plugin updates
        if (isset($componentsToCheck['core'])) {
            $coreVersions = $componentsToCheck['core'];
            unset($componentsToCheck['core']);
            $componentsToCheck = array_merge(array('core' => $coreVersions), $componentsToCheck);
        }

        $recordedCoreVersion = self::getCurrentRecordedComponentVersion('core');
        if (empty($recordedCoreVersion)) {
            // This should not happen
            $recordedCoreVersion = Version::VERSION;
            self::recordComponentSuccessfullyUpdated('core', $recordedCoreVersion);
        }

        foreach ($componentsToCheck as $name => $version) {
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
     * TODO
     *
     * @param $componentsWithUpdateFile
     * @return array
     */
    public function updateComponents($componentsWithUpdateFile)
    {
        $warnings = array();
        $errors   = array();
        $deactivatedPlugins = array();
        $coreError = false;

        if (!empty($componentsWithUpdateFile)) {
            $currentAccess      = Access::getInstance();
            $hasSuperUserAccess = $currentAccess->hasSuperUserAccess();

            if (!$hasSuperUserAccess) {
                $currentAccess->setSuperUserAccess(true);
            }

            // if error in any core update, show message + help message + EXIT
            // if errors in any plugins updates, show them on screen, disable plugins that errored + CONTINUE
            // if warning in any core update or in any plugins update, show message + CONTINUE
            // if no error or warning, success message + CONTINUE
            foreach ($componentsWithUpdateFile as $name => $filenames) {
                try {
                    $warnings = array_merge($warnings, $this->update($name));
                } catch (UpdaterErrorException $e) {
                    $errors[] = $e->getMessage();
                    if ($name == 'core') {
                        $coreError = true;
                        break;
                    } elseif (\Piwik\Plugin\Manager::getInstance()->isPluginActivated($name)) {
                        \Piwik\Plugin\Manager::getInstance()->deactivatePlugin($name);
                        $deactivatedPlugins[] = $name;
                    }
                }
            }

            if (!$hasSuperUserAccess) {
                $currentAccess->setSuperUserAccess(false);
            }
        }

        Filesystem::deleteAllCacheOnUpdate();

        $result = array(
            'warnings'  => $warnings,
            'errors'    => $errors,
            'coreError' => $coreError,
            'deactivatedPlugins' => $deactivatedPlugins
        );

        /**
         * Triggered after Piwik has been updated.
         */
        Piwik::postEvent('CoreUpdater.update.end');

        return $result;
    }

    /**
     * TODO
     */
    public function getComponentUpdates()
    {
        $componentsToCheck = array(
            'core' => Version::VERSION
        );

        $manager = \Piwik\Plugin\Manager::getInstance();
        $plugins = $manager->getLoadedPlugins();
        foreach ($plugins as $pluginName => $plugin) {
            if ($manager->isPluginInstalled($pluginName)) {
                $componentsToCheck[$pluginName] = $plugin->getVersion();
            }
        }

        $columnsVersions = ColumnUpdater::getAllVersions();
        foreach ($columnsVersions as $component => $version) {
            $componentsToCheck[$component] = $version;
        }

        $componentsWithUpdateFile = $this->getComponentsWithUpdateFile($componentsToCheck);

        if (count($componentsWithUpdateFile) == 0) {
            ColumnUpdater::onNoUpdateAvailable($columnsVersions);

            if (!$this->hasNewVersion('core')) {
                return null;
            }
        }

        return $componentsWithUpdateFile;
    }

    /**
     * TODO
     */
    public function executeMigrationQueries($file, $migrationQueries)
    {
        foreach ($migrationQueries as $update => $ignoreError) {
            self::$activeInstance->executeSingleMigrationQuery($update, $ignoreError, $file);
        }
    }

    /**
     * TODO
     */
    public function executeSingleMigrationQuery($migrationQuerySql, $errorToIgnore, $file)
    {
        try {
            Db::exec($migrationQuerySql);
        } catch (\Exception $e) {
            $this->handleUpdateQueryError($e, $migrationQuerySql, $errorToIgnore, $file);
        }
    }

    /**
     * TODO
     */
    public function handleUpdateQueryError(\Exception $e, $updateSql, $errorToIgnore, $file)
    {
        if (($errorToIgnore === false)
            || !self::isDbErrorOneOf($e, $errorToIgnore)
        ) {
            $message = $file . ":\nError trying to execute the query '" . $updateSql . "'.\nThe error was: " . $e->getMessage();
            throw new UpdaterErrorException($message);
        }
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
        self::$activeInstance->executeMigrationQueries($file, $sqlarray);
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
        self::$activeInstance->executeSingleMigrationQuery($updateSql, $errorToIgnore, $file);
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
        self::$activeInstance->handleQueryError($e, $updateSql, $errorToIgnore, $file);
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

    /**
     * Record version of successfully completed component update
     *
     * TODO: deprecate this and other static functions? need to pass an Updater to Updates.php descendants
     *
     * @param string $name
     * @param string $version
     */
    public static function recordComponentSuccessfullyUpdated($name, $version)
    {
        self::$activeInstance->markComponentSuccessfullyUpdated($name, $version);
    }

    /**
     * Retrieve the current version of a recorded component
     * @param string $name
     * @return false|string
     * @throws \Exception
     */
    public static function getCurrentRecordedComponentVersion($name)
    {
        return self::$activeInstance->getCurrentComponentVersion($name);
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
}

/**
 * Exception thrown by updater if a non-recoverable error occurs
 *
 */
class UpdaterErrorException extends \Exception
{
}
