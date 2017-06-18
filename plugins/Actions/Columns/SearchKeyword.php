<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\Columns;

use Piwik\Piwik;
use Piwik\Plugin\Dimension\ActionDimension;
use Piwik\Plugins\Actions\Segment;

class SearchKeyword extends ActionDimension
{
    protected $columnName = 'idaction_name';
    protected $type = self::TYPE_TEXT;

    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setSegment('siteSearchKeyword');
        $segment->setName('Actions_SiteSearchKeyword');
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('General_ColumnKeyword');
    }
}
