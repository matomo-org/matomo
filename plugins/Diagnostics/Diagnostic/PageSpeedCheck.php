<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\Http;
use Piwik\SettingsPiwik;
use Piwik\Translation\Translator;
use Piwik\Url;
use Psr\Log\LoggerInterface;

/**
 * Check that mod_pagespeed is not enabled.
 */
class PageSpeedCheck implements Diagnostic
{
    /**
     * @var Translator
     */
    private $translator;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(Translator $translator, LoggerInterface $logger)
    {
        $this->translator = $translator;
        $this->logger = $logger;
    }

    public function execute()
    {
        $label = $this->translator->translate('Installation_SystemCheckPageSpeedDisabled');

        if (! $this->isPageSpeedEnabled()) {
            return [DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_OK)];
        }

        $comment = $this->translator->translate('Installation_SystemCheckPageSpeedWarning', [
            '(eg. Apache, Nginx or IIS)',
        ]);

        return [DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_WARNING, $comment)];
    }

    private function isPageSpeedEnabled()
    {
        try {
            $matomoUrl = SettingsPiwik::getPiwikUrl();
        } catch (\Exception $e) {
            $matomoUrl = Url::getCurrentUrlWithoutQueryString();
        }

        if (empty($matomoUrl)) {
            // skip this check if we can't determine the matomo url (e.g. on command line)
            return false;
        }

        $url = $matomoUrl . '?module=Installation&action=getEmptyPageForSystemCheck';

        try {
            $page = Http::sendHttpRequest(
                $url,
                $timeout = 1,
                $userAgent = null,
                $destinationPath = null,
                $followDepth = 0,
                $acceptLanguage = false,
                $byteRange = false,
                // Return headers
                $getExtendedInfo = true
            );
        } catch (\Exception $e) {
            $this->logger->info('Unable to test if mod_pagespeed is enabled: the request to {url} failed', [
                'url' => $url,
            ]);
            // If the test failed, we assume Page speed is not enabled
            return false;
        }

        return isset($page['headers']['X-Mod-Pagespeed']) || isset($page['headers']['X-Page-Speed']);
    }
}
