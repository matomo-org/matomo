<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Unit\PHPStan\Rules\data;

use Piwik\DataTable\Renderer\Json;
use Piwik\Http\JsonResponse;
use Piwik\Plugin\Controller;

/**
 * Fixture controller exercising every #[JsonResponse] rule scenario. It is never executed; the
 * PHPStan rule tests analyse this file's source.
 */
class JsonResponseController extends Controller
{
    public function unconditionalManualCall()
    {
        Json::sendHeaderJSON();
        return json_encode([]);
    }

    public function conditionalManualCall()
    {
        if (self::class !== '') {
            Json::sendHeaderJSON();
            return json_encode([]);
        }

        return 'not json';
    }

    public function echoAndExit()
    {
        Json::sendHeaderJSON();
        echo json_encode([]);
        exit;
    }

    #[JsonResponse]
    public function redundantManualCall(): string
    {
        Json::sendHeaderJSON();
        return json_encode([]);
    }

    #[JsonResponse]
    protected function notPublic(): string
    {
        return json_encode([]);
    }

    #[JsonResponse]
    public function missingReturnType()
    {
        return json_encode([]);
    }

    #[JsonResponse]
    public function wrongReturnType(): void
    {
        echo json_encode([]);
    }

    #[JsonResponse]
    public function properlyConverted(): string
    {
        return json_encode([]);
    }

    #[JsonResponse]
    public function exitsEarly(): string
    {
        echo json_encode([]);
        exit;
    }

    // exit / sendHeaderJSON() inside a nested closure run in a separate frame and must NOT be
    // flagged: this method returns JSON normally, so the attribute handling still applies.
    #[JsonResponse]
    public function nestedScopesAreIgnored(): string
    {
        $shutdown = function () {
            Json::sendHeaderJSON();
            exit;
        };
        unset($shutdown);

        return json_encode([]);
    }

    public function rawJsonContentTypeHeader(): string
    {
        \Piwik\Common::sendHeader('Content-Type: application/json; charset=utf-8');
        return json_encode([]);
    }

    // unconditional JSON returns without the attribute must be flagged (JsonReturnRequiresAttribute)
    public function undeclaredJsonEncodeReturn()
    {
        return json_encode(['ok' => true]);
    }

    public function undeclaredJsonEncodeCastReturn()
    {
        return (string) json_encode(['ok' => true]);
    }

    public function undeclaredJsonLiteralReturn()
    {
        return '{"ok":true}';
    }

    // returns JSON on one path and HTML on another: mixing is forbidden, so it must be flagged
    public function conditionalJsonReturn(): string
    {
        if (self::class !== '') {
            return json_encode(['ok' => true]);
        }

        return '<div>not json</div>';
    }

    // E4: returns JSON unconditionally but only sets the header conditionally -> must be flagged
    public function conditionalHeaderUnconditionalJson(): string
    {
        if (self::class !== '') {
            Json::sendHeaderJSON();
        }

        return json_encode(['ok' => true]);
    }

    // HTML on one path, JSON on another: mixing is forbidden, so it must be flagged
    public function mixedHtmlAndJsonReturn(): string
    {
        if (self::class !== '') {
            return '<p>html</p>';
        }

        return json_encode(['ok' => true]);
    }

    // C3: a non-JSON literal that merely starts with a JSON keyword -> must NOT be flagged
    public function nonJsonLiteralIsIgnored(): string
    {
        return 'true story, not json';
    }

    // isPublic gate: non-public helpers are not dispatchable actions -> must NOT be flagged
    private function privateJsonHelper(): string
    {
        return json_encode(['ok' => true]);
    }

    protected function protectedSendsHeader(): string
    {
        Json::sendHeaderJSON();
        return json_encode(['ok' => true]);
    }

    // E1: a non-Content-Type header that merely contains "json" -> must NOT be flagged
    public function nonJsonContentTypeHeaderIsIgnored(): string
    {
        \Piwik\Common::sendHeader('X-Content-Type: application/json');
        return 'plain text';
    }

    // E3: attribute already present + raw header -> flagged with the "redundant" message
    #[JsonResponse]
    public function attributedRawHeader(): string
    {
        \Piwik\Common::sendHeader('Content-Type: application/json; charset=utf-8');
        return json_encode([]);
    }

    // D: emitting output before returning -> must be flagged (echo and flush)
    #[JsonResponse]
    public function emitsOutput(): string
    {
        echo 'partial';
        flush();

        return json_encode([]);
    }
}
