<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Http;

use Attribute;

/**
 * Marks a controller action as always returning a JSON response.
 *
 * Sending the JSON `Content-Type` header from inside a controller action is fragile: the header is
 * not committed until the response body is finally echoed, so any code that runs afterwards while
 * the action is still building its response (most commonly a `Piwik\View::render()` call, which
 * defaults to `text/html`) silently overwrites it again.
 *
 * Applying this attribute to an action tells the {@see \Piwik\FrontController} to (re-)send the JSON
 * header once the action has fully returned, immediately before the output is written, so it wins
 * over anything the action did while building its response (such as a rendered `Piwik\View`).
 *
 * For this to hold, an action carrying the attribute must:
 *  - always return its JSON body as a string (typically `json_encode(...)`);
 *  - not send the `Content-Type` header itself;
 *  - not emit output (`echo`, `print`, `flush`) or `exit`/`die` before returning — doing so commits
 *    the response headers first, and `Common::sendHeader()` cannot change a header once
 *    `headers_sent()` is true, so the JSON `Content-Type` would be lost.
 *
 * The attribute is not inherited by an overriding method: a subclass that overrides a JSON action
 * must re-declare `#[JsonResponse]`. (An inherited action the subclass does not override still
 * resolves to the parent's attributed declaration and keeps working.)
 *
 * These requirements are enforced by dedicated PHPStan rules.
 *
 * Example:
 *
 *     #[JsonResponse]
 *     public function getData(): string
 *     {
 *         return json_encode($this->buildData());
 *     }
 */
#[Attribute(Attribute::TARGET_METHOD)]
class JsonResponse
{
}
