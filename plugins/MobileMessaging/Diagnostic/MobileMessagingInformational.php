<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\MobileMessaging\Diagnostic;

use Piwik\Plugins\Diagnostics\Diagnostic\Diagnostic;
use Piwik\Plugins\Diagnostics\Diagnostic\DiagnosticResult;
use Piwik\Plugins\MobileMessaging\API;
use Piwik\SettingsPiwik;
use Piwik\Translation\Translator;

/**
 * Information about Matomo itself
 */
class MobileMessagingInformational implements Diagnostic
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
        if (SettingsPiwik::isMatomoInstalled()) {
            $provider = API::getInstance()->getSMSProvider();

            $label = 'Mobile Messaging SMS Provider';

            if (empty($provider)) {
                return [DiagnosticResult::informationalResult($label, 'not configured')];
            }

            try {
                $creditsLeft = API::getInstance()->getCreditLeft();
                return [DiagnosticResult::informationalResult(
                    $label,
                    sprintf('%s (%s credits left)', $provider, $creditsLeft)
                )];
            } catch (\Exception $e) {
                return [DiagnosticResult::singleResult(
                    $label,
                    DiagnosticResult::STATUS_ERROR,
                    sprintf('%s<br /><b>Communication error:</b> %s', $provider, $e->getMessage())
                )];
            }
        }
        return [];
    }
}
