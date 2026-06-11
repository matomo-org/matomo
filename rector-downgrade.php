<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Rector configuration used to downgrade composer dependencies to PHP 7.2 syntax.
 *
 * This config is not meant to be run directly. It is executed by
 * .github/scripts/vendor-downgrade/downgrade-vendor.php (wired into the composer
 * post-install/post-update hooks), which passes the vendor package paths to
 * process on the command line.
 *
 * The downgrade runs in multiple passes (selected via the MATOMO_DOWNGRADE_SET
 * environment variable, set by downgrade-vendor.php):
 *
 *   1. "php80": downgrades PHP 8.1+ syntax to PHP 8.0.
 *   2. "php72-prep": converts enums to constant-list classes and removes
 *      constructor property promotion. The named argument downgrade in the
 *      last pass inlines default values of skipped parameters, which it can
 *      only resolve when the files declaring those parameters no longer use
 *      enum cases or promoted properties. As that resolution happens across
 *      files, these constructs must already be downgraded on disk before the
 *      last pass starts. Also hoists match expressions out of if conditions,
 *      where the match-to-switch downgrade of the last pass cannot reach them.
 *   3. "php72" (default): downgrades the remaining syntax to PHP 7.2.
 *   4. "variance": drops constructor parameter types conflicting with
 *      interface-declared constructors. Runs last so that the reflected class
 *      hierarchies on disk are already fully downgraded.
 *
 * Note that the downgrade only covers PHP *syntax*. Calls to functions or classes
 * introduced after PHP 7.2 (e.g. str_contains, Stringable) are either rewritten
 * by rector or must be provided through symfony/polyfill-php7x/php8x packages
 * in composer.json.
 */

use Matomo\Rector\DowngradeEnumCaseValueFetchRector;
use Matomo\Rector\DowngradeEnumTypeDeclarationsRector;
use Matomo\Rector\DowngradeInterfaceConstructorParamTypesRector;
use Matomo\Rector\DowngradeMatchInIfConditionRector;
use Matomo\Rector\DowngradeParenthesizedClassExpressionRector;
use Rector\Config\RectorConfig;
use Rector\DowngradePhp80\Rector\Class_\DowngradePropertyPromotionRector;
use Rector\DowngradePhp80\Rector\Enum_\DowngradeEnumToConstantListClassRector;
use Rector\Set\ValueObject\DowngradeLevelSetList;

require_once __DIR__ . '/tools/rector/rules/DowngradeEnumCaseValueFetchRector.php';
require_once __DIR__ . '/tools/rector/rules/DowngradeEnumTypeDeclarationsRector.php';
require_once __DIR__ . '/tools/rector/rules/DowngradeInterfaceConstructorParamTypesRector.php';
require_once __DIR__ . '/tools/rector/rules/DowngradeMatchInIfConditionRector.php';
require_once __DIR__ . '/tools/rector/rules/DowngradeParenthesizedClassExpressionRector.php';

return static function (RectorConfig $rectorConfig): void {
    switch (getenv('MATOMO_DOWNGRADE_SET')) {
        case 'php80':
            $rectorConfig->sets([DowngradeLevelSetList::DOWN_TO_PHP_80]);
            break;
        case 'variance':
            $rectorConfig->rule(DowngradeInterfaceConstructorParamTypesRector::class);
            break;
        case 'php72-prep':
            $rectorConfig->rules([
                DowngradeEnumToConstantListClassRector::class,
                DowngradeMatchInIfConditionRector::class,
                DowngradePropertyPromotionRector::class,
            ]);

            // detected by downgrade-vendor.php before any rector pass runs
            $enumClassNames = array_filter(explode(',', (string) getenv('MATOMO_DOWNGRADE_ENUMS')));

            if (!empty($enumClassNames)) {
                $rectorConfig->ruleWithConfiguration(DowngradeEnumTypeDeclarationsRector::class, $enumClassNames);
                $rectorConfig->ruleWithConfiguration(DowngradeEnumCaseValueFetchRector::class, $enumClassNames);
            }
            break;
        default:
            $rectorConfig->sets([DowngradeLevelSetList::DOWN_TO_PHP_72]);
            $rectorConfig->rule(DowngradeParenthesizedClassExpressionRector::class);
    }

    $rectorConfig->parallel(240);

    // test and doc directories of the processed packages (computed by downgrade-vendor.php,
    // since glob patterns like */Test/* would also match real code such as twig's
    // src/Node/Expression/Test classes). These files are neither autoloaded nor shipped,
    // and may intentionally contain syntax rector cannot parse or downgrade.
    $skipPaths = array_filter(explode(',', (string) getenv('MATOMO_DOWNGRADE_SKIP')));

    if (!empty($skipPaths)) {
        $rectorConfig->skip($skipPaths);
    }
};
