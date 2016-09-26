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
use Piwik\Plugins\Actions\Segment;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;

class ContentTarget extends ActionDimension
{
    protected $columnName = 'idaction_content_target';
    protected $columnType = 'INTEGER(10) UNSIGNED DEFAULT NULL';

    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setSegment('contentTarget');
        $segment->setName('Contents_ContentTarget');
        $segment->setAcceptedValues('For instance the URL of a landing page: "http://landingpage.example.com"');
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('Contents_ContentTarget');
    }

    public function getActionId()
    {
        return Action::TYPE_CONTENT_TARGET;
    }

    public function onLookupAction(Request $request, Action $action)
    {
        $contentTarget = $request->getParam('c_t');
        $contentTarget = trim($contentTarget);

        if (strlen($contentTarget) > 0) {
            return $contentTarget;
        }

        return false;
    }
}