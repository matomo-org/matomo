<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\View;

use Piwik\Config;

/**
 * Content Security Policy HTTP Header management class
 *
 */
class SecurityPolicy
{
    /**
     * The policies that will generate the CSP header.
     * These are keyed by the directive.
     *
     * @var array
     */
    private $policies = array();

    private $cspEnabled = true;
    private $reportOnly = false;

    /**
     * Constructor.
     */
    public function __construct(Config $config) {
        $this->policies['default-src'] = "'self' 'unsafe-inline' 'unsafe-eval'";

        $generalConfig = $config->General;
        $this->cspEnabled = $generalConfig['csp_enabled'];
        $this->reportOnly = $generalConfig['csp_report_only'];
    }

    /**
     * Appends a policy to a directive.
     *
     */
    public function addPolicy($directive, $value) {
        if (isset($this->policies[$directive])) {
            $this->policies[$directive] .= ' ' . $value;
        } else {
            $this->policies[$directive] = $value;
        }
    }

    /**
     * Removes a directive.
     *
     */
    public function removeDirective($directive) {
        if (isset($this->policies[$directive])) {
            unset($this->policies[$directive]);
        }
    }

    /**
     * Overrides a directive.
     *
     */
    public function overridePolicy($directive, $value) {
        $this->policies[$directive] = $value;
    }

    /**
     * Creates the Header String that can be inserted in the Content-Security-Policy header.
     *
     * @return string
     */
    public function createHeaderString() {
        if (!$this->cspEnabled) {
            return '';
        }

        if ($this->reportOnly) {
            $headerString = 'Content-Security-Policy-Report-Only: ';
        } else {
            $headerString = 'Content-Security-Policy: ';
        }
        foreach ($this->policies as $directive => $values) {
            $headerString .= $directive . ' ' . $values . '; ';
        }

        return $headerString;
    }
}
