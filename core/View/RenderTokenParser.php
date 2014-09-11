<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\View;

use Twig_Node_Expression_Array;
use Twig_Node_Expression_MethodCall;
use Twig_Node_Include;
use Twig_Token;
use Twig_TokenParser;

/**
 * Defines a new Twig tag that will render a Piwik View.
 *
 * Use the tag like this:
 *
 *     {% render theView %}
 *
 * where `theView` is a variable referencing a View instance.
 */
class RenderTokenParser extends Twig_TokenParser
{
    /**
     * Parses the Twig stream and creates a Twig_Node_Include instance that includes
     * the View's template.
     *
     * @return Twig_Node_Include
     */
    public function parse(Twig_Token $token)
    {
        $parser = $this->parser;
        $stream = $parser->getStream();

        $view = $parser->getExpressionParser()->parseExpression();

        $variablesOverride = new Twig_Node_Expression_Array(array(), $token->getLine());
        if ($stream->test(Twig_Token::NAME_TYPE, 'with')) {
            $stream->next();

            $variablesOverride->addElement($this->parser->getExpressionParser()->parseExpression());
        }

        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        $viewTemplateExpr = new Twig_Node_Expression_MethodCall(
            $view,
            'getTemplateFile',
            new Twig_Node_Expression_Array(array(), $token->getLine()),
            $token->getLine()
        );

        $variablesExpr = new Twig_Node_Expression_MethodCall(
            $view,
            'getTemplateVars',
            $variablesOverride,
            $token->getLine()
        );

        return new Twig_Node_Include(
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