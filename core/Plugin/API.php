<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugin;

use Piwik\Singleton;

/**
 * The base class of all API singletons.
 *
 * Plugins that want to expose functionality through the Reporting API should create a class
 * that extends this one. Every public method in that class that is not annotated with **@ignore**
 * will be callable through Piwik's Web API.
 *
 * _Note: If your plugin calculates and stores reports, they should be made available through the API._
 *
 * ### Examples
 *
 * **Defining an API for a plugin**
 *
 *     class API extends \Piwik\Plugin\API
 *     {
 *         public function myMethod($idSite, $period, $date, $segment = false)
 *         {
 *             $dataTable = // ... get some data ...
 *             return $dataTable;
 *         }
 *     }
 *
 * **Linking to an API method**
 *
 *     <a href="?module=API&method=MyPlugin.myMethod&idSite=1&period=day&date=2013-10-23">Link</a>
 *
 * @api
 */
abstract class API extends Singleton
{

}
