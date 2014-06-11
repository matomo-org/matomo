<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers\Columns;

use Piwik\Piwik;
use Piwik\Plugin\VisitDimension;
use Piwik\Plugin\Segment;

class Keyword extends VisitDimension
{    
    protected $fieldName = 'referer_keyword';

    protected function init()
    {
        $segment = new Segment();
        $segment->setSegment('referrerKeyword');
        $segment->setName('General_ColumnKeyword');
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('General_ColumnKeyword');
    }
}