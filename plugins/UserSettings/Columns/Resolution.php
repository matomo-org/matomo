<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserSettings\Columns;

use Piwik\Piwik;
use Piwik\Plugin\VisitDimension;
use Piwik\Plugins\UserSettings\Segment;

class Resolution extends VisitDimension
{    
    protected $fieldName = 'config_resolution';

    protected function init()
    {
        $segment = new Segment();
        $segment->setSegment('resolution');
        $segment->setName('UserSettings_ColumnResolution');
        $segment->setCategory('Visit');
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('UserSettings_ColumnResolution');
    }
}