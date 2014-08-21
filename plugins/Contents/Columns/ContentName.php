<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Contents\Columns;

use Piwik\Piwik;
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Plugins\Contents\Actions\ActionContent;
use Piwik\Plugins\Actions\Segment;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;

class ContentName extends ActionDimension
{
    protected $columnName = 'idaction_name';

    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setSegment('contentName');
        $segment->setName('Contents_ContentName');
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('Contents_ContentName');
    }

    public function getActionId()
    {
        return Action::TYPE_CONTENT_NAME;
    }

    public function onLookupAction(Request $request, Action $action)
    {
        if (!($action instanceof ActionContent)) {
            return false;
        }

        $contentName = $request->getParam('c_n');
        $contentName = trim($contentName);

        if (strlen($contentName) > 0) {
            return $contentName;
        }

        return false;
    }
}