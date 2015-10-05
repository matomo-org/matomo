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

class ClickedUrl extends ActionDimension
{
    public function getName()
    {
        return Piwik::translate('Actions_ColumnClickedURL');
    }

    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setSegment('outlinkUrl');
        $segment->setName('Actions_ColumnClickedURL');
        $segment->setSqlSegment('log_link_visit_action.idaction_url');
        $this->addSegment($segment);
    }

}
