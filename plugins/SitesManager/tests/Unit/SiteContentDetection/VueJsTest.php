<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Plugins\SitesManager\tests\Unit\SiteContentDetection;

use Piwik\Plugins\SitesManager\SiteContentDetection\VueJs;

/**
 * @group SitesManager
 * @group SiteContentDetection
 * @group Plugins
 */
class VueJsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider responseProvider
     */
    public function testdetectByContent($expected, $data, $headers)
    {
        $detection = new VueJs();
        self::assertSame($expected, $detection->detectByContent($data, $headers));
    }

    public function responseProvider()
    {
        yield 'no content at all' => [
            false,
            '',
            []
        ];

        yield 'no vue.js content' => [
            false,
            'This is a blog about vue, not using it.',
            []
        ];

        $validVueJsFiles = [
            'node_modules/vue/dist/vue-develpment.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.cjs.js',
            'https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.cjs.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.cjs.prod.js',
            'https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.cjs.prod.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.esm-browser.js',
            'https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.esm-browser.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.esm-browser.prod.js',
            'https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.esm-browser.prod.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.esm-bundler.js',
            'https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.esm-bundler.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.global.js',
            'https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue-min.global.js',
            'https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.global.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.global.prod.js',
            'https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.global.prod.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.runtime.esm-browser.js',
            'https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.runtime.esm-browser.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.runtime.esm-browser.prod.js',
            'https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.runtime.esm-browser.prod.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.runtime.esm-bundler.js',
            'https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.runtime.esm-bundler.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.runtime.global.js',
            'https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.runtime.global.min.js',
            'https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.runtime.global.prod.js',
            'https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vue.runtime.global.prod.min.js',
        ];

        foreach ($validVueJsFiles as $vueJsFile) {
            yield "$vueJsFile used" => [
                true,
                "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script><script src='$vueJsFile'></script></head><body>A site</body></html>",
                []
            ];
        }

        yield "unknown vue.js file used" => [
            false,
            "<!DOCTYPE HTML>\n<html lang=\"en\"><head><title>A site</title><script><script>console.log('abc');</script><script src='https://cdnjs.cloudflare.com/ajax/libs/vue/3.3.4/vuetmp.runtime.global.prod.min.js'></script></head><body>A site</body></html>",
            []
        ];
    }
}
