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
use Piwik\Container\StaticContainer;
use Piwik\Updater\UpdateObserver;
use Zend_Db_Exception;

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
     * @var UpdateObserver[]
     */
    private $updateObservers = array();

    /**
     * @var Columns\Updater
     */
    private $columnsUpdater;

    /**
     * Currently used Updater instance, set on construction. This instance is used to provide backwards
     * compatibility w/ old code that may use the deprecated static methods in Updates.
     *
     * @var Updater
     */
    private static $activeInstance;

    /**
     * Constructor.
     *
     * @param string|null $pathUpdateFileCore The path to core Update files.
     * @param string|null $pathUpdateFilePlugins The path to plugin update files. Should contain a `'%s'` placeholder
     *                                           for the plugin name.
     * @param Columns\Updater|null $columnsUpdater The dimensions updater instance.
     */
    public function __construct($pathUpdateFileCore = null, $pathUpdateFilePlugins = null, Columns\Updater $columnsUpdater = null)
    {
        $this->pathUpdateFileCore = $pathUpdateFileCore ?: PIWIK_INCLUDE_PATH . '/core/Updates/';
        $this->pathUpdateFilePlugins = $pathUpdateFilePlugins ?: PIWIK_INCLUDE_PATH . '/plugins/%s/Updates/';
        $this->columnsUpdater = $columnsUpdater ?: new Columns\Updater();

        self::$activeInstance = $this;
    }

    /**
     * Adds an UpdateObserver to the internal list of listeners.
     *
     * @param UpdateObserver $listener
     */
    public function addUpdateObserver(UpdateObserver $listener)
    {
        $this->updateObservers[] = $listener;
    }

    /**
     * Marks a component as successfully updated to a specific version in the database. Sets an option
     * that looks like `"version_$componentName"`.
     *
     * @param string $name The component name. Eg, a plugin name, `'core'` or dimension column name.
     * @param string $version The component version (should use semantic versioning).
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
     * Marks a component as successfully uninstalled. Deletes an option
     * that looks like `"version_$componentName"`.
     *
     * @param string $name The component name. Eg, a plugin name, `'core'` or dimension column name.
     */
    public function markComponentSuccessfullyUninstalled($name)
    {
        try {
            Option::delete(self::getNameInOptionTable($name));
        } catch (\Exception $e) {
            // case when the option table is not yet created (before 0.2.10)
        }
    }

    /**
     * Returns the currently installed version of a Piwik component.
     *
     * @param string $name The component name. Eg, a plugin name, `'core'` or dimension column name.
     * @return string A semantic version.
     * @throws \Exception
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
     * Returns a list of components (core | plugin) that need to run through the upgrade process.
     *
     * @param string[] $componentsToCheck An array mapping component names to the latest locally available version.
     *                                    If the version is later than the currently installed version, the component
     *                                    must be upgraded.
     *
     *                                    Example: `array('core' => '2.11.0')`
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

                $update = StaticContainer::getContainer()->make($className);
                $queriesForComponent = call_user_func(array($update, 'getMigrationQueries'), $this);
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

        $this->executeListenerHook('onComponentUpdateStarting', array($componentName));

        foreach ($this->componentsWithUpdateFile[$componentName] as $file => $fileVersion) {
            try {
                require_once $file; // prefixed by PIWIK_INCLUDE_PATH

                $className = $this->getUpdateClassName($componentName, $fileVersion);
                if (!in_array($className, $this->updatedClasses)
                    && class_exists($className, false)
                ) {
                    $this->executeListenerHook('onComponentUpdateFileStarting', array($componentName, $file, $className, $fileVersion));

                    $this->executeSingleUpdateClass($className);

                    $this->executeListenerHook('onComponentUpdateFileFinished', array($componentName, $file, $className, $fileVersion));

                    // makes sure to call Piwik\Columns\Updater only once as one call updates all dimensions at the same
                    // time for better performance
                    $this->updatedClasses[] = $className;
                }

                $this->markComponentSuccessfullyUpdated($componentName, $fileVersion);
            } catch (UpdaterErrorException $e) {
                $this->executeListenerHook('onError', array($componentName, $fileVersion, $e));

                throw $e;
            } catch (\Exception $e) {
                $warningMessages[] = $e->getMessage();

                $this->executeListenerHook('onWarning', array($componentName, $fileVersion, $e));
            }
        }

        // to debug, create core/Updates/X.php, update the core/Version.php, throw an Exception in the try, and comment the following lines
        $updatedVersion = $this->componentsWithNewVersion[$componentName][self::INDEX_NEW_VERSION];
        $this->markComponentSuccessfullyUpdated($componentName, $updatedVersion);

        $this->executeListenerHook('onComponentUpdateFinished', array($componentName, $updatedVersion, $warningMessages));

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
                    if (// if the update is from a newer version
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
                $this->markComponentSuccessfullyUpdated($name, $newVersion);
            }
        }

        return $componentsWithUpdateFile;
    }

    /**
     * Construct list of outdated components
     *
     * @param string[] $componentsToCheck An array mapping component names to the latest locally available version.
     *                                    If the version is later than the currently installed version, the component
     *                                    must be upgraded.
     *
     *                                    Example: `array('core' => '2.11.0')`
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

        $recordedCoreVersion = $this->getCurrentComponentVersion('core');
        if (empty($recordedCoreVersion)) {
            // This should not happen
            $recordedCoreVersion = Version::VERSION;
            $this->markComponentSuccessfullyUpdated('core', $recordedCoreVersion);
        }

        foreach ($componentsToCheck as $name => $version) {
            $currentVersion = $this->getCurrentComponentVersion($name);

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
     * Updates multiple components, while capturing & returning errors and warnings.
     *
     * @param string[] $componentsWithUpdateFile Component names mapped with arrays of update files. Same structure
     *                                           as the result of `getComponentsWithUpdateFile()`.
     * @return array Information about the update process, including:
     *
     *               * **warnings**: The list of warnings that occurred during the update process.
     *               * **errors**: The list of updater exceptions thrown during individual component updates.
     *               * **coreError**: True if an exception was thrown while updating core.
     *               * **deactivatedPlugins**: The list of plugins that were deactivated due to an error in the
     *                                         update process.
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
     * Returns any updates that should occur for core and all plugins that are both loaded and
     * installed. Also includes updates required for dimensions.
     *
     * @return string[]|null Returns the result of `getComponentsWithUpdateFile()`.
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

        $columnsVersions = $this->columnsUpdater->getAllVersions($this);
        foreach ($columnsVersions as $component => $version) {
            $componentsToCheck[$component] = $version;
        }

        $componentsWithUpdateFile = $this->getComponentsWithUpdateFile($componentsToCheck);

        if (count($componentsWithUpdateFile) == 0) {
            $this->columnsUpdater->onNoUpdateAvailable($columnsVersions);

            if (!$this->hasNewVersion('core')) {
                return null;
            }
        }

        return $componentsWithUpdateFile;
    }

    /**
     * Execute multiple migration queries from a single Update file.
     *
     * @param string $file The path to the Updates file.
     * @param array $migrationQueries An array mapping SQL queries w/ one or more MySQL errors to ignore.
     */
    public function executeMigrationQueries($file, $migrationQueries)
    {
        foreach ($migrationQueries as $update => $ignoreError) {
            $this->executeSingleMigrationQuery($update, $ignoreError, $file);
        }
    }

    /**
     * Execute a single migration query from an update file.
     *
     * @param string $migrationQuerySql The SQL to execute.
     * @param int|int[]|null An optional error code or list of error codes to ignore.
     * @param string $file The path to the Updates file.
     */
    public function executeSingleMigrationQuery($migrationQuerySql, $errorToIgnore, $file)
    {
        try {
            $this->executeListenerHook('onStartExecutingMigrationQuery', array($file, $migrationQuerySql));

            Db::exec($migrationQuerySql);
        } catch (\Exception $e) {
            $this->handleUpdateQueryError($e, $migrationQuerySql, $errorToIgnore, $file);
        }

        $this->executeListenerHook('onFinishedExecutingMigrationQuery', array($file, $migrationQuerySql));
    }

    /**
     * Handle an update query error.
     *
     * @param \Exception $e The error that occurred.
     * @param string $updateSql The SQL that was executed.
     * @param int|int[]|null An optional error code or list of error codes to ignore.
     * @param string $file The path to the Updates file.
     * @throws \Exception
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

    private function executeListenerHook($hookName, $arguments)
    {
        foreach ($this->updateObservers as $listener) {
            call_user_func_array(array($listener, $hookName), $arguments);
        }
    }

    private function executeSingleUpdateClass($className)
    {
        $update = StaticContainer::getContainer()->make($className);
        try {
            call_user_func(array($update, 'doUpdate'), $this);
        } catch (\Exception $e) {
            // if an Update file executes PHP statements directly, DB exceptions be handled by executeSingleMigrationQuery, so
            // make sure to check for them here
            if ($e instanceof Zend_Db_Exception) {
                throw new UpdaterErrorException($e->getMessage(), $e->getCode(), $e);
            } else {
                throw $e;
            }
        }
    }

    /**
     * Performs database update(s)
     *
     * @param string $file Update script filename
     * @param array $sqlarray An array of SQL queries to be executed
     * @throws UpdaterErrorException
     */
    public static function updateDatabase($file, $sqlarray)
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
     * Record version of successfully completed component update
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
