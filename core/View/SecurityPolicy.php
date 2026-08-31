<?php

/**
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

namespace Piwik\View;

use Piwik\Config;

/**
 * Content Security Policy HTTP Header management class
 *
 */
class SecurityPolicy
{
    /*
     * Commonly used rules
     */
    public const RULE_DEFAULT = "'self' 'unsafe-inline' 'unsafe-eval'";
    public const RULE_IMG_DEFAULT = "'self' 'unsafe-inline' 'unsafe-eval' data:";
    public const RULE_EMBEDDED_FRAME = "'self' 'unsafe-inline' 'unsafe-eval' data: https: http:";
    public const RULE_NONE = "'none'";

    /**
     * The policies that will generate the CSP header.
     * These are keyed by the directive.
     *
     * @var array
     */
    private $policies = array();

    private $cspEnabled;
    private $reportOnly;

    public function __construct(Config $config)
    {
        $this->policies['default-src'] = self::RULE_DEFAULT;
        $this->policies['img-src'] = self::RULE_IMG_DEFAULT;

        $generalConfig = $config->General;
        $this->cspEnabled = $generalConfig['csp_enabled'] ?? true;
        $this->reportOnly = $generalConfig['csp_report_only'] ?? false;
    }

    /**
     * Appends a policy to a directive.
     *
     * @api
     */
    public function addPolicy($directive, $value)
    {
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
    public function removeDirective($directive)
    {
        if (isset($this->policies[$directive])) {
            unset($this->policies[$directive]);
        }
    }

    /**
     * Overrides a directive.
     *
     * @api
     */
    public function overridePolicy($directive, $value)
    {
        $this->policies[$directive] = $value;
    }

    /**
     * Disable CSP
     *
     * @api
     */
    public function disable()
    {
        $this->cspEnabled = false;
    }

    /**
     * Creates the Header String that can be inserted in the Content-Security-Policy header.
     *
     * @return string
     */
    public function createHeaderString()
    {
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

    /**
     * Replaces all directives with a policy for responses that are data rather than application UI
     * (API output, exports, generated reports): no scripts, plugins, framing, form submissions or
     * base URI overrides. Inline styles and first-party images stay allowed, as reports need both.
     *
     * Call this on an instance of your own: the shared one a controller exposes as
     * `$this->securityPolicy` builds the policy of the surrounding page. The policy is always
     * enforced, whatever `[General] csp_report_only` is set to.
     *
     * @api
     */
    public function restrictToDataResponse(): void
    {
        $this->policies = [
            'default-src' => self::RULE_NONE,
            'style-src' => "'unsafe-inline'",
            'img-src' => "'self' data:",
            'base-uri' => self::RULE_NONE,
            'form-action' => self::RULE_NONE,
            'frame-ancestors' => self::RULE_NONE,
        ];

        // nothing to roll out gradually here, so always enforce
        $this->reportOnly = false;
    }

    /**
     * A less restrictive CSP which will allow embedding other sites with iframes
     * (useful for heatmaps and session recordings)
     *
     * @api
     */
    public function allowEmbedPage()
    {
        $this->overridePolicy('default-src', self::RULE_EMBEDDED_FRAME);
        $this->overridePolicy('img-src', self::RULE_EMBEDDED_FRAME);
        $this->addPolicy('script-src', self::RULE_DEFAULT);
    }
}
