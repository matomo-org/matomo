<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Diagnostics\Test\Mock;

use Piwik\Plugins\Diagnostics\Diagnostic\Diagnostic;
use Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResult;

class DiagnosticWithWarning implements Diagnostic
{
    public function execute()
    {
        return array(
            DiagnosticResult::singleResult('Warning', DiagnosticResult::STATUS_WARNING, 'Comment'),
        );
    }
}
