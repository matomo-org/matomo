<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * Class to dynamically create an HTML SELECT
 * 
 * PHP versions 4 and 5
 *
 * LICENSE: This source file is subject to version 3.01 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_01.txt If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category    HTML
 * @package     HTML_QuickForm
 * @author      Adam Daniel <adaniel1@eesus.jnj.com>
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 * @author      Alexey Borzov <avb@php.net>
 * @copyright   2001-2007 The PHP Group
 * @license     http://www.php.net/license/3_01.txt PHP License 3.01
 * @version     CVS: $Id$
 * @link        http://pear.php.net/package/HTML_QuickForm
 */

/**
 * Base class for form elements
 */ 
require_once 'HTML/QuickForm/element.php';

/**
 * Class to dynamically create an HTML SELECT
 *
 * @category    HTML
 * @package     HTML_QuickForm
 * @author      Adam Daniel <adaniel1@eesus.jnj.com>
 * @author      Bertrand Mansion <bmansion@mamasam.com>
 * @author      Alexey Borzov <avb@php.net>
 * @version     Release: 3.2.9
 * @since       1.0
 */
class HTML_QuickForm_select extends HTML_QuickForm_element {
    
    // {{{ properties

    /**
     * Contains the select options
     *
     * @var       array
     * @since     1.0
     * @access    private
     */
    var $_options = array();
    
    /**
     * Default values of the SELECT
     * 
     * @var       string
     * @since     1.0
     * @access    private
     */
    var $_values = null;

    // }}}
    // {{{ constructor
        
    /**
     * Class constructor
     * 
     * @param     string    Select name attribute
     * @param     mixed     Label(s) for the select
     * @param     mixed     Data to be used to populate options
     * @param     mixed     Either a typical HTML attribute string or an associative array
     * @since     1.0
     * @access    public
     * @return    void
     */
    function HTML_QuickForm_select($elementName=null, $elementLabel=null, $options=null, $attributes=null)
    {
        HTML_QuickForm_element::HTML_QuickForm_element($elementName, $elementLabel, $attributes);
        $this->_persistantFreeze = true;
        $this->_type = 'select';
        if (isset($options)) {
            $this->load($options);
        }
    } //end constructor
    
    // }}}
    // {{{ apiVersion()

    /**
     * Returns the current API version 
     * 
     * @since     1.0
     * @access    public
     * @return    double
     */
    function apiVersion()
    {
        return 2.3;
    } //end func apiVersion

    // }}}
    // {{{ setSelected()

    /**
     * Sets the default values of the select box
     * 
     * @param     mixed    $values  Array or comma delimited string of selected values
     * @since     1.0
     * @access    public
     * @return    void
     */
    function setSelected($values)
    {
        if (is_string($values) && $this->getMultiple()) {
            $values = split("[ ]?,[ ]?", $values);
        }
        if (is_array($values)) {
            $this->_values = array_values($values);
        } else {
            $this->_values = array($values);
        }
    } //end func setSelected
    
    // }}}
    // {{{ getSelected()

    /**
     * Returns an array of the selected values
     * 
     * @since     1.0
     * @access    public
     * @return    array of selected values
     */
    function getSelected()
    {
        return $this->_values;
    } // end func getSelected

    // }}}
    // {{{ setName()

    /**
     * Sets the input field name
     * 
     * @param     string    $name   Input field name attribute
     * @since     1.0
     * @access    public
     * @return    void
     */
    function setName($name)
    {
        $this->updateAttributes(array('name' => $name));
    } //end func setName
    
    // }}}
    // {{{ getName()

    /**
     * Returns the element name
     * 
     * @since     1.0
     * @access    public
     * @return    string
     */
    function getName()
    {
        return $this->getAttribute('name');
    } //end func getName

    // }}}
    // {{{ getPrivateName()

    /**
     * Returns the element name (possibly with brackets appended)
     * 
     * @since     1.0
     * @access    public
     * @return    string
     */
    function getPrivateName()
    {
        if ($this->getAttribute('multiple')) {
            return $this->getName() . '[]';
        } else {
            return $this->getName();
        }
    } //end func getPrivateName

