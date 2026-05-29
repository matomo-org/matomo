<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Piwik\Plugins\BotTracking\tests\Fixtures;

use MatomoTracker;
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

        // Each tuple: [userAgent, url, httpStatus, bytes|null, isDownload, serverTimeMs|null]
        //
        // - serverTimeMs: integer milliseconds passed as `pf_srv` to the tracker, or null to omit
        //   the parameter entirely (leaving response_time_ms as SQL NULL in log_bot_request).
        //   At least a handful of requests intentionally omit server time so that
        //   nb_server_time < requests for some URLs, exercising the avg computation guard.
        //
        // - bytes: integer bytes passed as `bw_bytes`, or null to omit the parameter entirely
        //   (leaving response_size_bytes as SQL NULL). A couple of tuples use null bytes to produce
        //   at least one URL where nb_response_size < requests.
        $dailyPlans = [
            0 => [
                ['ChatGPT-User/1.0', $pages[0], 200, 12005, false, 250],
                ['Gemini-Deep-Research/1.0', $pages[1], 200, 29658, false, 1500],
                ['Perplexity-User/1.0', $downloads[0], 503, 1365955, true, 3200],
                ['Google-NotebookLM/1.0', $downloads[1], 404, 36522, true, null],
                ['ChatGPT-User/1.0', $pages[0], 200, 12584, false, 180],
                ['Gemini-Deep-Research/1.0', $pages[1], 200, 36598, false, 950],
                ['Perplexity-User/1.0', $downloads[0], 200, 99562, true, null],
                ['Google-NotebookLM/1.0', $pages[2], 200, 25489, false, 2100],
            ],
            1 => [
                // Extra broken entries placed early so their hour offset stays within Feb 3
                // (each tuple index N maps to addHour((N+1)*2); N≥9 would spill into Feb 4).
                // doc.pdf 404 and guide.pdf 503 ensure ranking_limit tests (limit=2) produce
                // an "Others" row for the Broken report on this day.
                ['MistralAI-User/2.0', $downloads[0], 404, null, true, null],
                ['Perplexity-User/1.0', $downloads[1], 503, null, true, 150],
                ['MistralAI-User/2.0', $pages[2], 200, 32485, false, 1800],
                ['Claude-User/3.0', $downloads[2], 200, 123456, true, 4500],
                ['ChatGPT-User/1.0', $pages[1], 500, null, false, 320],
                // Same URL as the 500 above but a 200 — proves total_broken_requests = 1 (not COUNT=2) after F18 fix
                ['Gemini-Deep-Research/1.0', $pages[1], 200, 29658, false, 1200],
                ['ChatGPT-User/1.0', $downloads[1], 200, 33658, true, 750],
                ['Perplexity-User/1.0', $pages[2], 200, 36985, false, 2800],
                ['Perplexity-User/1.0', $pages[2], 200, 36985, false, 3100],
                ['MistralAI-User/2.0', $pages[3], 200, 85236, false, null],
                ['Claude-User/3.0', $downloads[3], 200, 12456, true, 600],
                ['Claude-User/3.0', $downloads[4], 200, 35562, true, 1200],
            ],
            2 => [
                ['Perplexity-User/1.0', $downloads[3], 200, 84269, true, 900],
                ['Gemini-Deep-Research/1.0', $pages[3], 200, 3265, false, 400],
                ['Google-NotebookLM/1.0', $pages[6], 200, 33366, false, 1100],
                ['ChatGPT-User/1.0', $pages[3], 200, 5454, false, null],
                ['Perplexity-User/1.0', $downloads[2], 200, 69856, true, 2200],
                ['Gemini-Deep-Research/1.0', $pages[4], 200, 63256, false, 3800],
                ['Google-NotebookLM/1.0', $pages[6], 200, 25486, false, 850],
            ],
            3 => [
                ['MistralAI-User/2.0', $pages[4], 200, 12568, false, 1400],
                ['Google-NotebookLM/1.0', $downloads[4], 404, 25648, true, null],
                ['ChatGPT-User/1.0', $pages[4], 200, 12548, false, 670],
                ['Claude-User/3.0', $pages[5], 503, null, false, 200],
                ['Perplexity-User/1.0', $downloads[0], 200, 225445, true, 5000],
                ['MistralAI-User/2.0', $pages[2], 200, 12456, false, 1650],
                ['Google-NotebookLM/1.0', $downloads[1], 200, 258741, true, 2900],
            ],
            4 => [
                ['Perplexity-User/1.0', $downloads[1], 200, 36985, true, 480],
                ['Gemini-Deep-Research/1.0', $pages[5], 200, 95147, false, 3300],
                ['ChatGPT-User/1.0', $pages[0], 200, 25412, false, 210],
                ['Claude-User/3.0', $pages[3], 200, 36985, false, null],
                ['Perplexity-User/1.0', $downloads[4], 200, 145811, true, 1700],
            ],
        ];

        foreach ($dailyPlans as $dayOffset => $requests) {
            foreach ($requests as $index => $request) {
                [$userAgent, $url, $status, $bytes, $isDownload, $serverTimeMs] = $request;
                $date = Date::factory($this->dateTime)
                    ->addDay($dayOffset)
                    ->addHour(($index + 1) * 2)
                    ->getDatetime();

                if ($isDownload) {
                    $this->logBotDownload($userAgent, $url, $status, $bytes, $date, $serverTimeMs);
                } else {
                    $this->logBot($userAgent, $url, $status, $bytes, $date, $serverTimeMs);
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
            self::checkResponse($tracker->doTrackPageView('Article From AI Chatbot ' . ($index + 1)));
        }
    }

    private function logBot(string $userAgent, string $url, int $statusCode, ?int $bytes, string $dateTime, ?int $serverTimeMs): void
    {
        $tracker = self::getTracker($this->idSite, $dateTime, true);
        $tracker->setUrl($url);
        $this->applyCommonTrackingParameters($tracker, $userAgent, $statusCode, $bytes, $serverTimeMs);
        self::checkResponse($tracker->doTrackPageView(''));
    }

    private function logBotDownload(string $userAgent, string $url, int $statusCode, ?int $bytes, string $dateTime, ?int $serverTimeMs): void
    {
        $tracker = self::getTracker($this->idSite, $dateTime, true);
        $this->applyCommonTrackingParameters($tracker, $userAgent, $statusCode, $bytes, $serverTimeMs);
        self::checkResponse($tracker->doTrackAction($url, 'download'));
    }

    /**
     * Applies the tracking parameters shared between page and download bot requests:
     * user-agent, recMode flag, http_status, and the optional bw_bytes / pf_srv parameters.
     *
     * When $bytes or $serverTimeMs is null the corresponding parameter is intentionally omitted
     * so that response_size_bytes / response_time_ms stores SQL NULL in log_bot_request —
     * this exercises nb_response_size < requests and nb_server_time < requests in the averages guard.
     */
    private function applyCommonTrackingParameters(MatomoTracker $tracker, string $userAgent, int $statusCode, ?int $bytes, ?int $serverTimeMs): void
    {
        $tracker->setUserAgent($userAgent);
        $tracker->setCustomTrackingParameter('recMode', '1');
        $tracker->setCustomTrackingParameter('http_status', (string) $statusCode);
        if ($bytes !== null) {
            $tracker->setCustomTrackingParameter('bw_bytes', (string) $bytes);
        }
        if ($serverTimeMs !== null) {
            $tracker->setCustomTrackingParameter('pf_srv', (string) $serverTimeMs);
        }
    }
}
