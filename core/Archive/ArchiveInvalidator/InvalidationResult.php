<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Archive\ArchiveInvalidator;

use Piwik\Date;

/**
 * Information about the result of an archive invalidation operation.
 */
class InvalidationResult
{
    /**
     * Dates that couldn't be invalidated because they are earlier than the configured log
     * deletion limit.
     *
     * @var array
     */
    public $warningDates = [];

    /**
     * Dates that were successfully invalidated.
     *
     * @var array
     */
    public $processedDates = [];

    /**
     * The day of the oldest log entry.
     *
     * @var Date|bool
     */
    public $minimumDateWithLogs = false;

    /**
     * @return string[]
     */
    public function makeOutputLogs(): array
    {
        $output = [];
        if ($this->warningDates) {
            $output[] = 'Warning: the following Dates have not been invalidated, because they are earlier than your Log Deletion limit: ' .
                implode(", ", $this->warningDates) .
                "\n The last day with logs is " . $this->minimumDateWithLogs . ". " .
                "\n Please disable 'Delete old Logs' or set it to a higher deletion threshold (eg. 180 days or 365 years).'.";
        }

        $output[] = "Success. The following dates were invalidated successfully: " . implode(", ", $this->processedDates);
        return $output;
    }
}
