<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers\Columns;

use Piwik\Piwik;
use Piwik\Plugin\Dimension\VisitDimension;

class Referrer extends VisitDimension
{    
    public function getName()
    {
        return Piwik::translate('Referrers_Referrer');
    }
}