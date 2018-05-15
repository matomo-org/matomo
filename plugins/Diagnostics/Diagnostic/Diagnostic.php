<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Diagnostics\Diagnostic;

/**
 * Performs a diagnostic on the system or Piwik.
 *
 * Example:
 *
 *     class MyDiagnostic implements Diagnostic
 *     {
 *         public function execute()
 *         {
 *             $results = array();
 *
 *             // First check (error)
 *             $status = testSomethingIsOk() ? DiagnosticResult::STATUS_OK : DiagnosticResult::STATUS_ERROR;
 *             $results[] = DiagnosticResult::singleResult('First check', $status);
 *
 *             // Second check (warning)
 *             $status = testSomethingElseIsOk() ? DiagnosticResult::STATUS_OK : DiagnosticResult::STATUS_WARNING;
 *             $results[] = DiagnosticResult::singleResult('Second check', $status);
 *
 *             return $results;
 *         }
 *     }
 *
 * Diagnostics are loaded with dependency injection support.
 *
 * @api
 */
interface Diagnostic
{
    /**
     * @return DiagnosticResult[]
     */
    public function execute();
}
