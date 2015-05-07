<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik;

use Piwik\DataAccess\RawLogDao;

/**
 * TODO
 * TODO: change name to LogDeleter
 *
 * TODO: class docs
 */
class LogPurger
{
    /**
     * @var RawLogDao
     */
    private $rawLogDao;

    public function __construct(RawLogDao $rawLogDao)
    {
        $this->rawLogDao = $rawLogDao;
    }

    // TODO: these methods should cascade; deleting visits must delete conversions/conversion items/etc.
    public function deleteVisits($visitIds)
    {
        $this->deleteConversions($visitIds);
        $this->rawLogDao->deleteVisitActionsForVisits($visitIds);

        return $this->rawLogDao->deleteVisits($visitIds);
    }

    public function deleteVisitActions($visitActionIds)
    {
        return $this->rawLogDao->deleteVisitActions($visitActionIds);
    }

    public function deleteConversions($visitIds)
    {
        $this->deleteConversionItems($visitIds);
        return $this->rawLogDao->deleteConversions($visitIds);
    }

    public function deleteConversionItems($visitIds)
    {
        return $this->rawLogDao->deleteConversionItems($visitIds);
    }

    public function deleteActions($actionIds)
    {
        // TODO (should this cascade as well? what about existing references? revisit when refactoring log purging class)
    }
}