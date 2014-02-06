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
     * Whether we are currently rendering the containing div or not.
     */
    private $renderingContainer = false;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct(static::TEMPLATE);

        $this->clientSideProperties = array();
        $this->clientSideParameters = array();
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

        if ($this->renderingContainer) {
            return parent::render();
        } else {
            $this->renderingContainer = true;

            $surroundingDivView = new View("@CoreHome\_uiControl");
            $surroundingDivView->clientSideProperties = $this->clientSideProperties;
            $surroundingDivView->clientSideParameters = $this->clientSideParameters;
            $surroundingDivView->implView = $this;
            $surroundingDivView->cssIdentifier = $this->cssIdentifier;
            $surroundingDivView->cssClass = $this->cssClass;
            $surroundingDivView->jsClass = $this->jsClass;

            $result = $surroundingDivView->render();

            $this->renderingContainer = false;

            return $result;
        }
    }
}