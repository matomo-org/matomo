<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Actions\Columns;

use Piwik\Columns\Discriminator;
use Piwik\Columns\Join\ActionNameJoin;
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Tracker\Action;
use Piwik\Tracker\TableLogAction;

class PageTitle extends ActionDimension
{
    protected $columnName = 'idaction_name';
    protected $columnType = 'INTEGER(10) UNSIGNED';
    protected $type = self::TYPE_TEXT;
    protected $segmentName = 'pageTitle';
    protected $nameSingular = 'Goals_PageTitle';
    protected $namePlural = 'Actions_WidgetPageTitles';
    protected $category = 'General_Actions';
    protected $suggestedValuesApi = 'Actions.getPageTitles';
    protected $sqlFilter = [TableLogAction::class, 'getOptimizedIdActionSqlMatch'];

    public function getDbColumnJoin()
    {
        return new ActionNameJoin();
    }

    public function getDbDiscriminator()
    {
        return new Discriminator('log_action', 'type', Action::TYPE_PAGE_TITLE);
    }
}
