<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Referrers\DataTable\Filter;

use Piwik\DataTable\BaseFilter;
use Piwik\DataTable;

class UrlsForAIAssistant extends BaseFilter
{
    /**
     * @param DataTable $table
     */
    public function filter($table)
    {
        // make url labels clickable
        $table->filter('ColumnCallbackAddMetadata', array('label', 'url'));

        // prettify the DataTable
        $table->filter('ColumnCallbackReplace', array('label', 'Piwik\Plugins\Referrers\removeUrlProtocol'));
        $table->queueFilter('ReplaceColumnNames');
    }
}
