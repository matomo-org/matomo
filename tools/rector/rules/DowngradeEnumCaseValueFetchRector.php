<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

declare(strict_types=1);

namespace Matomo\Rector;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Removes `->value` fetches on expressions originating from one of the configured
 * enum classes (case constant fetches like `Type::Prefix->value` and static calls
 * like `Type::getType($x)->value`). When rector downgrades an enum to a constant-list
 * class, the cases already hold their backing value, so the `->value` fetch would
 * fail at runtime on the scalar.
 *
 * Static calls on the enum class are assumed to return an enum case; methods of the
 * configured enums returning something else would be rewritten incorrectly, so check
 * the affected package code when adding enums to the configuration.
 */
final class DowngradeEnumCaseValueFetchRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string[] lowercased fully qualified enum class names
     */
    private $enumClassNames = [];

    /**
     * @param mixed[] $configuration fully qualified enum class names
     */
    public function configure(array $configuration): void
    {
        $this->enumClassNames = array_map(
            static function ($className): string {
                return strtolower(ltrim((string) $className, '\\'));
            },
            $configuration
        );
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove ->value fetches on cases of enums that are downgraded to constant-list classes',
            [new ConfiguredCodeSample(
                '$type = ParserType::Prefix->value;',
                '$type = ParserType::Prefix;',
                ['ParserType']
            )]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [PropertyFetch::class];
    }

    /**
     * @param PropertyFetch $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isName($node->name, 'value')) {
            return null;
        }

        $caseExpr = $node->var;

        if (!$caseExpr instanceof ClassConstFetch && !$caseExpr instanceof StaticCall) {
            return null;
        }

        $class = $caseExpr->class;

        if (!$class instanceof Name) {
            return null;
        }

        $className = $this->getName($class);

        if ($className === null || !in_array(strtolower($className), $this->enumClassNames, true)) {
            return null;
        }

        return $caseExpr;
    }
}
