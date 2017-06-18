<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\Columns;

use Piwik\Plugin\Dimension\ActionDimension;

class DownloadUrl extends ActionDimension
{
    protected $segmentName = 'downloadUrl';
    protected $nameSingular = 'Actions_ColumnDownloadURL';
    protected $columnName = 'idaction_url';
    protected $category = 'General_Actions';
    protected $sqlFilter = '\\Piwik\\Tracker\\TableLogAction::getIdActionFromSegment';
}
