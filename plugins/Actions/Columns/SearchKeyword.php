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

class SearchKeyword extends ActionDimension
{
    protected $columnName = 'idaction_name';
    protected $segmentName = 'siteSearchKeyword';
    protected $nameSingular = 'Actions_SiteSearchKeyword';
    protected $type = self::TYPE_TEXT;

}
