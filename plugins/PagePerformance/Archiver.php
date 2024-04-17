<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\PagePerformance;

/**
 * Class Archiver
 */
class Archiver extends \Piwik\Plugin\Archiver
{
    const PAGEPERFORMANCE_TOTAL_NETWORK_TIME = 'PagePerformance_network_time';
    const PAGEPERFORMANCE_TOTAL_NETWORK_HITS = 'PagePerformance_network_hits';
    const PAGEPERFORMANCE_TOTAL_SERVER_TIME = 'PagePerformance_servery_time';
    const PAGEPERFORMANCE_TOTAL_SERVER_HITS = 'PagePerformance_server_hits';
    const PAGEPERFORMANCE_TOTAL_TRANSFER_TIME = 'PagePerformance_transfer_time';
    const PAGEPERFORMANCE_TOTAL_TRANSFER_HITS = 'PagePerformance_transfer_hits';
    const PAGEPERFORMANCE_TOTAL_DOMPROCESSING_TIME = 'PagePerformance_domprocessing_time';
    const PAGEPERFORMANCE_TOTAL_DOMPROCESSING_HITS = 'PagePerformance_domprocessing_hits';
    const PAGEPERFORMANCE_TOTAL_DOMCOMPLETION_TIME = 'PagePerformance_domcompletion_time';
    const PAGEPERFORMANCE_TOTAL_DOMCOMPLETION_HITS = 'PagePerformance_domcompletion_hits';
    const PAGEPERFORMANCE_TOTAL_ONLOAD_TIME = 'PagePerformance_onload_time';
    const PAGEPERFORMANCE_TOTAL_ONLOAD_HITS = 'PagePerformance_onload_hits';
    const PAGEPERFORMANCE_TOTAL_PAGE_LOAD_TIME = 'PagePerformance_pageload_time';
    const PAGEPERFORMANCE_TOTAL_PAGE_LOAD_HITS = 'PagePerformance_pageload_hits';
}
