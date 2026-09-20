<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\ExamplePlugin\Diagnostic;

use Piwik\Plugins\Diagnostics\Diagnostic\Diagnostic;
use Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResult;

class ExampleCheck implements Diagnostic
{
    /**
     * @return DiagnosticResult[]
     */
    public function execute()
    {
        $status = DiagnosticResult::STATUS_OK; // can be ok, error, warning or informational

        return [
            DiagnosticResult::singleResult('Example Check', $status, 'A comment for this check'),
            DiagnosticResult::informationalResult('Example Information', 'The PHP version is ' . PHP_VERSION),
        ];
    }
}
