<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Contents\Columns;

use Piwik\Columns\Discriminator;
use Piwik\Columns\Join\ActionNameJoin;
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Plugins\Contents\Actions\ActionContent;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;

class ContentPiece extends ActionDimension
{
    protected $columnName = 'idaction_content_piece';
    protected $columnType = 'INTEGER(10) UNSIGNED DEFAULT NULL';
    protected $segmentName = 'contentPiece';
    protected $nameSingular = 'Contents_ContentPiece';
    protected $namePlural = 'Contents_ContentPieces';
    protected $acceptValues = 'The actual content. For instance "ad.jpg" or "My text ad"';
    protected $suggestedValuesApi = 'Contents.getContentPieces';
    protected $type = self::TYPE_TEXT;
    protected $category = 'General_Actions';
    protected $sqlFilter = '\\Piwik\\Tracker\\TableLogAction::getIdActionFromSegment';

    public function getDbColumnJoin()
    {
        return new ActionNameJoin();
    }

    public function getDbDiscriminator()
    {
        return new Discriminator('log_action', 'type', $this->getActionId());
    }

    public function getActionId()
    {
        return Action::TYPE_CONTENT_PIECE;
    }

    public function onLookupAction(Request $request, Action $action)
    {
        if (!($action instanceof ActionContent)) {
            return false;
        }

        $contentPiece = $request->getParam('c_p');
        $contentPiece = trim($contentPiece);

        if (strlen($contentPiece) > 0) {
            return $contentPiece;
        }

        return false;
    }
}