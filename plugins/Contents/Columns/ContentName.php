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
use Piwik\Exception\InvalidRequestParameterException;
use Piwik\Plugins\Contents\Actions\ActionContent;
use Piwik\Tracker\Action;
use Piwik\Tracker\Request;

class ContentName extends ActionDimension
{
    protected $columnName = 'idaction_content_name';
    protected $columnType = 'INTEGER(10) UNSIGNED DEFAULT NULL';
    protected $segmentName = 'contentName';
    protected $nameSingular = 'Contents_ContentName';
    protected $namePlural = 'Contents_ContentNames';
    protected $acceptValues = 'The name of a content block, for instance "Ad Sale"';
    protected $suggestedValuesApi = 'Contents.getContentNames';
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

        throw new InvalidRequestParameterException('Param `c_n` must not be empty or filled with whitespaces');
    }
}