    // }}}
    // {{{ setValue()

    /**
     * Sets the value of the form element
     *
     * @param     mixed    $values  Array or comma delimited string of selected values
     * @since     1.0
     * @access    public
     * @return    void
     */
    function setValue($value)
    {
        $this->setSelected($value);
    } // end func setValue

    // }}}
    // {{{ getValue()

    /**
     * Returns an array of the selected values
     * 
     * @since     1.0
     * @access    public
     * @return    array of selected values
     */
    function getValue()
    {
        return $this->_values;
    } // end func getValue

    // }}}
    // {{{ setSize()

    /**
     * Sets the select field size, only applies to 'multiple' selects
     * 
     * @param     int    $size  Size of select  field
     * @since     1.0
     * @access    public
     * @return    void
     */
    function setSize($size)
    {
        $this->updateAttributes(array('size' => $size));
    } //end func setSize
    
    // }}}
    // {{{ getSize()

    /**
     * Returns the select field size
     * 
     * @since     1.0
     * @access    public
     * @return    int
     */
    function getSize()
    {
        return $this->getAttribute('size');
    } //end func getSize

    // }}}
    // {{{ setMultiple()

    /**
     * Sets the select mutiple attribute
     * 
     * @param     bool    $multiple  Whether the select supports multi-selections
     * @since     1.2
     * @access    public
     * @return    void
     */
    function setMultiple($multiple)
    {
        if ($multiple) {
            $this->updateAttributes(array('multiple' => 'multiple'));
        } else {
            $this->removeAttribute('multiple');
        }
    } //end func setMultiple
    
    // }}}
    // {{{ getMultiple()

    /**
     * Returns the select mutiple attribute
     * 
     * @since     1.2
     * @access    public
     * @return    bool    true if multiple select, false otherwise
     */
    function getMultiple()
    {
        return (bool)$this->getAttribute('multiple');
    } //end func getMultiple

    // }}}
    // {{{ addOption()

    /**
     * Adds a new OPTION to the SELECT
     *
     * @param     string    $text       Display text for the OPTION
     * @param     string    $value      Value for the OPTION
     * @param     mixed     $attributes Either a typical HTML attribute string 
     *                                  or an associative array
     * @since     1.0
     * @access    public
     * @return    void
     */
    function addOption($text, $value, $attributes=null)
    {
        if (null === $attributes) {
            $attributes = array('value' => (string)$value);
        } else {
            $attributes = $this->_parseAttributes($attributes);
            if (isset($attributes['selected'])) {
                // the 'selected' attribute will be set in toHtml()
                $this->_removeAttr('selected', $attributes);
                if (is_null($this->_values)) {
                    $this->_values = array($value);
                } elseif (!in_array($value, $this->_values)) {
                    $this->_values[] = $value;
                }
            }
            $this->_updateAttrArray($attributes, array('value' => (string)$value));
        }
        $this->_options[] = array('text' => $text, 'attr' => $attributes);
    } // end func addOption
    
    // }}}
    // {{{ loadArray()

    /**
     * Loads the options from an associative array
     * 
     * @param     array    $arr     Associative array of options
     * @param     mixed    $values  (optional) Array or comma delimited string of selected values
     * @since     1.0
     * @access    public
     * @return    PEAR_Error on error or true
     * @throws    PEAR_Error
     */
    function loadArray($arr, $values=null)
    {
        if (!is_array($arr)) {
            return PEAR::raiseError('Argument 1 of HTML_Select::loadArray is not a valid array');
        }
        if (isset($values)) {
            $this->setSelected($values);
        }
        foreach ($arr as $key => $val) {
            // Warning: new API since release 2.3
            $this->addOption($val, $key);
        }
        return true;
    } // end func loadArray

    // }}}
    // {{{ loadDbResult()

