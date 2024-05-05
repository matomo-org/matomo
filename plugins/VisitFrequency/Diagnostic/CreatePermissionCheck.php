<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\VisitFrequency\Diagnostic;

use Piwik\Db;
use Piwik\Config;
use Piwik\Plugins\Diagnostics\Diagnostic\Diagnostic;
use Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResult;
use Piwik\SettingsPiwik;
use Piwik\Translation\Translator;

/**
 * Check if the database user has been granted the create permission
 */
class CreatePErmissionCheck implements Diagnostic
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
        $result = [];

        if (SettingsPiwik::isMatomoInstalled()) {
            $db = Db::get();

            if (method_exists($db, 'hasCreatePrivilege')) {

                $label = $this->translator->translate('Database user has been granted the create permission');

                $createStatus = $db->hasCreatePrivilege();

                $comment = '';
                if ($createStatus) {
                    $status = DiagnosticResult::STATUS_OK;
                } else {
                    $status = DiagnosticResult::STATUS_WARNING;
                    $comment = 'not permitted';
                }

                return [DiagnosticResult::singleResult($label, $status, $comment)];
            }
        }

        return $result;
    }
}
