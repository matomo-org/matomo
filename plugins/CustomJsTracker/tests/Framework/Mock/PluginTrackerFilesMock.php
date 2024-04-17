<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomJsTracker\tests\Framework\Mock;

use Piwik\Plugins\CustomJsTracker\File;
use Piwik\Plugins\CustomJsTracker\TrackingCode\PluginTrackerFiles;

class PluginTrackerFilesMock extends PluginTrackerFiles
{
    /**
     * @var array
     */
    private $files;

    public function __construct($files)
    {
        $this->files = $files;
    }

    public function find()
    {
        $files = array();
        foreach ($this->files as $file) {
            $files[] = new File(PIWIK_DOCUMENT_ROOT . $file);
        }
        return $files;
    }
}
