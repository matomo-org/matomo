<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\View;

use Twig\Node\Expression\ArrayExpression;
use Twig\Node\IncludeNode;
use Twig\Token;
use Twig\TokenParser\AbstractTokenParser;

/**
 * Defines a new Twig tag that will render a Piwik View.
 *
 * Use the tag like this:
 *
 *     {% render theView %}
 *
 * where `theView` is a variable referencing a View instance.
 */
class RenderTokenParser extends AbstractTokenParser
{
    /**
     * Parses the Twig stream and creates a Twig_Node_Include instance that includes
     * the View's template.
     *
     * @return \Twig\Node\Node
     */
    public function parse(Token $token)
    {
        $parser = $this->parser;
        $stream = $parser->getStream();

        $view = $parser->getExpressionParser()->parseExpression();

        $variablesOverride = new ArrayExpression(array(), $token->getLine());
        if ($stream->test(Token::NAME_TYPE, 'with')) {
            $stream->next();

            $variablesOverride->addElement($this->parser->getExpressionParser()->parseExpression());
        }

        $stream->expect(Token::BLOCK_END_TYPE);

        $viewTemplateExpr = new MethodCallExpression(
            $view,
            'getTemplateFile',
            new ArrayExpression(array(), $token->getLine()),
            $token->getLine()
        );

        $variablesExpr = new MethodCallExpression(
            $view,
            'getTemplateVars',
            $variablesOverride,
            $token->getLine()
        );

        return new IncludeNode(
            $viewTemplateExpr,
            $variablesExpr,
            $only = false,
            $ignoreMissing = false,
            $token->getLine()
        );
    }

    /**
     * Returns the tag identifier.
     *
     * @return string
     */
    public function getTag()
    {
        return 'render';
    }
}
