<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @category Piwik
 * @package Piwik
 */

// no direct access
defined('PIWIK_INCLUDE_PATH') or die;

/**
 * @see core/Option.php
 */
require_once PIWIK_INCLUDE_PATH . '/core/Option.php';

/**
 * Load and execute all relevant, incremental update scripts for Piwik core and plugins, and bump the component version numbers for completed updates.
 *
 * @package Piwik
 * @subpackage Piwik_Updater
 * @see Piwik_iUpdate
 */
class Piwik_Updater
{
	const INDEX_CURRENT_VERSION = 0;
	const INDEX_NEW_VERSION = 1;
	
	public $pathUpdateFileCore;
	public $pathUpdateFilePlugins;

	private $componentsToCheck = array();
	
	public function __construct()
	{
		$this->pathUpdateFileCore = PIWIK_INCLUDE_PATH . '/core/Updates/';
		$this->pathUpdateFilePlugins = PIWIK_INCLUDE_PATH . '/plugins/%s/Updates/';
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
	public function recordComponentSuccessfullyUpdated($name, $version)
	{
		try {
			Piwik_SetOption('version_'.$name, $version, $autoload = 1);
		} catch(Exception $e) {
			// case when the option table is not yet created (before 0.2.10)
		}
	}
	
	/**
	 * Returns a list of components (core | plugin) that need to run through the upgrade process.
	 *
	 * @return array( componentName => array( file1 => version1, [...]), [...])
	 */
	public function getComponentsWithUpdateFile()
	{
		$this->componentsWithNewVersion = $this->loadComponentsWithNewVersion();
		$this->componentsWithUpdateFile = $this->loadComponentsWithUpdateFile();
		return $this->componentsWithUpdateFile;
	}

	/**
	 * Update the named component
	 *
	 * @param string $name
	 * @return array of warning strings if applicable
	 */
	public function update($name)
	{
		$warningMessages = array();
		foreach($this->componentsWithUpdateFile[$name] as $file => $fileVersion)
		{
			try {
				require_once $file; // prefixed by PIWIK_INCLUDE_PATH

				if($name == 'core')
				{
					$className = 'Piwik_Updates_' . str_replace('.', '_', $fileVersion);
				}
				else
				{
					$className = 'Piwik_'. $name .'_Updates_' . str_replace('.', '_', $fileVersion);
				}

				if(class_exists($className, false))
				{
					call_user_func( array($className, 'update') );
				}

				$this->recordComponentSuccessfullyUpdated($name, $fileVersion);
			} catch( Piwik_Updater_UpdateErrorException $e) {
				throw $e;
			} catch( Exception $e) {
				$warningMessages[] = $e->getMessage();
			}
		}
		
		// to debug, create core/Updates/X.php, update the core/Version.php, throw an Exception in the try, and comment the following line
		$this->recordComponentSuccessfullyUpdated($name, $this->componentsWithNewVersion[$name][self::INDEX_NEW_VERSION]);
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
		foreach($this->componentsWithNewVersion as $name => $versions)
		{
			$currentVersion = $versions[self::INDEX_CURRENT_VERSION];
			$newVersion = $versions[self::INDEX_NEW_VERSION];
			
			if($name == 'core')
			{
				$pathToUpdates = $this->pathUpdateFileCore . '*.php';
			}
			else
			{
				$pathToUpdates = sprintf($this->pathUpdateFilePlugins, $name) . '*.php';
			}
			
			$files = glob( $pathToUpdates );
			if($files === false)
			{
				continue;
			}
			foreach( $files as $file)
			{
				$fileVersion = basename($file, '.php');
				if(version_compare($currentVersion, $fileVersion) == -1)
				{
					$componentsWithUpdateFile[$name][$file] = $fileVersion;
				}
			}
			
			if(isset($componentsWithUpdateFile[$name]))
			{
				// order the update files by version asc
				uasort($componentsWithUpdateFile[$name], "version_compare");
			}
			else
			{
				// there are no update file => nothing to do, update to the new version is successful
				$this->recordComponentSuccessfullyUpdated($name, $newVersion);
			}
		}
		return $componentsWithUpdateFile;
	}
	
	/**
	 * Construct list of outdated components
	 *
	 * @return array array( componentName => array( oldVersion, newVersion), [...])
	 */
	private function loadComponentsWithNewVersion()
	{
		$componentsToUpdate = array();
		
		// we make sure core updates are processed before any plugin updates
		if(isset($this->componentsToCheck['core']))
		{
			$coreVersions = $this->componentsToCheck['core'];
			unset($this->componentsToCheck['core']);
			$this->componentsToCheck = array_merge( array('core' => $coreVersions), $this->componentsToCheck);
		}
		
		foreach($this->componentsToCheck as $name => $version)
		{
			try {
				$currentVersion = Piwik_GetOption('version_'.$name);
			} catch( Exception $e) {
				// mysql error 1146: table doesn't exist
				if(Zend_Registry::get('db')->isErrNo($e, '1146'))
				{
					// case when the option table is not yet created (before 0.2.10)
					$currentVersion = false;
				}
				else
				{
					// failed for some other reason
					throw $e;
				}
			}
			if($currentVersion === false)
			{
				if($name === 'core')
				{
					$currentVersion = '0.2.9';
				}
				else
				{
					$currentVersion = '0.0.1';
				}
				$this->recordComponentSuccessfullyUpdated($name, $currentVersion);
			}

			$versionCompare = version_compare($currentVersion, $version);
			if($versionCompare == -1)
			{
				$componentsToUpdate[$name] = array(
								self::INDEX_CURRENT_VERSION => $currentVersion, 
								self::INDEX_NEW_VERSION => $version
							);
			}
			else if($versionCompare == 1) 
			{
				// the version in the DB is newest.. we choose to ignore (for the time being)
			}
		}
		return $componentsToUpdate;
	}

	/**
	 * Performs database update(s)
	 *
	 * @param string $file Update script filename
	 * @param array $sqlarray An array of SQL queries to be executed
	 */
	static function updateDatabase($file, $sqlarray)
	{
		foreach($sqlarray as $update => $ignoreError)
		{
			try {
				Piwik_Query( $update );
			} catch(Exception $e) {
				if(($ignoreError === false) || !Zend_Registry::get('db')->isErrNo($e, $ignoreError))
				{
					$message =  $file .":\nError trying to execute the query '". $update ."'.\nThe error was: ". $e->getMessage();
					throw new Piwik_Updater_UpdateErrorException($message);
				}
			}
		}
	}
}

/**
 * Exception thrown by updater if a non-recoverable error occurs
 *
 * @package Piwik
 * @subpackage Piwik_Updater
 */
class Piwik_Updater_UpdateErrorException extends Exception {}
