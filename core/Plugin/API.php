<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @category Piwik
 * @package Piwik_PluginArchiver
 */

namespace Piwik\Plugin;

use Piwik\Singleton;

/**
 * The base class of all API singletons.
 * 
 * Plugins that want to expose functionality through an API should create a class
 * that derives from this one. Every public method in that class will be callable
 * through Piwik's API.
 * 
 * ### Example
 * 
 *     class MyAPI extends API
 *     {
 *         public function myMethod($idSite, $period, $date, $segment = false)
 *         {
 *             $dataTable = // ... get some data ...
 *             return $dataTable;
 *         }
 *     }
 * 
 * @api
 */
abstract class API extends Singleton
{

}