<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\Columns;

use Piwik\Columns\Discriminator;
use Piwik\Columns\Join\ActionNameJoin;
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Tracker\Action;

class ClickedUrl extends ActionDimension
{
    protected $columnName = 'idaction_url';
    protected $segmentName = 'outlinkUrl';
    protected $nameSingular = 'Actions_ColumnClickedURL';
    protected $namePlural = 'Actions_ColumnClickedURLs';
    protected $category = 'General_Actions';
    protected $suggestedValuesApi = 'Actions.getOutlinks';
    protected $sqlFilter = '\\Piwik\\Tracker\\TableLogAction::getIdActionFromSegment';
    protected $type = self::TYPE_URL;

    public function getDbColumnJoin()
    {
        return new ActionNameJoin();
    }

    public function getDbDiscriminator()
    {
        return new Discriminator('log_action', 'type', Action::TYPE_OUTLINK);
    }
}
