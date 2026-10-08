<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIProviders\Exception;

use Piwik\Plugins\AIProviders\AIRequestDecision;

/**
 * Thrown before a provider call when a listener of `AIProviders.beforeRequest`
 * denied it, for example because an AI usage limit is reached. Nothing was
 * sent to the provider. {@link getDecision()} carries the reason and the
 * usage figures for an upgrade message.
 */
class AIQuotaExceededException extends AIProviderClientException
{
    /**
     * @var AIRequestDecision
     */
    private $decision;

    public function __construct(string $message, AIRequestDecision $decision)
    {
        parent::__construct($message);

        $this->decision = $decision;
    }

    public function getDecision(): AIRequestDecision
    {
        return $this->decision;
    }
}
