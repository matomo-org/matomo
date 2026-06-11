<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Twig;

use Twig\Environment;
use Twig\Source;

/**
 * Twig environment that makes the PHP code Twig generates for compiled templates
 * compatible with PHP 7.2.
 *
 * Twig itself is automatically downgraded to PHP 7.2 syntax when installed (see
 * .github/scripts/vendor-downgrade), but that cannot cover the template code Twig
 * generates at runtime: its class header declares typed properties, which PHP 7.2
 * cannot parse.
 *
 * The transformation is applied on all PHP versions, not just PHP < 7.4, so that
 * compiled template caches stay identical regardless of the PHP version that
 * happened to fill them.
 */
class Php72CompatibleEnvironment extends Environment
{
    public function compileSource(Source $source): string
    {
        $compiled = parent::compileSource($source);

        // strip the typed property declarations from the generated class header
        // (`private Source $source;` and `private array $macros = [];` as of Twig 3.27,
        // see \Twig\Node\ModuleNode::compileClassHeader())
        $compiled = preg_replace(
            '/^(\s*private\s+)(?:\\\\?[A-Za-z_][\w\\\\]*\s+)(\$(?:source|macros)\b)/m',
            '$1$2',
            $compiled
        );

        // strip union return types from generated method signatures
        // (`): string|Markup` for macros, `): bool|string|Template|TemplateWrapper` for
        // doGetParent() as of Twig 3.27, see \Twig\Node\MacroNode and \Twig\Node\ModuleNode)
        $compiled = preg_replace(
            '/^(\s*(?:public|protected|private)\s+function\s+\w+\([^\n]*\))\s*:\s*\??[\w\\\\]+(?:\|[\w\\\\]+)+/m',
            '$1',
            $compiled
        );

        return $compiled;
    }
}