    /**
     * Loads the options from DB_result object
     * 
     * If no column names are specified the first two columns of the result are
     * used as the text and value columns respectively
     * @param     object    $result     DB_result object 
     * @param     string    $textCol    (optional) Name of column to display as the OPTION text 
     * @param     string    $valueCol   (optional) Name of column to use as the OPTION value 
     * @param     mixed     $values     (optional) Array or comma delimited string of selected values
     * @since     1.0
     * @access    public
     * @return    PEAR_Error on error or true
     * @throws    PEAR_Error
     */
    function loadDbResult(&$result, $textCol=null, $valueCol=null, $values=null)
    {
        if (!is_object($result) || !is_a($result, 'db_result')) {
            return PEAR::raiseError('Argument 1 of HTML_Select::loadDbResult is not a valid DB_result');
        }
        if (isset($values)) {
            $this->setValue($values);
        }
        $fetchMode = ($textCol && $valueCol) ? DB_FETCHMODE_ASSOC : DB_FETCHMODE_ORDERED;
        while (is_array($row = $result->fetchRow($fetchMode)) ) {
            if ($fetchMode == DB_FETCHMODE_ASSOC) {
                $this->addOption($row[$textCol], $row[$valueCol]);
            } else {
                $this->addOption($row[0], $row[1]);
            }
        }
        return true;
    } // end func loadDbResult
    
    // }}}
    // {{{ loadQuery()

    /**
     * Queries a database and loads the options from the results
     *
     * @param     mixed     $conn       Either an existing DB connection or a valid dsn 
     * @param     string    $sql        SQL query string
     * @param     string    $textCol    (optional) Name of column to display as the OPTION text 
     * @param     string    $valueCol   (optional) Name of column to use as the OPTION value 
     * @param     mixed     $values     (optional) Array or comma delimited string of selected values
     * @since     1.1
     * @access    public
     * @return    void
     * @throws    PEAR_Error
     */
    function loadQuery(&$conn, $sql, $textCol=null, $valueCol=null, $values=null)
    {
        if (is_string($conn)) {
            require_once('DB.php');
            $dbConn = &DB::connect($conn, true);
            if (DB::isError($dbConn)) {
                return $dbConn;
            }
        } elseif (is_subclass_of($conn, "db_common")) {
            $dbConn = &$conn;
        } else {
            return PEAR::raiseError('Argument 1 of HTML_Select::loadQuery is not a valid type');
        }
        $result = $dbConn->query($sql);
        if (DB::isError($result)) {
            return $result;
        }
        $this->loadDbResult($result, $textCol, $valueCol, $values);
        $result->free();
        if (is_string($conn)) {
            $dbConn->disconnect();
        }
        return true;
    } // end func loadQuery

    // }}}
    // {{{ load()

    /**
     * Loads options from different types of data sources
     *
     * This method is a simulated overloaded method.  The arguments, other than the
     * first are optional and only mean something depending on the type of the first argument.
     * If the first argument is an array then all arguments are passed in order to loadArray.
     * If the first argument is a db_result then all arguments are passed in order to loadDbResult.
     * If the first argument is a string or a DB connection then all arguments are 
     * passed in order to loadQuery.
     * @param     mixed     $options     Options source currently supports assoc array or DB_result
     * @param     mixed     $param1     (optional) See function detail
     * @param     mixed     $param2     (optional) See function detail
     * @param     mixed     $param3     (optional) See function detail
     * @param     mixed     $param4     (optional) See function detail
     * @since     1.1
     * @access    public
     * @return    PEAR_Error on error or true
     * @throws    PEAR_Error
     */
    function load(&$options, $param1=null, $param2=null, $param3=null, $param4=null)
    {
        switch (true) {
            case is_array($options):
                return $this->loadArray($options, $param1);
                break;
            case (is_a($options, 'db_result')):
                return $this->loadDbResult($options, $param1, $param2, $param3);
                break;
            case (is_string($options) && !empty($options) || is_subclass_of($options, "db_common")):
                return $this->loadQuery($options, $param1, $param2, $param3, $param4);
                break;
        }
    } // end func load
    
    // }}}
    // {{{ toHtml()

