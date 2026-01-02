<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\tests\Fixtures;

use Piwik\Date;
use Piwik\Tests\Framework\Fixture;

class BotTraffic extends Fixture
{
    public $dateTime = '2025-02-02 03:00:00';
    public $idSite = 1;

    public function setUp(): void
    {
        parent::setUp();

        self::createSuperUser();
        $this->setUpWebsite();
        $this->trackBotRequests();
        $this->trackAcquiredVisits();
    }

    public function tearDown(): void
    {
        // nothing to clean up
    }

    private function setUpWebsite(): void
    {
        if (!self::siteCreated($this->idSite)) {
            self::createWebsite($this->dateTime, 1, 'https://example.com');
        }
    }

    private function trackBotRequests(): void
    {
        $pages = [
            'https://example.com/article/1/page/1',
            'https://example.com/article/1/page/2',
            'https://example.com/article/2',
            'https://example.com/article/3',
            'https://example.com/article/4/page/1',
            'https://example.com/article/4/page/2',
            'https://example.com/overview',
        ];

        $downloads = [
            'https://example.com/resources/doc.pdf',
            'https://example.com/resources/guide.pdf',
            'https://example.com/resources/whitepaper.pdf',
            'https://example.com/resources/datasheet.pdf',
            'https://example.com/resources/case-study.pdf',
        ];

        $dailyPlans = [
            0 => [
                ['ChatGPT-User/1.0', $pages[0], 200, 12005, false],
                ['Gemini-Deep-Research/1.0', $pages[1], 200, 29658, false],
                ['Perplexity-User/1.0', $downloads[0], 503, 1365955, true],
                ['Google-NotebookLM/1.0', $downloads[1], 404, 36522, true],
                ['ChatGPT-User/1.0', $pages[0], 200, 12584, false],
                ['Gemini-Deep-Research/1.0', $pages[1], 200, 36598, false],
                ['Perplexity-User/1.0', $downloads[0], 200, 99562, true],
                ['Google-NotebookLM/1.0', $pages[2], 200, 25489, false],
            ],
            1 => [
                ['MistralAI-User/2.0', $pages[2], 200, 32485, false],
                ['Claude-User/3.0', $downloads[2], 200, 123456, true],
                ['ChatGPT-User/1.0', $pages[1], 500, 25896, false],
                ['ChatGPT-User/1.0', $downloads[1], 200, 33658, true],
                ['Perplexity-User/1.0', $pages[2], 200, 36985, false],
                ['Perplexity-User/1.0', $pages[2], 200, 36985, false],
                ['MistralAI-User/2.0', $pages[3], 200, 85236, false],
                ['Claude-User/3.0', $downloads[3], 200, 12456, true],
                ['Claude-User/3.0', $downloads[4], 200, 35562, true],
            ],
            2 => [
                ['Perplexity-User/1.0', $downloads[3], 200, 84269, true],
                ['Gemini-Deep-Research/1.0', $pages[3], 200, 3265, false],
                ['Devin/1.0', $pages[6], 200, 33366, false],
                ['ChatGPT-User/1.0', $pages[3], 200, 5454, false],
                ['Perplexity-User/1.0', $downloads[2], 200, 69856, true],
                ['Gemini-Deep-Research/1.0', $pages[4], 200, 63256, false],
                ['Devin/1.0', $pages[6], 200, 25486, false],
            ],
            3 => [
                ['MistralAI-User/2.0', $pages[4], 200, 12568, false],
                ['Google-NotebookLM/1.0', $downloads[4], 404, 25648, true],
                ['ChatGPT-User/1.0', $pages[4], 200, 12548, false],
                ['Claude-User/3.0', $pages[5], 503, 36598, false],
                ['Perplexity-User/1.0', $downloads[0], 200, 225445, true],
                ['MistralAI-User/2.0', $pages[2], 200, 12456, false],
                ['Google-NotebookLM/1.0', $downloads[1], 200, 258741, true],
            ],
            4 => [
                ['Perplexity-User/1.0', $downloads[1], 200, 36985, true],
                ['Gemini-Deep-Research/1.0', $pages[5], 200, 95147, false],
                ['ChatGPT-User/1.0', $pages[0], 200, 25412, false],
                ['Claude-User/3.0', $pages[3], 200, 36985, false],
                ['Perplexity-User/1.0', $downloads[4], 200, 145811, true],
            ],
        ];

        foreach ($dailyPlans as $dayOffset => $requests) {
            foreach ($requests as $index => $request) {
                [$userAgent, $url, $status, $bytes, $isDownload] = $request;
                $date = Date::factory($this->dateTime)
                    ->addDay($dayOffset)
                    ->addHour(($index + 1) * 2)
                    ->getDatetime();

                if ($isDownload) {
                    $this->logBotDownload($userAgent, $url, $status, $bytes, $date);
                } else {
                    $this->logBot($userAgent, $url, $status, $bytes, $date);
                }
            }
        }
    }

    private function trackAcquiredVisits(): void
    {
        $sources = [
            'https://chatgpt.com/thread/12345',
            'https://perplexity.ai/share/6789',
            'https://copilot.microsoft.com/answer/abc',
            'https://claude.ai/share/987',
            'https://gemini.google.com/share/notes',
            'https://chat.qwen.ai/share/insight',
            'https://chatgpt.com/thread/8888',
            'https://perplexity.ai/share/2222',
            'https://copilot.microsoft.com/answer/xyz',
            'https://claude.ai/share/1111',
        ];

        foreach ($sources as $index => $referrer) {
            $date = Date::factory($this->dateTime)
                ->addDay($index % 5)
                ->addHour(($index % 4) * 3)
                ->getDatetime();
            $tracker = self::getTracker($this->idSite, $date, true);
            $tracker->setUrl('https://example.com/article-' . (($index % 4) + 1));
            $tracker->setUrlReferrer($referrer);
            self::checkResponse($tracker->doTrackPageView('Article From AI Assistant ' . ($index + 1)));
        }
    }

    private function logBot(string $userAgent, string $url, int $statusCode, int $bytes, string $dateTime): void
    {
        $tracker = self::getTracker($this->idSite, $dateTime, true);
        $tracker->setUserAgent($userAgent);
        $tracker->setUrl($url);
        $tracker->setCustomTrackingParameter('recMode', '1');
        $tracker->setCustomTrackingParameter('http_status', (string) $statusCode);
        $tracker->setCustomTrackingParameter('bw_bytes', (string) $bytes);
        self::checkResponse($tracker->doTrackPageView(''));
    }

    private function logBotDownload(string $userAgent, string $url, int $statusCode, int $bytes, string $dateTime): void
    {
        $tracker = self::getTracker($this->idSite, $dateTime, true);
        $tracker->setUserAgent($userAgent);
        $tracker->setCustomTrackingParameter('recMode', '1');
        $tracker->setCustomTrackingParameter('http_status', (string) $statusCode);
        $tracker->setCustomTrackingParameter('bw_bytes', (string) $bytes);
        self::checkResponse($tracker->doTrackAction($url, 'download'));
    }
}
