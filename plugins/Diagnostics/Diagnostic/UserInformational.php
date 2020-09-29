<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\Common;
use Piwik\Translation\Translator;

/**
 * Information about the current user.
 */
class UserInformational implements Diagnostic
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

        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            $results[] = DiagnosticResult::informationalResult('User Agent', $_SERVER['HTTP_USER_AGENT']);
        }

        $results[] = DiagnosticResult::informationalResult('Browser Language', Common::getBrowserLanguage());

        return $results;
    }

}
