<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
namespace Piwik\Plugins\Diagnostics\Diagnostic;

use Piwik\Config;
use Piwik\Http;
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
            return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_OK));
        }

        $comment = $this->translator->translate('Installation_SystemCheckPageSpeedWarn', array(
            '(eg. Apache, Nginx or IIS)',
        ));

        return array(DiagnosticResult::singleResult($label, DiagnosticResult::STATUS_WARNING, $comment));
    }

    private function isPageSpeedEnabled()
    {
        $url = Url::getCurrentUrlWithoutQueryString() . '?module=Installation&action=getEmptyPageForSystemCheck';

        try {
            $page = Http::sendHttpRequest($url,
                $timeout = 1,
                $userAgent = null,
                $destinationPath = null,
                $followDepth = 0,
                $acceptLanguage = false,
                $byteRange = false,

                // Return headers
                $getExtendedInfo = true
            );
        } catch(\Exception $e) {
            $this->logger->info('Unable to test if mod_pagespeed is enabled: the request to {url} failed', array(
                'url' => $url,
            ));
            // If the test failed, we assume Page speed is not enabled
            return false;
        }

        $headers = $page['headers'];

        return isset($headers['X-Mod-Pagespeed']) || isset($headers['X-Page-Speed']);
    }
}
