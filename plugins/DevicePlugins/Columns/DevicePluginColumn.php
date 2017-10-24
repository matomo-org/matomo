<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\DevicePlugins\Columns;

use Piwik\Plugin\Dimension\VisitDimension;

/**
 * Columns extending this class will be automatically considered as new browser plugin
 *
 * Note: The column name needs to start with `config_` to be handled correctly
 */
abstract class DevicePluginColumn extends VisitDimension
{
    /**
     * Can be overwritten by Columns in other plugins to
     * set a custom icon not included in Piwik Core
     */
    public $columnIcon = null;
}
