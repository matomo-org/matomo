<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Cache;

use Piwik\Translate;

/**
 * Caching class used for static caching which is language aware. It'll cache the given content depending on the
 * current loaded language. This prevents you from having to invalidate the cache during tests in case the loaded
 * language changes etc.
 *
 * TODO convert this to a decorator... see {@link StaticCache}
 */
class LanguageAwareStaticCache extends StaticCache
{
    protected function completeKey($cacheKey)
    {
        return $cacheKey . Translate::getLanguageLoaded();
    }
}
