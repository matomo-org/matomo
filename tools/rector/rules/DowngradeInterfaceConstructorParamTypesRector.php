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
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Analyser\Scope;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Drops native parameter types from constructors of classes implementing an interface
 * that declares __construct. PHP only applies signature variance checks to constructors
 * when they are declared in an interface, and contravariant parameter types (e.g. the
 * interface requiring AbstractExpression while the class accepts the wider Node) are
 * only allowed since PHP 7.4. Omitting the parameter types entirely is valid widening
 * on PHP 7.2.
 *
 * Rector's DowngradeParameterTypeWideningRector does not cover interface-declared
 * constructors (present as of rector 2.4.5). Must run in a pass after all other
 * downgrades, so that class hierarchies on disk are stable when reflected.
 */
final class DowngradeInterfaceConstructorParamTypesRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Drop constructor parameter types that PHP 7.2 considers incompatible with an interface-declared constructor',
            [new CodeSample(
                'class Binary implements BinaryInterface { public function __construct(Node $left) {} }',
                'class Binary implements BinaryInterface { public function __construct($left) {} }'
            )]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isName($node->name, '__construct')) {
            return null;
        }

        $hasTypedParams = false;

        foreach ($node->params as $param) {
            if ($param->type !== null) {
                $hasTypedParams = true;
                break;
            }
        }

        if (!$hasTypedParams) {
            return null;
        }

        $scope = $node->getAttribute(AttributeKey::SCOPE);

        if (!$scope instanceof Scope) {
            return null;
        }

        $classReflection = $scope->getClassReflection();

        if ($classReflection === null || $classReflection->isInterface()) {
            return null;
        }

        $interfaceDeclaresConstructor = false;

        foreach ($classReflection->getInterfaces() as $interfaceReflection) {
            if ($interfaceReflection->hasNativeMethod('__construct')) {
                $interfaceDeclaresConstructor = true;
                break;
            }
        }

        if (!$interfaceDeclaresConstructor) {
            return null;
        }

        foreach ($node->params as $param) {
            $param->type = null;
        }

        return $node;
    }
}
