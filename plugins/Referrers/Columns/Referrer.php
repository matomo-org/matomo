<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Referrers\Columns;

use Piwik\Columns\Column;
use Piwik\Piwik;

class Referrer extends Column
{
    public function getName()
    {
        return Piwik::translate('Referrers_Referrer');
    }
}