    /**
     * Returns the SELECT in HTML
     *
     * @since     1.0
     * @access    public
     * @return    string
     */
    function toHtml()
    {
        if ($this->_flagFrozen) {
            return $this->getFrozenHtml();
        } else {
            $tabs    = $this->_getTabs();
            $strHtml = '';

            if ($this->getComment() != '') {
                $strHtml .= $tabs . '<!-- ' . $this->getComment() . " //-->\n";
            }

            if (!$this->getMultiple()) {
                $attrString = $this->_getAttrString($this->_attributes);
            } else {
                $myName = $this->getName();
                $this->setName($myName . '[]');
                $attrString = $this->_getAttrString($this->_attributes);
                $this->setName($myName);
            }
            $strHtml .= $tabs . '<select' . $attrString . ">\n";

            $strValues = is_array($this->_values)? array_map('strval', $this->_values): array();
            foreach ($this->_options as $option) {
                if (!empty($strValues) && in_array($option['attr']['value'], $strValues, true)) {
                    $option['attr']['selected'] = 'selected';
                }
                $strHtml .= $tabs . "\t<option" . $this->_getAttrString($option['attr']) . '>' .
                            $option['text'] . "</option>\n";
            }

            return $strHtml . $tabs . '</select>';
        }
    } //end func toHtml
    
    // }}}
    // {{{ getFrozenHtml()

    /**
     * Returns the value of field without HTML tags
     * 
     * @since     1.0
     * @access    public
     * @return    string
     */
    function getFrozenHtml()
    {
        $value = array();
        if (is_array($this->_values)) {
            foreach ($this->_values as $key => $val) {
                for ($i = 0, $optCount = count($this->_options); $i < $optCount; $i++) {
                    if (0 == strcmp($val, $this->_options[$i]['attr']['value'])) {
                        $value[$key] = $this->_options[$i]['text'];
                        break;
                    }
                }
            }
        }
        $html = empty($value)? '&nbsp;': join('<br />', $value);
        if ($this->_persistantFreeze) {
            $name = $this->getPrivateName();
            // Only use id attribute if doing single hidden input
            if (1 == count($value)) {
                $id     = $this->getAttribute('id');
                $idAttr = isset($id)? array('id' => $id): array();
            } else {
                $idAttr = array();
            }
            foreach ($value as $key => $item) {
                $html .= '<input' . $this->_getAttrString(array(
                             'type'  => 'hidden',
                             'name'  => $name,
                             'value' => $this->_values[$key]
                         ) + $idAttr) . ' />';
            }
        }
        return $html;
    } //end func getFrozenHtml

    // }}}
    // {{{ exportValue()

   /**
    * We check the options and return only the values that _could_ have been
    * selected. We also return a scalar value if select is not "multiple"
    */
    function exportValue(&$submitValues, $assoc = false)
    {
        $value = $this->_findValue($submitValues);
        if (is_null($value)) {
            $value = $this->getValue();
        } elseif(!is_array($value)) {
            $value = array($value);
        }
        if (is_array($value) && !empty($this->_options)) {
            $cleanValue = null;
            foreach ($value as $v) {
                for ($i = 0, $optCount = count($this->_options); $i < $optCount; $i++) {
                    if (0 == strcmp($v, $this->_options[$i]['attr']['value'])) {
                        $cleanValue[] = $v;
                        break;
                    }
                }
            }
        } else {
            $cleanValue = $value;
        }
        if (is_array($cleanValue) && !$this->getMultiple()) {
            return $this->_prepareValue($cleanValue[0], $assoc);
        } else {
            return $this->_prepareValue($cleanValue, $assoc);
        }
    }
    
    // }}}
    // {{{ onQuickFormEvent()

    function onQuickFormEvent($event, $arg, &$caller)
    {
        if ('updateValue' == $event) {
            $value = $this->_findValue($caller->_constantValues);
            if (null === $value) {
                $value = $this->_findValue($caller->_submitValues);
                // Fix for bug #4465 & #5269
                // XXX: should we push this to element::onQuickFormEvent()?
                if (null === $value && (!$caller->isSubmitted() || !$this->getMultiple())) {
                    $value = $this->_findValue($caller->_defaultValues);
                }
            }
            if (null !== $value) {
                $this->setValue($value);
            }
            return true;
        } else {
            return parent::onQuickFormEvent($event, $arg, $caller);
        }
    }

    // }}}
} //end class HTML_QuickForm_select
?>
