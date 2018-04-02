<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\GeoIp2\Columns;

class Region extends \Piwik\Plugins\UserCountry\Columns\Region
{
    protected $columnType = 'char(3) DEFAULT NULL';
    protected $segmentName = '';

    public function uninstall()
    {
        // do not remove region column when plugin is deactivated
    }
}