<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\API;

/**
 * Contains logic to replicate inconsistencies in Piwik's API. This class exists
 * to provide a way to clean up existing Piwik code and behavior without breaking
 * backwards compatibility immediately.
 *
 * Code that handles the case when the 'format_metrics' query parameter value is
 * 'bc' should be removed as well. This code is in API\Request and DataTablePostProcessor.
 *
 * Should be removed before releasing Piwik 3.0.
 */
class Inconsistencies
{
    /**
     * In Piwik 2.X and below, the "raw" API would format percent values but no others.
     * This method returns the list of percent metrics that were returned from the API
     * formatted so we can maintain BC.
     *
     * Used by DataTablePostProcessor.
     */
    public function getPercentMetricsToFormat()
    {
        return array(
            'bounce_rate',
            'conversion_rate',
            'abandoned_rate',
            'interaction_rate',
            'exit_rate',
            'bounce_rate_returning',
            'nb_visits_percentage',
            '/.*_evolution/',
            '/step_.*_rate/',
            '/funnel_.*_rate/',
            '/form_.*_rate/',
            '/field_.*_rate/',
            '/Referrers.*_percent/',
        );
    }
}
