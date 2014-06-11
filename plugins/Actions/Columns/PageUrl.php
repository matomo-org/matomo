<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Actions\Columns;

use Piwik\Piwik;
use Piwik\Plugin\ActionDimension;
use Piwik\Plugins\Actions\Segment;

class PageUrl extends ActionDimension
{
    protected $fieldName = 'idaction_url';
    protected $fieldType = 'INTEGER(10) UNSIGNED DEFAULT NULL';

    protected function init()
    {
        $segment = new Segment();
        $segment->setSegment('pageUrl');
        $segment->setName('Actions_ColumnPageURL');
        $segment->setAcceptValues('All these segments must be URL encoded, for example: ' . urlencode('http://example.com/path/page?query'));
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('Actions_ColumnPageURL');
    }

    /*
    public function shouldHandleAction(Request $request)
    {
        return true;
    }

    public function getActionId()
    {
        return 1;
    }*/
}
