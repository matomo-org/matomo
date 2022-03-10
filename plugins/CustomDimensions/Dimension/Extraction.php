<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik\Plugins\CustomDimensions\Dimension;

use Piwik\Common;
use Piwik\Tracker\Request;
use Piwik\Tracker\Action;
use Piwik\Piwik;
use Exception;
use Piwik\Validators\Regex;

class Extraction
{
    private $dimension = '';
    private $pattern = '';
    private $caseSensitive = true;

    public function __construct($dimension, $pattern)
    {
        $this->dimension = $dimension;
        $this->pattern   = $pattern;
    }

    public function toArray()
    {
        return array('dimension' => $this->dimension, 'pattern' => $this->pattern);
    }

    public function check()
    {
        $dimensions = $this->getSupportedDimensions();
        if (!array_key_exists($this->dimension, $dimensions)) {
            $dimensions = implode(', ', array_keys($dimensions));
            throw new Exception("Invald dimension '$this->dimension' used in an extraction. Available dimensions are: " . $dimensions);
        }

        if (!empty($this->pattern) && $this->dimension !== 'urlparam') {
            // make sure there is exactly one ( followed by one )
            if (1 !== substr_count($this->pattern, '(') ||
                1 !== substr_count($this->pattern, ')') ||
                1 !== substr_count($this->pattern, ')', strpos($this->pattern, '('))) {
                throw new Exception("You need to group exactly one part of the regular expression inside round brackets, eg 'index_(.+).html'");
            }
        }

        //validate regex pattern
        $validator = new Regex();
        $validator->validate($this->formatPattern());
    }

    public static function getSupportedDimensions()
    {
        return array(
            'url' => Piwik::translate('Actions_ColumnPageURL'),
            'urlparam' => Piwik::translate('CustomDimensions_PageUrlParam'),
            'action_name' => Piwik::translate('Goals_PageTitle')
        );
    }

    public function setCaseSensitive($caseSensitive)
    {
        $this->caseSensitive = (bool) $caseSensitive;
    }

    public function extract(Request $request)
    {
        $value = $this->getValueForDimension($request);
        $value = $this->extractValue($value);

        return $value;
    }

    private function getValueForDimension(Request $request)
    {
        /** @var Action $action */
        $action = $request->getMetadata('Actions', 'action');

        if (in_array($this->dimension, array('url', 'urlparam'))) {
            if (!empty($action)) {
                $dimension = $action->getActionUrlRaw();
            } else {
                $dimension = $request->getParam('url');
            }
        } elseif ($this->dimension === 'action_name' && !empty($action)) {
            $dimension = $action->getActionName();
        } else {
            $dimension = $request->getParam($this->dimension);
        }

        if (!empty($dimension)) {
            $dimension = Common::unsanitizeInputValue($dimension);
        }

        return $dimension;
    }

    private function extractValue($value)
    {
        if (!isset($value) || '' === $value) {
            return null;
        }

        $regex = $this->formatPattern();

        if (preg_match($regex, (string) $value, $matches)) {
            // we could improve performance here I reckon by combining all patterns of all configs see eg http://nikic.github.io/2014/02/18/Fast-request-routing-using-regular-expressions.html

            if (array_key_exists(1, $matches)) {
                return $matches[1];
            }
        }
    }

    // format pattern to matomo format
    private function formatPattern () {

        $pattern = $this->pattern;
        if ($this->dimension === 'urlparam') {
            $pattern = '\?.*' . $pattern . '=([^&]*)';
        }

        $regex = '/' . str_replace('/', '\/', $pattern) . '/';
        if (!$this->caseSensitive) {
            $regex .= 'i';
        }

        return $regex;
    }
}