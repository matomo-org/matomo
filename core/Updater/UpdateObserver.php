<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Updater;

/**
 * UpdateObservers can be used to inject logic into the component updating process. Derive
 * from this base class and add an instance of the derived class to a Updater instance. When
 * Updater::update() is called, the methods in the added UpdateListeners will be executed
 * accordingly.
 */
abstract class UpdateObserver
{
    /**
     * Executed when a component is about to be updated. At this point, no SQL queries or other
     * updating logic has been executed.
     *
     * @param string $name The name of the component being updated.
     */
    public function onComponentUpdateStarting($name)
    {
        // empty
    }

    /**
     * Executed after a component has been successfully updated.
     *
     * @param string $name The name of the component being updated.
     * @param string $version The version of the component that was updated.
     * @param string[] $warnings Any warnings that occurred during the component update process.
     */
    public function onComponentUpdateFinished($name, $version, $warnings)
    {
        // empty
    }

    /**
     * Executed before the update method of an Updates class is executed.
     *
     * @param string $componentName The name of the component being updated.
     * @param string $file The path to the Updates file being executed.
     * @param string $updateClassName The name of the Update class that should exist within the Update file.
     * @param string $version The version the Updates file belongs to.
     */
    public function onComponentUpdateFileStarting($componentName, $file, $updateClassName, $version)
    {
        // empty
    }

    /**
     * Executed after the update method of an Updates class is successfully executed.
     *
     * @param string $componentName The name of the component being updated.
     * @param string $file The path to the Updates file being executed.
     * @param string $updateClassName The name of the Update class that should exist within the Update file.
     * @param string $version The version the Updates file belongs to.
     */
    public function onComponentUpdateFileFinished($componentName, $file, $updateClassName, $version)
    {
        // empty
    }

    /**
     * Executed before a migration is executed.
     *
     * @param string $updateFile The path to the Updates file being executed.
     * @param Migration $migration The migration that is about to be executed.
     */
    public function onStartExecutingMigration($updateFile, Migration $migration)
    {
        // empty
    }

    /**
     * Executed after a migration is executed.
     *
     * @param string $updateFile The path to the Updates file being executed.
     * @param Migration $migration The migration that is about to be executed.
     */
    public function onFinishedExecutingMigration($updateFile, Migration $migration)
    {
        // empty
    }

    /**
     * Executed when a warning occurs during the update process. A warning occurs when an Updates file
     * throws an exception that is not a UpdaterErrorException.
     *
     * @param string $componentName The name of the component being updated.
     * @param string $version The version of the Updates file during which the warning occurred.
     * @param \Exception $exception The exception that generated the warning.
     */
    public function onWarning($componentName, $version, \Exception $exception)
    {
        // empty
    }

    /**
     * Executed when an error occurs during the update process. An error occurs when an Updates file
     * throws a UpdaterErrorException.
     *
     * @param string $componentName The name of the component being updated.
     * @param string $version The version of the Updates file during which the error occurred.
     * @param \Exception $exception The exception that generated the error.
     */
    public function onError($componentName, $version, \Exception $exception)
    {
        // empty
    }
}
