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
}
