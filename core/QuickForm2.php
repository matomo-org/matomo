<?php
/**
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 *
 */

namespace Piwik;

use HTML_QuickForm2;
use HTML_QuickForm2_InvalidArgumentException;
use HTML_QuickForm2_Node;
use HTML_QuickForm2_NotFoundException;
use HTML_QuickForm2_Renderer;

/**
 * Manages forms displayed in Twig
 *
 * For an example, @see Piwik\Plugins\Login\FormLogin
 *
 * @see                 HTML_QuickForm2, libs/HTML/QuickForm2.php
 * @link http://pear.php.net/package/HTML_QuickForm2/
 */
abstract class QuickForm2 extends HTML_QuickForm2
{
    protected $a_formElements = array();

    public function __construct($id, $method = 'post', $attributes = null, $trackSubmit = false)
    {
        if (!isset($attributes['action'])) {
            $attributes['action'] = Url::getCurrentQueryString();
        }
        if (!isset($attributes['name'])) {
            $attributes['name'] = $id;
        }
        parent::__construct($id, $method, $attributes, $trackSubmit);

        $this->init();
    }

    /**
     * Class specific initialization
     */
    abstract public function init();

    /**
     * The elements in this form
     *
     * @return array Element names
     */
    public function getElementList()
    {
        return $this->a_formElements;
    }

    /**
     * Wrapper around HTML_QuickForm2_Container's addElement()
     *
     * @param    string|HTML_QuickForm2_Node $elementOrType Either type name (treated
     *               case-insensitively) or an element instance
     * @param    mixed $name Element name
     * @param    mixed $attributes Element attributes
     * @param    array $data Element-specific data
     * @return   HTML_QuickForm2_Node     Added element
     * @throws   HTML_QuickForm2_InvalidArgumentException
     * @throws   HTML_QuickForm2_NotFoundException
     */
    public function addElement($elementOrType, $name = null, $attributes = null,
                               array $data = array())
    {
        if ($name != 'submit') {
            $this->a_formElements[] = $name;
        }

        return parent::addElement($elementOrType, $name, $attributes, $data);
    }

    public function setChecked($nameElement)
    {
        foreach ($this->_elements as $key => $value) {
            if ($value->_attributes['name'] == $nameElement) {
                $this->_elements[$key]->_attributes['checked'] = 'checked';
            }
        }
    }

    public function setSelected($nameElement, $value)
    {
        foreach ($this->_elements as $key => $value) {
            if ($value->_attributes['name'] == $nameElement) {
                $this->_elements[$key]->_attributes['selected'] = 'selected';
            }
        }
    }

    /**
     * Ported from HTML_QuickForm to minimize changes to Controllers
     *
     * @param string $elementName
     * @return mixed
     */
    public function getSubmitValue($elementName)
    {
        $value = $this->getValue();
        return isset($value[$elementName]) ? $value[$elementName] : null;
    }

    public function getErrorMessages()
    {
        $messages = array();

        foreach ($this as $element) {
            $messages[] = $element->getError();
        }

        return array_filter($messages);
    }

    protected static $registered = false;

    /**
     * Returns the rendered form as an array.
     *
     * @param bool $groupErrors Whether to group errors together or not.
     * @return array
     */
    public function getFormData($groupErrors = true)
    {
        if (!self::$registered) {
            HTML_QuickForm2_Renderer::register('smarty', 'HTML_QuickForm2_Renderer_Smarty');
            self::$registered = true;
        }

        // Create the renderer object
        $renderer = HTML_QuickForm2_Renderer::factory('smarty');
        $renderer->setOption('group_errors', $groupErrors);

        // build the HTML for the form
        $this->render($renderer);

        return $renderer->toArray();
    }
}
