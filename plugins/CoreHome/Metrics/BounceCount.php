<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\CoreHome\Metrics;

use Piwik\DataTable\Row;
use Piwik\Plugin\Metric;

/**
 * TODO
 */
class BounceCount extends Metric
{
    public function getName()
    {
        return 'bounce_count';
    }
}