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

class ContentPiece extends ActionDimension
{
    protected $columnName = 'idaction_content_piece';
    protected $columnType = 'INTEGER(10) UNSIGNED DEFAULT NULL';

    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setSegment('contentPiece');
        $segment->setName('Contents_ContentPiece');
        $segment->setAcceptedValues('The actual content. For instance "ad.jpg" or "My text ad"');
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('Contents_ContentPiece');
    }

    public function getActionId()
    {
        return Action::TYPE_CONTENT_PIECE;
    }

    public function onLookupAction(Request $request, Action $action)
    {
        $contentPiece = $request->getParam('c_p');

        if (empty($contentPiece)) {
            return false;
        }

        $contentPiece = trim($contentPiece);

        if (strlen($contentPiece) > 0) {
            return $contentPiece;
        }

        return false;
    }
}