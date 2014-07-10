<?php
/**
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\View;

use Exception;
use Piwik\View;

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
     * This field must be set prior to rendering.
     *
     * @var string
     */
    public $jsClass = null;

    /**
     * The JavaScript module that contains the JavaScript class.
     *
     * @var string
     */
    public $jsNamespace = 'piwik/UI';

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

        return parent::render();
    }

    /**
     * See {@link View::getTemplateVars()}.
     */
    public function getTemplateVars($override = array())
    {
        $this->templateVars['implView'] = $this->innerView;
        $this->templateVars['cssIdentifier'] = $this->cssIdentifier;
        $this->templateVars['cssClass'] = $this->cssClass;
        $this->templateVars['jsClass'] = $this->jsClass;
        $this->templateVars['jsNamespace'] = $this->jsNamespace;
        $this->templateVars['implOverride'] = $override;

        $innerTemplateVars = $this->innerView->getTemplateVars($override);

        $this->templateVars['clientSideProperties'] = array();
        foreach ($this->getClientSideProperties() as $name) {
            $this->templateVars['clientSideProperties'][$name] = $innerTemplateVars[$name];
        }

        $this->templateVars['clientSideParameters'] = array();
        foreach ($this->getClientSideParameters() as $name) {
            $this->templateVars['clientSideParameters'][$name] = $innerTemplateVars[$name];
        }

        return parent::getTemplateVars($override);
    }

    /**
     * Returns the array of property names whose values are passed to the UIControl JavaScript class.
     *
     * Should be overriden by descendants.
     *
     * @return array
     */
    public function getClientSideProperties()
    {
        return array();
    }

    /**
     * Returns an array of property names whose values are passed to the UIControl JavaScript class.
     * These values differ from those in {@link $clientSideProperties} in that they are meant to passed as
     * request parameters when the JavaScript code makes an AJAX request.
     *
     * Should be overriden by descendants.
     *
     * @return array
     */
    public function getClientSideParameters()
    {
        return array();
    }
}