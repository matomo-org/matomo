<?php
/*
 * This file is a modified file from Twig 1.x.
 *
 * (c) Fabien Potencier
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with it's source code.
 */

namespace Piwik\View;

use Twig\Compiler;
use Twig\Node\Expression\AbstractExpression;
use Twig\Node\Expression\ArrayExpression;
use Twig\Node\Expression\NameExpression;

class MethodCallExpression extends AbstractExpression
{
    public function __construct(AbstractExpression $node, $method, ArrayExpression $arguments, $lineno)
    {
        parent::__construct(['node' => $node, 'arguments' => $arguments], ['method' => $method, 'safe' => false], $lineno);

        if ($node instanceof NameExpression) {
            $node->setAttribute('always_defined', true);
        }
    }

    public function compile(Compiler $compiler)
    {
        $compiler
            ->subcompile($this->getNode('node'))
            ->raw('->')
            ->raw($this->getAttribute('method'))
            ->raw('(')
        ;
        $first = true;
        foreach ($this->getNode('arguments')->getKeyValuePairs() as $pair) {
            if (!$first) {
                $compiler->raw(', ');
            }
            $first = false;

            $compiler->subcompile($pair['value']);
        }
        $compiler->raw(')');
    }
}