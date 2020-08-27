<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SEO\Metric;

/**
 * Provides SEO metrics for a domain.
 */
interface MetricsProvider
{
    /**
     * @param string $domain
     * @return Metric[]
     */
    public function getMetrics($domain);
}
