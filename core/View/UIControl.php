<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\View;

use Piwik\View;
use Exception;

/**
 * Base type of UI controls.
 *
 * The JavaScript companion class can be found in plugins/CoreHome/javascripts/uiControl.js.
 *
 * @api
 */
class UIControl extends \Piwik\View
{
    /**
     * The Twig template file that generates the control's HTML.
     *
     * Derived classes must set this constant.
     */
    const TEMPLATE = '';

    /**
     * Holds the array of values that are passed to the UIControl JavaScript class.
     *
     * @var array
     */
    public $clientSideProperties = array();

    /**
     * Holds an array of values that are passed to the UIControl JavaScript class. These values
     * differ from those in {@link $clientSideProperties} in that they are meant to passed as
     * request parameters when the JavaScript code makes an AJAX request.
     *
     * @var array
     */
    public $clientSideParameters = array();

    /**
     * The CSS class that is used to map the root element of this control with the JavaScript class.
     *
     * This field must be set prior to rendering.
     *
     * @var string
     */
    public $cssIdentifier = null;

    /**
     * The name of the JavaScript class that handles the behavior of this control.
     *
     * The JavaScript class must exist in the **piwik/UI** JavaScript module (so it will exist in
     * `window.piwik.UI`).
     *
     * This field must be set prior to rendering.
     *
     * @var string
     */
    public $jsClass = null;

    /**
     * Extra CSS class(es) for the root element.
     *
     * @var string
     */
    public $cssClass = "";

    /**
     * The inner view that renders the actual control content.
     *
     * @var View
     */
    private $innerView = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->innerView = new View(static::TEMPLATE);

        parent::__construct("@CoreHome\_uiControl");

        $this->clientSideProperties = array();
        $this->clientSideParameters = array();
    }

    /**
     * Sets a variable. See {@link View::__set()}.
     */
    public function __set($key, $val)
    {
        $this->innerView->__set($key, $val);
    }

    /**
     * Gets a view variable. See {@link View::__get()}.
     */
    public function &__get($key)
    {
        return $this->innerView->__get($key);
    }

    public function __isset($key)
    {
        return isset($this->innerView->templateVars[$key]);
    }

    /**
     * Renders the control view within a containing <div> that is used by the UIControl JavaScript
     * class.
     *
     * @return string
     */
    public function render()
    {
        if ($this->cssIdentifier === null) {
            throw new Exception("All UIControls must set a cssIdentifier property");
        }

        if ($this->jsClass === null) {
            throw new Exception("All UIControls must set a jsClass property");
        }

        $this->getTemplateVars();
        return parent::render();
    }

    /**
     * See {@link View::getTemplateVars()}.
     */
    public function getTemplateVars($override = array())
    {
        $this->templateVars['implView'] = $this->innerView;
        $this->templateVars['clientSideProperties'] = $this->clientSideProperties;
        $this->templateVars['clientSideParameters'] = $this->clientSideParameters;
        $this->templateVars['cssIdentifier'] = $this->cssIdentifier;
        $this->templateVars['cssClass'] = $this->cssClass;
        $this->templateVars['jsClass'] = $this->jsClass;

        return parent::getTemplateVars($override);
    }
}