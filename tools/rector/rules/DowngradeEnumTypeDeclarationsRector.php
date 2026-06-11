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
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\UnionType;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Removes parameter, return and property type declarations that reference one of the
 * configured enum classes. When rector downgrades an enum to a constant-list class,
 * all formerly-enum-typed values become scalars (the constant values), so remaining
 * type declarations would cause TypeErrors at runtime.
 *
 * Rector's DowngradeEnumToConstantListClassRector covers parameter types only, and
 * relies on reflection that breaks when the enum declaration is converted on disk
 * before its usages within the same rector run. This rule instead works against a
 * statically pre-computed list of enum class names (detected by downgrade-vendor.php
 * before any rector pass runs), making it deterministic and covering return and
 * property types as well.
 */
final class DowngradeEnumTypeDeclarationsRector extends AbstractRector implements ConfigurableRectorInterface
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
            'Remove type declarations referencing enums that are downgraded to constant-list classes',
            [new ConfiguredCodeSample(
                'function parse(Associativity $associativity): Associativity { }',
                'function parse($associativity) { }',
                ['Associativity']
            )]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class, Function_::class, Closure::class, ArrowFunction::class, Property::class];
    }

    /**
     * @param ClassMethod|Function_|Closure|ArrowFunction|Property $node
     */
    public function refactor(Node $node): ?Node
    {
        $hasChanged = false;

        if ($node instanceof Property) {
            if ($this->typeRefersToEnum($node->type)) {
                $node->type = null;
                $hasChanged = true;
            }

            return $hasChanged ? $node : null;
        }

        foreach ($node->params as $param) {
            if ($this->typeRefersToEnum($param->type)) {
                $param->type = null;
                $hasChanged = true;
            }
        }

        if ($this->typeRefersToEnum($node->returnType)) {
            $node->returnType = null;
            $hasChanged = true;
        }

        return $hasChanged ? $node : null;
    }

    private function typeRefersToEnum(?Node $type): bool
    {
        if ($type instanceof NullableType) {
            return $this->typeRefersToEnum($type->type);
        }

        if ($type instanceof UnionType || $type instanceof IntersectionType) {
            foreach ($type->types as $innerType) {
                if ($this->typeRefersToEnum($innerType)) {
                    return true;
                }
            }

            return false;
        }

        if (!$type instanceof Name) {
            return false;
        }

        $name = $this->getName($type);

        return $name !== null && in_array(strtolower($name), $this->enumClassNames, true);
    }
}
