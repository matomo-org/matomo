<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link    http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\CustomPiwikJs\tests\Framework\Mock;

use Piwik\Plugins\CustomPiwikJs\File;
use Piwik\Plugins\CustomPiwikJs\TrackingCode\PluginTrackerFiles;

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
