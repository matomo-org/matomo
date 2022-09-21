<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

/**
 * This file is for php-scoper, a tool used when prefixing dependencies.
 * TODO: link to docs here
 */

$namespacesToPrefix = json_decode(getenv('MATOMO_NAMESPACES_TO_PREFIX'), true);

return [
    'prefix' => 'Matomo\\Dependencies',
    'patchers' => [],
    'include-namespaces' => $namespacesToPrefix,
];
