<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserCountry\Columns;

use Piwik\Piwik;
use Piwik\Plugin\VisitDimension;
use Piwik\Plugins\UserCountry\Segment;

class Latitude extends VisitDimension
{
    protected $fieldName = 'location_latitude';

    protected function init()
    {
        $segment = new Segment();
        $segment->setSegment('latitude');
        $segment->setName('UserCountry_Latitude');
        $segment->setAcceptValues('-33.578, 40.830, etc.<br/>You can select visitors within a lat/long range using &segment=lat&gt;X;lat&lt;Y;long&gt;M;long&lt;N.');
        $this->addSegment($segment);
    }

    public function getName()
    {
        return Piwik::translate('UserCountry_Latitude');
    }
}