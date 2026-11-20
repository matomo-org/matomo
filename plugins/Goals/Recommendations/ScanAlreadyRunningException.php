<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Goals\Recommendations;

use Piwik\Http\HttpCodeException;
use Piwik\Piwik;

class ScanAlreadyRunningException extends \Exception implements HttpCodeException
{
    public function __construct()
    {
        parent::__construct(Piwik::translate('Goals_RecommendScanAlreadyRunning'), 429);
    }
}
