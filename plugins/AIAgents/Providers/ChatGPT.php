<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIAgents\Providers;

use Piwik\Request as PiwikRequest;
use Piwik\Tracker\Request;

class ChatGPT extends AgentAbstract
{
    public const HEADER_SIGNATURE       = 'HTTP_SIGNATURE';
    public const HEADER_SIGNATURE_AGENT = 'HTTP_SIGNATURE_AGENT';
    public const HEADER_SIGNATURE_INPUT = 'HTTP_SIGNATURE_INPUT';

    public const PARAM_SIGNATURE       = 'ai_s';
    public const PARAM_SIGNATURE_AGENT = 'ai_sa';
    public const PARAM_SIGNATURE_INPUT = 'ai_si';

    public function getId(): string
    {
        return 'ChatGPT';
    }

    public function isDetectedForTrackerRequest(Request $trackerRequest): bool
    {
        // cannot use \Piwik\Tracker\Request::getParam() for the custom parameters
        $matomoRequest = new PiwikRequest($trackerRequest->getParams());

        $signature = $matomoRequest->getStringParameter(
            self::PARAM_SIGNATURE,
            $_SERVER[self::HEADER_SIGNATURE] ?? ''
        );

        $signatureAgent = $matomoRequest->getStringParameter(
            self::PARAM_SIGNATURE_AGENT,
            $_SERVER[self::HEADER_SIGNATURE_AGENT] ?? ''
        );

        $signatureInput = $matomoRequest->getStringParameter(
            self::PARAM_SIGNATURE_INPUT,
            $_SERVER[self::HEADER_SIGNATURE_INPUT] ?? ''
        );

        return (
            '' !== $signature
            && '' !== $signatureInput
            // the value of the Signature-Agent header is wrapped in double quotes!
            && '"https://chatgpt.com"' === $signatureAgent
        );
    }
}
