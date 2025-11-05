<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\AIAgents\tests\Unit\Providers;

use PHPUnit\Framework\TestCase;
use Piwik\Plugins\AIAgents\Providers\ChatGPT;
use Piwik\Tracker\Request;

/**
 * @group AIAgents
 * @group AIAgentsProviders
 * @group Plugins
 */
class ChatGPTTest extends TestCase
{
    /**
     * @var ChatGPT
     */
    private $chatGPT;

    public function setUp(): void
    {
        parent::setUp();

        $this->chatGPT = ChatGPT::getInstance();
    }

    /**
     * @dataProvider getIsDetectedForTrackerRequestTestData
     */
    public function testIsDetectedForTrackerRequestUsingHeaders(
        ?string $requestSignature,
        ?string $requestSignatureAgent,
        ?string $requestSignatureInput,
        bool $expectDetection
    ): void {
        try {
            $_SERVER[ChatGPT::HEADER_SIGNATURE]       = $requestSignature;
            $_SERVER[ChatGPT::HEADER_SIGNATURE_AGENT] = $requestSignatureAgent;
            $_SERVER[ChatGPT::HEADER_SIGNATURE_INPUT] = $requestSignatureInput;

            $this->assertIsDetectedForTrackerRequest(
                $expectDetection,
                ['ua' => 'mock user agent']
            );
        } finally {
            unset($_SERVER[ChatGPT::HEADER_SIGNATURE]);
            unset($_SERVER[ChatGPT::HEADER_SIGNATURE_AGENT]);
            unset($_SERVER[ChatGPT::HEADER_SIGNATURE_INPUT]);
        }
    }

    /**
     * @dataProvider getIsDetectedForTrackerRequestTestData
     */
    public function testIsDetectedForTrackerRequestUsingParameters(
        ?string $requestSignature,
        ?string $requestSignatureAgent,
        ?string $requestSignatureInput,
        bool $expectDetection
    ): void {
        $this->assertIsDetectedForTrackerRequest(
            $expectDetection,
            [
                'ua' => 'mock user agent',
                ChatGPT::PARAM_SIGNATURE       => $requestSignature,
                ChatGPT::PARAM_SIGNATURE_AGENT => $requestSignatureAgent,
                ChatGPT::PARAM_SIGNATURE_INPUT => $requestSignatureInput,
            ]
        );
    }

    public function getIsDetectedForTrackerRequestTestData(): iterable
    {
        yield 'missing Signature header'       => [null, 'sig-agent', 'sig-input', false];
        yield 'missing Signature Agent header' => ['sig', null, 'sig-input', false];
        yield 'missing Signature Input header' => ['sig', 'sig-agent', null, false];

        yield 'unhandled Signature Agent' => ['sig', 'sig-agent', 'sig-input', false];

        yield 'ChatGPT Agent' => [
            'Signature (value irrelevant)',
            '"https://chatgpt.com"',
            'Signature Input (value irrelevant)',
            true,
        ];
    }

    /**
     * @param array<string, string> $requestParams
     */
    private function assertIsDetectedForTrackerRequest(
        bool $expected,
        array $requestParams
    ): void {
        $request  = new Request($requestParams);
        $actual   = $this->chatGPT->isDetectedForTrackerRequest($request);

        self::assertSame($expected, $actual);
    }
}
