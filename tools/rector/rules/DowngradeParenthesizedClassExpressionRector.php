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
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Downgrades the PHP 8.0-only parentheses around dereferenceable class expressions
 * in `new` and `instanceof`, e.g. `new ($this->nodeClass)($arg)` to `new $this->nodeClass($arg)`.
 *
 * Rector's own DowngradeArbitraryExpressionsSupportRector only covers such expressions
 * inside assignments, missing e.g. `return new ($this->nodeClass)($arg);`
 * (https://github.com/rectorphp/rector/issues - present as of rector 2.4.5). Class
 * expressions that are not dereferenceable (e.g. method calls) still need that rule,
 * as they cannot be downgraded by removing the parentheses alone.
 */
final class DowngradeParenthesizedClassExpressionRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Remove PHP 8.0-only parentheses around dereferenceable class expressions in new and instanceof',
            [new CodeSample(
                'return new ($this->nodeClass)($arg);',
                'return new $this->nodeClass($arg);'
            )]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [New_::class, Instanceof_::class];
    }

    /**
     * @param New_|Instanceof_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $classExpr = $node->class;

        $isDereferenceable = $classExpr instanceof Variable
            || $classExpr instanceof PropertyFetch
            || $classExpr instanceof StaticPropertyFetch
            || $classExpr instanceof ArrayDimFetch;

        if (!$isDereferenceable || !$this->isClassExprParenthesized($node)) {
            return null;
        }

        // force re-printing the node, which drops the parentheses around the class expression
        $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);

        return $node;
    }

    /**
     * @param New_|Instanceof_ $node
     */
    private function isClassExprParenthesized(Node $node): bool
    {
        $oldTokens = $this->file->getOldTokens();
        $tokenPos = $node->class->getStartTokenPos() - 1;

        // find the nearest significant token left of the class expression
        while ($tokenPos >= 0) {
            $token = $oldTokens[$tokenPos] ?? null;
            --$tokenPos;

            if ($token === null || $token->id === \T_WHITESPACE || $token->id === \T_COMMENT) {
                continue;
            }

            return $token->text === '(';
        }

        return false;
    }
}
