<?php
namespace Piwik\Plugins\testpluginUpdates;

use Piwik\Updates as PiwikUpdates;

class Updates_0_5 extends PiwikUpdates
{
    function doUpdate(\Piwik\Updater $updater)
    {
        throw new \Piwik\Exception\MissingFilePermissionException('make sure this exception is thrown');
    }
}
