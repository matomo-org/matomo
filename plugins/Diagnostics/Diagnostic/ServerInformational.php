<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\Translation\Translator;

/**
 * Informatation about the server.
 */
class ServerInformational implements Diagnostic
{
    /**
     * @var Translator
     */
    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function execute()
    {
        $results = [];

        if ( ! empty( $_SERVER['SERVER_SOFTWARE'] ) ) {
            $results[] = DiagnosticResult::informationalResult('Server Info', $_SERVER['SERVER_SOFTWARE']);
        }

        return $results;
    }

}
