<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\Goals;

use Piwik\API\Request;

class Conversions
{

    public function getConversionForGoal($idGoal, $idSite, $period, $date)
    {
        if (!$period || !$date || !$idSite) {
            return false;
        }

        $datatable = Request::processRequest('Goals.get', array(
            'idGoal'    => $idGoal,
            'period'    => $period,
            'date'      => $date,
            'idSite'    => $idSite,
            'serialize' => 0,
            'segment'   => false
        ));

        // we ignore the segment even if there is one set. We still want to show conversion overview if there are conversions
        // in general but not for this segment

        $dataRow = $datatable->getFirstRow();

        if (!$dataRow) {
            return false;
        }

        return $dataRow->getColumn('nb_conversions');
    }
}
