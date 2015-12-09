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
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Plugins\Actions\Segment;

class ActionUrl extends ActionDimension
{
    public function getName()
    {
        return Piwik::translate('Actions_ColumnActionURL');
    }

    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setSegment('actionUrl');
        $segment->setName('Actions_ColumnActionURL');
        $segment->setUnionOfSegments(array('pageUrl', 'downloadUrl', 'outlinkUrl'));

        $this->addSegment($segment);
    }

}
