<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\Columns;

use Piwik\Columns\Discriminator;
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Tracker\Action;

class PageGenerationTime extends ActionDimension
{
    protected $nameSingular = 'General_ColumnPageGenerationTime';
    protected $columnName = 'custom_float';
    protected $category = 'General_Actions';
    protected $type = self::TYPE_DURATION_MS;

    public function getDbDiscriminator()
    {
        return new Discriminator('log_action', 'type', Action::TYPE_PAGE_URL);
    }
}
