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

class ContentInteraction extends ActionDimension
{
    protected $columnName = 'idaction_content_interaction';
    protected $columnType = 'INTEGER(10) UNSIGNED DEFAULT NULL';
    protected $type = self::TYPE_TEXT;
    protected $acceptValues = 'The type of interaction with the content. For instance "click" or "submit".';
    protected $segmentName = 'contentInteraction';
    protected $nameSingular = 'Contents_ContentInteraction';
    protected $namePlural = 'Contents_ContentInteractions';
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
        return Action::TYPE_CONTENT_INTERACTION;
    }

    public function onLookupAction(Request $request, Action $action)
    {
        if (!($action instanceof ActionContent)) {
            return false;
        }

        $interaction = $request->getParam('c_i');
        $interaction = trim($interaction);

        if (strlen($interaction) > 0) {
            return $interaction;
        }

        return false;
    }
}