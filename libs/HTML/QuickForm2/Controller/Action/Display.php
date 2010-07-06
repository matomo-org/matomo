<?php
/**
 * Action handler for outputting the form
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
 * @version    SVN: $Id: Display.php 294028 2010-01-25 23:09:11Z avb $
 * @link       http://pear.php.net/package/HTML_QuickForm2
 */

/** Interface for Controller action handlers */
// require_once 'HTML/QuickForm2/Controller/Action.php';

/** Class presenting the values stored in session by Controller as submitted ones */
// require_once 'HTML/QuickForm2/DataSource/Session.php';

/**
 * Action handler for outputting the form
 *
 * If you want to customize the form display, subclass this class and override
 * the renderForm() method, you don't need to change the perform() method.
 *
 * @category   HTML
 * @package    HTML_QuickForm2
 * @author     Alexey Borzov <avb@php.net>
 * @author     Bertrand Mansion <golgote@mamasam.com>
 * @version    Release: @package_version@
 */
class HTML_QuickForm2_Controller_Action_Display
    implements HTML_QuickForm2_Controller_Action
{
    public function perform(HTML_QuickForm2_Controller_Page $page, $name)
    {
        $validate        = false;
        $datasources     = $page->getForm()->getDataSources();
        $container       = $page->getController()->getSessionContainer();
        list(, $oldName) = $page->getController()->getActionName();
        // Check the original action name, we need to do additional processing
        // if it was 'display'
        if ('display' == $oldName) {
            // In case of wizard-type controller we should not allow access to
            // a page unless all previous pages are valid (see also bug #2323)
            if ($page->getController()->isWizard()
                && !$page->getController()->isValid($page)
            ) {
                return $page->getController()->getFirstInvalidPage()->handle('jump');
            }
            // If we have values in container then we should inject the Session
            // DataSource, if page was invalid previously we should later call
            // validate() to get the errors
            if (count($container->getValues($page->getForm()->getId()))) {
                array_unshift($datasources, new HTML_QuickForm2_DataSource_Session(
                    $container->getValues($page->getForm()->getId())
                ));
                $validate = false === $container->getValidationStatus($page->getForm()->getId());
            }
        }

        // Add "defaults" datasources stored in session
        $page->getForm()->setDataSources(array_merge($datasources, $container->getDatasources()));
        $page->populateFormOnce();
        if ($validate) {
            $page->getForm()->validate();
        }
        return $this->renderForm($page->getForm());
    }

   /**
    * Outputs the form
    *
    * Default behaviour is to rely on form's __toString() magic method.
    * If you want to customize form appearance or use a different Renderer,
    * you should override this method.
    *
    * @param    HTML_QuickForm2
    */
    protected function renderForm(HTML_QuickForm2 $form)
    {
        echo $form;
    }
}
?>
