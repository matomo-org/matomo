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

class ContentTarget extends ActionDimension
{
    protected $columnName = 'idaction_content_target';
    protected $columnType = 'INTEGER(10) UNSIGNED DEFAULT NULL';
    protected $type = self::TYPE_URL;
    protected $nameSingular = 'Contents_ContentTarget';
    protected $namePlural = 'Contents_ContentTargets';
    protected $segmentName = 'contentTarget';
    protected $category = 'General_Actions';
    protected $sqlFilter = '\\Piwik\\Tracker\\TableLogAction::getIdActionFromSegment';
    protected $acceptValues = 'For instance the URL of a landing page: "http://landingpage.example.com"';

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
        return Action::TYPE_CONTENT_TARGET;
    }

    public function onLookupAction(Request $request, Action $action)
    {
        if (!($action instanceof ActionContent)) {
            return false;
        }

        $contentTarget = $request->getParam('c_t');
        $contentTarget = trim($contentTarget);

        if (strlen($contentTarget) > 0) {
            return $contentTarget;
        }

        return false;
    }
}