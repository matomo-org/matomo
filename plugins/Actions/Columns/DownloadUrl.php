<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\Columns;

use Piwik\Piwik;
use Piwik\Plugins\Actions\Segment;

class DownloadUrl extends PageUrl
{
    public function getName()
    {
        return Piwik::translate('Actions_ColumnDownloadURL');
    }

    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setSegment('downloadUrl');
        $segment->setName('Actions_ColumnDownloadURL');
        $this->addSegment($segment);
    }

}
