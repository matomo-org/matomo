<?php
/**
 * Class representing a page of a multipage form
 *
 * PHP version 5
 *
 * LICENSE:
 *
 * Copyright (c) 2006-2010, Alexey Borzov <avb@php.net>,
 *                          Bertrand Mansion <golgote@mamasam.com>
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *    * Redistributions of source code must retain the above copyright
 *      notice, this list of conditions and the following disclaimer.
 *    * Redistributions in binary form must reproduce the above copyright
 *      notice, this list of conditions and the following disclaimer in the
 *      documentation and/or other materials provided with the distribution.
 *    * The names of the authors may not be used to endorse or promote products
 *      derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS
 * IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
 * THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR
 * CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL,
 * EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR
 * PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY
 * OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING
 * NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @license    http://opensource.org/licenses/bsd-license.php New BSD License
 * @version    SVN: $Id: Page.php 295963 2010-03-08 14:33:43Z avb $
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/**
 * Class representing a page of a multipage form
 *
 * Unlike old HTML_QuickForm_Controller, this does not extend HTML_QuickForm2
 * but accepts an instance of that in the constructor. You need to create a
 * subclass of this class and implement its populateForm() method.
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
abstract class HTML_QuickForm2_Controller_Page
{
   /**
    * Button name template (needs form ID and action name substituted by sprintf())
    */
    const KEY_NAME = '_qf_%s_%s';

   /**
    * Whether populateForm() was already called
    * @var  boolean
    */
    private $_formPopulated = false;

   /**
    * The form wrapped by this page
    * @var  HTML_QuickForm2
    */
    protected $form = null;

   /**
    * Controller this page belongs to
    * @var  HTML_QuickForm2_Controller
    */
    protected $controller = null;

   /**
    * Contains the mapping of action names to handlers (objects implementing HTML_QuickForm2_Controller_Action)
    * @var  array
    */
    protected $handlers = array();

   /**
    * Class constructor, accepts the form to wrap around
    *
    * @param    HTML_QuickForm2
    */
    public function __construct(HTML_QuickForm2 $form)
    {
        $this->form = $form;
    }

   /**
    * Returns the form this page wraps around
    *
    * @return   HTML_QuickForm2
    */
    public function getForm()
    {
        return $this->form;
    }

   /**
    * Sets the controller owning the page
    *
    * @param    HTML_QuickForm2_Controller  controller the page belongs to
    */
    public function setController(HTML_QuickForm2_Controller $controller)
    {
        $this->controller = $controller;
    }

   /**
    * Returns the controller owning this page
    *
    * @return   HTML_QuickForm2_Controller
    */
    public function getController()
    {
        return $this->controller;
    }

   /**
    * Adds a handler for a specific action
    *
    * @param  string                            action name
    * @param  HTML_QuickForm2_Controller_Action the handler for the action
    */
    public function addHandler($actionName, HTML_QuickForm2_Controller_Action $action)
    {
        $this->handlers[$actionName] = $action;
    }

   /**
    * Handles an action
    *
    * If the page does not contain a handler for this action, controller's
    * handle() method will be called.
    *
    * @param    string      Name of the action
    * @throws   HTML_QuickForm2_NotFoundException   if handler for an action is missing
    */
    public function handle($actionName)
    {
        if (isset($this->handlers[$actionName])) {
            return $this->handlers[$actionName]->perform($this, $actionName);
        } else {
            return $this->getController()->handle($this, $actionName);
        }
    }

   /**
    * Returns a name for a submit button that will invoke a specific action
    *
    * @param  string  Name of the action
    * @return string  "name" attribute for a submit button
    */
    public function getButtonName($actionName)
    {
        return sprintf(self::KEY_NAME, $this->getForm()->getId(), $actionName);
    }

   /**
    * Sets the default action invoked on page-form submit
    *
    * This is necessary as the user may just press Enter instead of
    * clicking one of the named submit buttons and then no action name will
    * be passed to the script.
    *
    * @param  string    Default action name
    * @param  string    Path to a 1x1 transparent GIF image
    * @return object    Returns the image input used for default action
    */
    public function setDefaultAction($actionName, $imageSrc = '')
    {
        // require_once 'HTML/QuickForm2/Controller/DefaultAction.php';

        if (0 == count($this->form)) {
            $image = $this->form->appendChild(
                new HTML_QuickForm2_Controller_DefaultAction(
                    $this->getButtonName($actionName), array('src' => $imageSrc)
                )
            );

        // replace the existing DefaultAction
        } elseif ($image = $this->form->getElementById('_qf_default')) {
            $image->setName($this->getButtonName($actionName))
                  ->setAttribute('src', $imageSrc);

        // Inject the element to the first position to improve chances that
        // it ends up on top in the output
        } else {
            $it = $this->form->getIterator();
            $it->rewind();
            $image = $this->form->insertBefore(
                new HTML_QuickForm2_Controller_DefaultAction(
                    $this->getButtonName($actionName), array('src' => $imageSrc)
                ),
                $it->current()
            );
        }
        return $image;
    }

   /**
    * Wrapper around populateForm() ensuring that it is only called once
    */
    final public function populateFormOnce()
    {
        if (!$this->_formPopulated) {
            if (!empty($this->controller) && $this->controller->propagateId()) {
                $this->form->addElement(
                    'hidden', HTML_QuickForm2_Controller::KEY_ID,
                    array('id' => HTML_QuickForm2_Controller::KEY_ID)
                )->setValue($this->controller->getId());
            }
            $this->populateForm();
            $this->_formPopulated = true;
        }
    }

   /**
    * Populates the form with the elements
    *
    * The implementation of this method in your subclass of
    * HTML_QuickForm2_Controller_Page should contain all the necessary
    * addElement(), addRule() etc. calls. The method will only be called if
    * needed to prevent wasting resources on the forms that aren't going to
    * be seen by the user.
    */
    abstract protected function populateForm();

   /**
    * Stores the form values (and validation status) is session container
    *
    * @param    bool    Whether to store validation status
    */
    public function storeValues($validate = true)
    {
        $this->populateFormOnce();
        $container = $this->getController()->getSessionContainer();
        $id        = $this->form->getId();

        $container->storeValues($id, (array)$this->form->getValue());
        if ($validate) {
            $container->storeValidationStatus($id, $this->form->validate());
        }
        return $container->getValidationStatus($id);
    }
}
?>
