<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 * @method static \Piwik\AssetManager\UIAssetMinifier getInstance()
 */
namespace Piwik\AssetManager;

use Exception;
use JShrink\Minifier;
use Piwik\Singleton;

class UIAssetMinifier extends Singleton
{
    const MINIFIED_JS_RATIO = 100;

    protected function __construct()
    {
        self::validateDependency();
        parent::__construct();
    }

    /**
     * Indicates if the provided JavaScript content has already been minified or not.
     * The heuristic is based on a custom ratio : (size of file) / (number of lines).
     * The threshold (100) has been found empirically on existing files :
     * - the ratio never exceeds 50 for non-minified content and
     * - it never goes under 150 for minified content.
     *
     * @param string $content Contents of the JavaScript file
     * @return boolean
     */
    public function isMinifiedJs($content)
    {
        $lineCount = substr_count($content, "\n");

        if ($lineCount == 0) {
            return true;
        }

        $contentSize = strlen($content);

        $ratio = $contentSize / $lineCount;

        return $ratio > self::MINIFIED_JS_RATIO;
    }

    /**
     * @param string $content
     * @return string
     */
    public function minifyJs($content)
    {
        return Minifier::minify($content);
    }

    private static function validateDependency()
    {
        if (!class_exists("JShrink\\Minifier")) {
            throw new Exception("JShrink could not be found, maybe you are using Matomo from git and need to update Composer. $ php composer.phar update");
        }
    }
}
