<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\View;

/**
 * Content Security Policy HTTP Header management class
 *
 * @api
 */
class SecurityPolicy
{
    /**
     * The policies that will generate the CSP header.
     * These are keyed by the directive.
     *
     * @var array
     */
    protected $policies = array();

    /**
     * Constructor.
     */
    public function __construct() {
        $this->policies['default-src'] = "'self' 'unsafe-inline' 'unsafe-eval'";
    }

    /**
     * Appends a policy to a directive.
     *
     * @api
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
     * @api
     */
    public function removeDirective($directive) {
        if (isset($this->policies[$directive])) {
            unset($this->policies[$directive]);
        }
    }

    /**
     * Overrides a directive.
     *
     * @api
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
        $headerString = 'Content-Security-Policy: ';

        foreach ($this->policies as $directive => $values) {
            $headerString .= $directive . ' ' . $values . '; ';
        }
        return $headerString;
    }

}