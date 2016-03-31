<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\UserId\DataTable\Filter;

use Piwik\DataTable\BaseFilter;
use Piwik\Plugins\UserId\Archiver;

/**
 * The DataTable filter that adds visitor-url metadata to all rows. Visitor URL is used then to
 * open a popover with detailed visitor information.
 */
class AddVisitorProfileUrl extends BaseFilter
{
    /**
     * @param DataTable $table
     */
    public function filter($table)
    {
        foreach ($table->getRowsWithoutSummaryRow() as $row) {
            /** @var \Piwik\DataTable\Row $row */
            $visitorId = $row->getMetadata(Archiver::VISITOR_ID_FIELD);
            if (!empty($visitorId)) {
                $row->setMetadata('visitor_url', "module=Live&action=getVisitorProfilePopup&visitorId=$visitorId");
            }
        }
    }
}
