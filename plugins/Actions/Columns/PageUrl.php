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

class PageUrl extends ActionDimension
{
    protected $columnName = 'idaction_url';
    protected $columnType = 'INTEGER(10) UNSIGNED DEFAULT NULL';
    protected $segmentName = 'pageUrl';
    protected $nameSingular = 'Actions_ColumnPageURL';
    protected $namePlural = 'Actions_PageUrls';
    protected $type = self::TYPE_URL;
    protected $category = 'General_Actions';
    protected $sqlFilter = '\\Piwik\\Tracker\\TableLogAction::getIdActionFromSegment';
    protected $suggestedValuesApi = 'Actions.getPageUrls';

    public function getDbColumnJoin()
    {
        return new ActionNameJoin();
    }

    public function getDbDiscriminator()
    {
        return new Discriminator('log_action', 'type', Action::TYPE_PAGE_URL);
    }

}
