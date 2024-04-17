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
    public function execute()
    {
        $result = [];

        $label = 'Example Check';
        $status = DiagnosticResult::STATUS_OK; // can be ok, error, warning or informational
        $comment = 'A comment for this check';
        $result[] = DiagnosticResult::singleResult($label, $status, $comment);

        $label = 'Example Information';
        $comment = 'The PHP version is ' . PHP_VERSION;
        $result[] = DiagnosticResult::informationalResult($label, $status, $comment);

        return $result;
    }
}
