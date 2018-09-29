<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\Tests\Framework;


use Piwik\Common;
use Piwik\Option;

/**
 * TODO: doc
 *
 * TODO: describe need to keep strings as small as possible
 */
class XssTesting
{
    const OPTION_NAME = 'Tests.xssEntries';

    public function forTwig($type, $sanitize = false)
    {
        $n = $this->addXssEntry($type, 'twig');

        $result = "<script>_x($n)</script>";
        if ($sanitize) {
            // NOTE: since API\Request does sanitization, API methods do not. when calling them, we must
            // sometimes do sanitization ourselves.
            $result = Common::sanitizeInputValue($result);
        }
        return $result;
    }

    public function forAngular($type, $sanitize = false)
    {
        $n = $this->addXssEntry($type, 'angular');

        $result = "{{constructor.constructor(\"_x($n)\")()}}";
        if ($sanitize) {
            $result = Common::sanitizeInputValue($result);
        }
        return $result;
    }

    private function addXssEntry($attackVectorType, $injectionType)
    {
        $entries = $this->getXssEntries();
        $key = count($entries);
        $entries[$key] = $injectionType . '-(' . $attackVectorType . ')';
        $this->setXssEntries($entries);
        return $key;
    }

    private function getXssEntries()
    {
        $value = Option::get(self::OPTION_NAME);
        return json_decode($value, $isAssoc = true);
    }

    private function setXssEntries($entries)
    {
        $value = json_encode($entries);
        Option::set(self::OPTION_NAME, $value);
    }

    private function getJavaScriptCode()
    {
        $entries = json_encode($this->getXssEntries());
        $js = <<<JS
window._xssEntryTypes = $entries;
window._x = function triggerXss(id) {
    document.body.innerHTML = 'XSS ' + window._xssEntryTypes[id];
};
JS;
        return $js;
    }

    public static function getJavaScriptAddEvent()
    {
        $xssTesting = new XssTesting();
        return ['Template.jsGlobalVariables', function (&$out) use ($xssTesting) {
            $out .= $xssTesting->getJavaScriptCode();
        }];
    }
}
