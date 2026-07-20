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
 * header once the action has fully returned, immediately before the output is written. Because that
 * happens after anything the action itself does, the JSON `Content-Type` is guaranteed to win.
 *
 * Actions carrying this attribute must therefore always return JSON (typically a `json_encode(...)`
 * string) and must not send the header themselves.
 *
 * Example:
 *
 *     #[JsonResponse]
 *     public function getData()
 *     {
 *         return json_encode($this->buildData());
 *     }
 */
#[Attribute(Attribute::TARGET_METHOD)]
class JsonResponse
{
}
