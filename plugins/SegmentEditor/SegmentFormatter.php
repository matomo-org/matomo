<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\Plugins\SegmentEditor;

use Exception;
use Piwik\Piwik;
use Piwik\Segment;
use Piwik\Segment\SegmentExpression;

/**
 */
class SegmentFormatter
{
    /**
     * @var Segment\SegmentsList
     */
    private $segmentList;

    private $matchesMetric = array(
        SegmentExpression::MATCH_EQUAL => 'General_OperationEquals',
        SegmentExpression::MATCH_NOT_EQUAL => 'General_OperationNotEquals',
        SegmentExpression::MATCH_LESS_OR_EQUAL => 'General_OperationAtMost',
        SegmentExpression::MATCH_GREATER_OR_EQUAL => 'General_OperationAtLeast',
        SegmentExpression::MATCH_LESS => 'General_OperationLessThan',
        SegmentExpression::MATCH_GREATER => 'General_OperationGreaterThan',
    );

    private $matchesDimension = array(
        SegmentExpression::MATCH_EQUAL => 'General_OperationIs',
        SegmentExpression::MATCH_NOT_EQUAL => 'General_OperationIsNot',
        SegmentExpression::MATCH_CONTAINS => 'General_OperationContains',
        SegmentExpression::MATCH_DOES_NOT_CONTAIN => 'General_OperationDoesNotContain',
        SegmentExpression::MATCH_STARTS_WITH => 'General_OperationStartsWith',
        SegmentExpression::MATCH_ENDS_WITH => 'General_OperationEndsWith'
    );

    private $operators = array(
        SegmentExpression::BOOL_OPERATOR_AND => 'General_And',
        SegmentExpression::BOOL_OPERATOR_OR => 'General_Or',
        SegmentExpression::BOOL_OPERATOR_END => '',
    );

    public function __construct(Segment\SegmentsList $segmentList)
    {
        $this->segmentList = $segmentList;
    }

    public function getHumanReadable($segmentString, $idSite)
    {
        if (empty($segmentString)) {
            return Piwik::translate('SegmentEditor_DefaultAllVisits');
        }

        try {
            $segment = new SegmentExpression(urldecode($segmentString));
            $expressions = $segment->parseSubExpressions();
        } catch (Exception $e) {
            $segment = new SegmentExpression($segmentString);
            $expressions = $segment->parseSubExpressions();
        }

        $readable = '';
        foreach ($expressions as $expression) {
            $operator = $expression[SegmentExpression::INDEX_BOOL_OPERATOR];
            $operand  = $expression[SegmentExpression::INDEX_OPERAND];
            $name     = $operand[SegmentExpression::INDEX_OPERAND_NAME];

            $segment = $this->segmentList->getSegment($name);

            if (empty($segment)) {
                throw new Exception(sprintf("The segment '%s' does not exist.", $name));
            }

            $readable .= Piwik::translate($segment->getName()) . ' ';
            $readable .= $this->getTranslationForComparison($operand, $segment->getType()) . ' ';
            $readable .= $this->getFormattedValue($operand);
            $readable .= $this->getTranslationForBoolOperator($operator) . ' ';
        }

        $readable = trim($readable);

        return $readable;
    }

    private function getTranslationForComparison($operand, $segmentType)
    {
        $operator = $operand[SegmentExpression::INDEX_OPERAND_OPERATOR];

        $translation = $operator;

        if ($operator === SegmentExpression::MATCH_IS_NULL_OR_EMPTY) {
            return Piwik::translate('SegmentEditor_SegmentOperatorIsNullOrEmpty');
        }

        if ($operator === SegmentExpression::MATCH_IS_NOT_NULL_NOR_EMPTY) {
            return Piwik::translate('SegmentEditor_SegmentOperatorIsNotNullNorEmpty');
        }

        if ($segmentType === 'dimension' && !empty($this->matchesDimension[$operator])) {
            $translation = Piwik::translate($this->matchesDimension[$operator]);
        }
        if ($segmentType === 'metric' && !empty($this->matchesMetric[$operator])) {
            $translation = Piwik::translate($this->matchesMetric[$operator]);
        }

        return mb_strtolower($translation);
    }

    private function getFormattedValue($operand)
    {
        $operator = $operand[SegmentExpression::INDEX_OPERAND_OPERATOR];

        if ($operator === SegmentExpression::MATCH_IS_NULL_OR_EMPTY
            || $operator === SegmentExpression::MATCH_IS_NOT_NULL_NOR_EMPTY) {
            return '';
        }

        $value = $operand[SegmentExpression::INDEX_OPERAND_VALUE];

        if (empty($value)) {
            $value = '';
        }

        return '"' . $value . '" ';
    }

    private function getTranslationForBoolOperator($operator)
    {
        $translation = '';

        if (!empty($this->operators[$operator])) {
            $translation = Piwik::translate($this->operators[$operator]);
        } elseif (!empty($operator)) {
            $translation = $operator;
        }

        return $translation;
    }
}
