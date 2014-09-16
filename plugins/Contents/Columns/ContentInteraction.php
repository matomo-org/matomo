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

class ContentInteraction extends ActionDimension
{
    protected $columnName = 'idaction_content_interaction';
    protected $columnType = 'INTEGER(10) UNSIGNED DEFAULT NULL';

    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setSegment('contentInteraction');
        $segment->setName('Contents_Interaction');
        $segment->setAcceptedValues('The type of interaction with the content. For instance "click" or "submit".');
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('Contents_Interaction');
    }

    public function getActionId()
    {
        return Action::TYPE_CONTENT_INTERACTION;
    }

    public function onLookupAction(Request $request, Action $action)
    {
        $interaction = $request->getParam('c_i');

        if (empty($interaction)) {
            return false;
        }

        $interaction = trim($interaction);

        if (strlen($interaction) > 0) {
            return $interaction;
        }

        return false;
    }
}