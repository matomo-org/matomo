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

class PageUrl extends ActionDimension
{
    protected $columnName = 'idaction_url';
    protected $columnType = 'INTEGER(10) UNSIGNED DEFAULT NULL';

    protected function configureSegments()
    {
        $segment = new Segment();
        $segment->setSegment('pageUrl');
        $segment->setName('Actions_ColumnPageURL');
        $segment->setAcceptedValues('All these segments must be URL encoded, for example: ' . urlencode('http://example.com/path/page?query'));
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('Actions_ColumnPageURL');
    }
}
