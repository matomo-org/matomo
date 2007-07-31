<?php


/**
 * Plugin specification for a statistics logging plugin
 * 
 * A plugin that display data in the Piwik Interface is very different from a plugin 
 * that will save additional data in the database during the statistics logging. 
 * These two types of plugins don't have the same requirements at all. Therefore a plugin
 * that saves additional data in the database during the stats logging process will have a different
 * structure.
 * 
 * A plugin for logging data has to focus on performance and therefore has to stay as simple as possible.
 * For input data, it is strongly advised to use the Piwik methods available in Piwik_Common 
 *
 * Things that can be done with such a plugin:
 * - having a dependency with a list of other plugins
 * - have an install step that would prepare the plugin environment
 * 		- install could add columns to the tables
 * 		- install could create tables 
 * - register to hooks at several points in the logging process
 * - register to hooks in other plugins
 * - generally a plugin method can modify data (filter) and add/remove data 
 * 
 */

/**
 * To maximise the performance of the logging module, we use different techniques.
 * 
 * On the PHP-only side:
 * - minimize the number of external files included. 
 * 	 Ideally only one (the configuration file) in all the normal cases.
 *   We load the Loggers only when an error occurs ; this error is logged in the DB/File/etc
 *   depending on the loggers settings in the configuration file.
 * - we may have to include external classes but we try to include only very 
 *   simple code without any dependency, so that we could simply write a script
 *   that would merge all this simple code into a big piwik.php file.
 * 
 * On the Database-related side:
 * - write all the SQL queries without using any DB abstraction layer.
 * 	 Of course we carefully filter all input values.
 * - minimize the number of SQL queries necessary to complete the algorithm.
 * - carefully index the tables used
 * 
 * [ - use a partitionning by date for the tables ]
 *   
 * - handle the timezone settings??
 * 
 * [ - country detection plugin => ip lookup ]
 * [ - precise country detection plugin ]
 * 
 * 
 * 
 */

/**
 * Configuration options for the statsLogEngine module:
 * - record_logs ; defines if the logs are saved
 * - use_cookie  ; defines if we try to get/set a cookie to help recognize a unique visitor
 * - 
 */




class Piwik_LogStats_Action
{
	/**
	 * About the Action concept:
	 * 
	 * - An action is defined by a name.
	 * - The name can be specified in the JS Code in the variable 'action_name'
	 * - If the name is not specified, we use the URL to build a name based on the path.
	 *   For example for "http://piwik.org/test/my_page/test.html" 
	 *   the name would be "test/my_page/test.html"
	 * - An action is associated to a URL
	 * 
	 */
	function getName()
	{}
	
	/**
	 * A query to the Piwik statistics logging engine is associated to 1 action.
	 * 
	 * We have to save the action for the current visit.
	 * - check the action exists already in the db
	 * - save the relation between idvisit and idaction
	 */
	function save()
	{}
}

class Piwik_LogStats_Visit
{
	// test if the visitor is excluded because of
	// - IP
	// - cookie
	// - configuration option?
	public function isExcluded()
	{}
	
	/**
	 * Handles the visitor.
	 * 
	 * We have to split the visitor into one of the category 
	 * - Known visitor
	 * - New visitor
	 * 
	 * A known visitor is a visitor that has already visited the website in the current month.
	 * We define a known visitor using (in order of importance):
	 * 1) A cookie that contains
	 * 		// a unique id for the visitor
	 * 		- id_visitor 
	 * 
	 * 		// the timestamp of the last action in the most recent visit
	 * 		- timestamp_last_action 
	 * 
 	 *  	// the timestamp of the first action in the most recent visit
	 * 		- timestamp_first_action
	 * 
	 * 		// the ID of the most recent visit (which could be in the past or the current visit)
	 * 		- id_visit 
	 * 
	 * 		// the ID of the most recent action
	 * 		- id_last_action
	 * 2) If the visitor doesn't have a cookie, we try to look for a similar visitor configuration.
	 * 	  We search for a visitor with the same plugins/OS/Browser/Resolution for today for this website.
	 */
	 
	/**
	 * Once we have the visitor information, we have to define if the visit is a new or a known visit.
	 * 
	 * 1) When the last action was done more than 30min ago, 
	 * 	  or if the visitor is new, then this is a new visit.
	 *    
	 * 	  In the case of a new visit, then the time spent 
	 *    during the last action of the previous visit is unknown.
	 * 
	 *    In the case of a new visit but with a known visitor, 
	 *    we can set the 'returning visitor' flag.
	 * 
	 * 2) If the last action is less than 30min ago, then the same visit is going on. 
	 *    Because the visit goes on, we can get the time spent during the last action.
	 */
	 
	/**
	 * In all the cases we set a cookie to the visitor with the new information.
	 */
	public function handle()
	{}
	
	/**
	 * In the case of a known visit, we have to do the following actions:
	 * 
	 * 1) Insert the new action
	 * 
	 * 2) Update the visit information
	 */
	private function handleKnownVisit()
	{}
	
	/**
	 * In the case of a new visit, we have to do the following actions:
	 * 
	 * 1) Insert the new action
	 * 
	 * 2) Insert the visit information
	 */
	private function handleNewVisit()
	{}
}


class Piwik_LogStats
{
	// load the configuration file
	function loadConfigFile() 
	{}
	
	// create the database object
	function connectDatabase()
	{}
	
	// in case of any error during the logging, 
	// log the errors in DB or file depending on the config file
	function logMessage()
	{}

	// set some php configuration 
	function init()
	{}
	
	// main algorithm 
	// => input : variables filtered
	// => action : read cookie, read database, database logging, cookie writing
	function main()
	{}	
	
	// display the logo or pixel 1*1 GIF
	// or a marketing page if no parameters in the url
	// or redirect to a url (transmit the cookie as well)
	// or load a URL (rss feed) (transmit the cookie as well)
	public function endProcess()
	{}
}

?>
