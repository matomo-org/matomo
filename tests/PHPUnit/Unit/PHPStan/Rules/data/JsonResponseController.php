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

    // conditional JSON return (HTML on the other path) is NOT top-level, so it must NOT be flagged
    public function conditionalJsonReturnIsIgnored(): string
    {
        if (self::class !== '') {
            return json_encode(['ok' => true]);
        }

        return '<div>not json</div>';
    }
}
