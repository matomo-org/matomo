<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

use Isolated\Symfony\Component\Finder\Finder;

/**
 * This file is for php-scoper, a tool used when prefixing dependencies.
 * TODO: link to docs here
 */

$dependenciesToPrefix = json_decode(getenv('MATOMO_DEPENDENCIES_TO_PREFIX'), true);
$namespacesToPrefix = json_decode(getenv('MATOMO_NAMESPACES_TO_PREFIX'), true);

return [
    'prefix' => 'Matomo\\Dependencies',
    'finders' => array_map(function ($dependency) {
        return Finder::create()
            ->files()
            ->in($dependency);
    }, $dependenciesToPrefix),
    'patchers' => [
        // patchers for twig
        static function (string $filePath, string $prefix, string $content): string {
            // correct use statements in generated templates
            if (preg_match('%twig/src/Node/ModuleNode\\.php$%', $filePath)) {
                return str_replace('"use Twig\\', '"use ' . str_replace('\\', '\\\\', $prefix) . '\\\\Twig\\', $content);
            }

            // correctly scoped function calls to twig_... globals (which will not be globals anymore) in strings
            if (strpos($filePath, 'twig/twig') !== false) {
                $content = preg_replace("/'(_?twig_[a-z_0-9]+)([('])/", '\'\\Matomo\\Dependencies\\\${1}${2}', $content);
                $content = preg_replace("/\"(_?twig_[a-z_0-9]+)([(\"])/", '"\\\\\\Matomo\\\\\\Dependencies\\\\\\\${1}${2}', $content);

                $content = preg_replace("/([^\\\\])(_?twig_[a-z_0-9]+)\(\"/", '${1}\\\\\\Matomo\\\\\\Dependencies\\\\\\\${2}("', $content);
                $content = preg_replace("/([^\\\\])(_?twig_[a-z_0-9]+)\('/", '${1}\\Matomo\\Dependencies\\\${2}(\'', $content);
            }

            return $content;
        },
    ],
    'include-namespaces' => $namespacesToPrefix,
];
