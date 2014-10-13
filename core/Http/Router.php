<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Http;

use Piwik\Url;

/**
 * Router
 */
class Router
{
    /**
     * Filters some malformed URL by suggesting to redirect them.
     *
     * E.g. /index.php/.html?... can be interpreted as HTML by old browsers
     * even though the Content-Type says JSON.
     * @link https://github.com/piwik/piwik/issues/6156
     *
     * @param string $url The URL to filter.
     *
     * @return string|null If not null, then the application should redirect to that URL.
     */
    public function filterUrl($url)
    {
        $path = parse_url($url, PHP_URL_PATH);

        if (strpos($path, 'index.php/') !== false) {
            return preg_replace('#index\.php/([^\?]*)#', 'index.php', $url, 1);
        }

        return null;
    }
}
