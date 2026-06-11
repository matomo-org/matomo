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
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Match_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PHPStan\Analyser\Scope;
use Rector\Naming\Naming\VariableNaming;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Hoists `match` expressions used as (or assigned within) an `if` condition into a
 * statement before the `if`. Rector's DowngradeMatchToSwitchRector only downgrades
 * `match` in echo/expression/return statement positions, so a `match` inside an `if`
 * condition would survive the downgrade otherwise (present as of rector 2.4.5).
 * Must run in a pass before the match-to-switch downgrade.
 */
final class DowngradeMatchInIfConditionRector extends AbstractRector
{
    /**
     * @var VariableNaming
     */
    private $variableNaming;

    public function __construct(VariableNaming $variableNaming)
    {
        $this->variableNaming = $variableNaming;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Move match expressions out of if conditions into a preceding statement',
            [new CodeSample(
                'if ($node = match ($x) { 1 => "a", default => null }) { }',
                '$node = match ($x) { 1 => "a", default => null }; if ($node) { }'
            )]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [If_::class];
    }

    /**
     * @param If_ $node
     * @return Node[]|null
     */
    public function refactor(Node $node): ?array
    {
        $condition = $node->cond;

        if ($condition instanceof Match_) {
            $scope = $node->getAttribute(AttributeKey::SCOPE);
            $matchVariable = new Variable(
                $scope instanceof Scope
                    ? $this->variableNaming->createCountedValueName('match', $scope)
                    : 'match'
            );

            $hoistedStatement = new Expression(new Assign($matchVariable, $condition));
            $node->cond = $matchVariable;

            return [$hoistedStatement, $node];
        }

        if ($condition instanceof Assign && $condition->expr instanceof Match_ && $condition->var instanceof Variable) {
            $hoistedStatement = new Expression($condition);
            $node->cond = new Variable($condition->var->name);

            return [$hoistedStatement, $node];
        }

        return null;
    }
}
