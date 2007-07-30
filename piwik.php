<?php
/**
 * To maximise the performance of the logging module, we 
 * - minimize the number of external files included. 
 * 	 Ideally only one (the configuration file).
 * - write all the SQL queries without using any DB abstraction layer.
 * 	 Of course we carefully filter all input values.
 * - minimize the number of SQL queries necessary to complete the algorithm.
 *   
 * 
 * 
 * 
 * 
 */


// load config file
// connect Database
// clean parameters

// in case of any error during the logging, 
// log the errors in DB or file depending on the config file

// main algorithm 
// => input : variables filtered
// => action : read cookie, read database, database logging, cookie writing

// display the logo or pixel 1*1 GIF

?>
