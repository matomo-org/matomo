<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking;

/**
 * Detects AI assistants and bots based on User-Agent patterns
 */
class BotDetector
{
    /** @var null|array{bot_name: string, bot_type: string} */
    private $detectionResult;

    /**
     * AI Assistant/Bot User-Agent patterns to detect
     *
     * @var array<string, string>
     */
    private $aiAssistantPatterns = [
        'ChatGPT-User'         => 'ai_assistant',
        'MistralAI-User'       => 'ai_assistant',
        'Gemini-Deep-Research' => 'ai_assistant',
        'Claude-User'          => 'ai_assistant',
        'Perplexity-User'      => 'ai_assistant',
        'Google-NotebookLM'    => 'ai_assistant',
        'Devin'                => 'ai_assistant',
    ];

    public function __construct(string $userAgent)
    {
        $this->detectionResult = $this->detect($userAgent);
    }

    /**
     * Detect if the User-Agent represents an AI assistant or bot
     *
     * @param string $userAgent The User-Agent string to check
     * @return null|array{bot_name: string, bot_type: string} Returns array if detected, null otherwise
     */
    private function detect(string $userAgent): ?array
    {
        if (empty($userAgent)) {
            return null;
        }

        foreach ($this->aiAssistantPatterns as $pattern => $botType) {
            if (stripos($userAgent, $pattern) !== false) {
                return [
                    'bot_name' => $pattern,
                    'bot_type' => $botType,
                ];
            }
        }

        return null;
    }

    public function isBot(): bool
    {
        return $this->detectionResult !== null;
    }

    /**
     * @return null|array{'bot_name': string, 'bot_type': string}
     */
    public function getDetectionResult(): ?array
    {
        return $this->detectionResult;
    }
}
