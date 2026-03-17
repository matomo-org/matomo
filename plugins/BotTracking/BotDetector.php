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
 * Detects AI chatbots and bots based on User-Agent patterns
 */
class BotDetector
{
    public const BOT_TYPE_AI_CHATBOT = 'ai_chatbot';

    /** @var null|array{bot_name: string, bot_type: string} */
    private $detectionResult;

    /**
     * AI Chatbot/Bot User-Agent patterns to detect
     *
     * @var array<string, string>
     */
    private $aiAssistantPatterns = [
        'ChatGPT-User'         => self::BOT_TYPE_AI_CHATBOT,
        'MistralAI-User'       => self::BOT_TYPE_AI_CHATBOT,
        'Gemini-Deep-Research' => self::BOT_TYPE_AI_CHATBOT,
        'Claude-User'          => self::BOT_TYPE_AI_CHATBOT,
        'Perplexity-User'      => self::BOT_TYPE_AI_CHATBOT,
        'Google-NotebookLM'    => self::BOT_TYPE_AI_CHATBOT,
    ];

    public function __construct(string $userAgent)
    {
        $this->detectionResult = $this->detect($userAgent);
    }

    /**
     * Detect if the User-Agent represents an AI chatbot or bot
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
