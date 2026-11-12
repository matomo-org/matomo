<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\Transitions\PreprocessedPages;

use Piwik\Http\HttpCodeException;
use Piwik\Piwik;

/**
 * Another change to the same website's pre-processed pages held the lock for too long.
 */
class PreprocessedPagesBusyException extends \Exception implements HttpCodeException
{
    public function __construct()
    {
        parent::__construct(Piwik::translate('Transitions_PreprocessedPagesBusy'), 409);
    }
}
