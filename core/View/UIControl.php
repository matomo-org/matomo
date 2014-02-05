<?php
/**
 * Piwik - Open source web analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */
namespace Piwik\View;

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
     * Constructor.
     */
    public function __construct() {
        parent::__construct(static::TEMPLATE);

        $this->clientSideProperties = array();
        $this->clientSideParameters = array();
    }
}