<?php
// +-----------------------------------------------------------------------+
// | Copyright (c) 2002, Richard Heyes, Harald Radi                        |
// | All rights reserved.                                                  |
// |                                                                       |
// | Redistribution and use in source and binary forms, with or without    |
// | modification, are permitted provided that the following conditions    |
// | are met:                                                              |
// |                                                                       |
// | o Redistributions of source code must retain the above copyright      |
// |   notice, this list of conditions and the following disclaimer.       |
// | o Redistributions in binary form must reproduce the above copyright   |
// |   notice, this list of conditions and the following disclaimer in the |
// |   documentation and/or other materials provided with the distribution.| 
// | o The names of the authors may not be used to endorse or promote      |
// |   products derived from this software without specific prior written  |
// |   permission.                                                         |
// |                                                                       |
// | THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS   |
// | "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT     |
// | LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR |
// | A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT  |
// | OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, |
// | SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT      |
// | LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, |
// | DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY |
// | THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT   |
// | (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE |
// | OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.  |
// |                                                                       |
// +-----------------------------------------------------------------------+
// | Author: Richard Heyes <richard@phpguru.org>                           |
// |         Harald Radi <harald.radi@nme.at>                              |
// +-----------------------------------------------------------------------+
//
// $Id$

/**
* @package HTML_TreeMenu
*/
/**
* HTML_TreeMenu Class
*
* A simple couple of PHP classes and some not so simple
* Jabbascript which produces a tree menu. In IE this menu
* is dynamic, with branches being collapsable. In IE5+ the
* status of the collapsed/open branches persists across page
* refreshes.In any other browser the tree is static. Code is
* based on work of Harald Radi.
*
* Usage.
*
* After installing the package, copy the example php script to
* your servers document root. Also place the TreeMenu.js and the
* images folder in the same place. Running the script should
* then produce the tree.
*
* Thanks go to Chip Chapin (http://www.chipchapin.com) for many
* excellent ideas and improvements.
*
* @author  Richard Heyes <richard@php.net>
* @author  Harald Radi <harald.radi@nme.at>
* @access  public
* @package HTML_TreeMenu
*/

class HTML_TreeMenu
{
    /**
    * Indexed array of subnodes
    * @var array
    */
    var $items;

    /**
    * Constructor
    *
    * @access public
    */
    function HTML_TreeMenu()
    {
        // Not much to do here :(
    }

    /**
    * This function adds an item to the the tree.
    *
    * @access public
    * @param  object $node The node to add. This object should be
    *                      a HTML_TreeNode object.
    * @return object       Returns a reference to the new node inside
    *                      the tree.
    */
    function &addItem(&$node)
    {
        $this->items[] = &$node;
        return $this->items[count($this->items) - 1];
    }
} // HTML_TreeMenu


/**
* HTML_TreeNode class
* 
* This class is supplementary to the above and provides a way to
* add nodes to the tree. A node can have other nodes added to it. 
*
* @author  Richard Heyes <richard@php.net>
* @author  Harald Radi <harald.radi@nme.at>
* @access  public
* @package HTML_TreeMenu
*/
class HTML_TreeNode
{
    /**
    * The text for this node.
    * @var string
    */
    var $text;

    /**
    * The link for this node.
    * @var string
    */
    var $link;

    /**
    * The icon for this node.
    * @var string
    */
    var $icon;
    
    /**
    * The css class for this node
    * @var string
    */
    var $cssClass;

    /**
    * Indexed array of subnodes
    * @var array
    */
    var $items;

    /**
    * Whether this node is expanded or not
    * @var bool
    */
    var $expanded;
    
    /**
    * Whether this node is dynamic or not
    * @var bool
    */
    var $isDynamic;
    
    /**
    * Should this node be made visible?
    * @var bool
    */
    var $ensureVisible;
    
    /**
    * The parent node. Null if top level
    * @var object
    */
    var $parent;

    /**
    * Javascript event handlers;
    * @var array
    */
    var $events;

    /**
    * Constructor
    *
    * @access public
    * @param  array $options An array of options which you can pass to change
    *                        the way this node looks/acts. This can consist of:
    *                         o text          The title of the node, defaults to blank
    *                         o link          The link for the node, defaults to blank
    *                         o icon          The icon for the node, defaults to blank
    *                         o class         The CSS class for this node, defaults to blank
    *                         o expanded      The default expanded status of this node, defaults to false
    *                                         This doesn't affect non dynamic presentation types
    *                         o isDynamic     If this node is dynamic or not. Only affects
    *                                         certain presentation types.
    *                         o ensureVisible If true this node will be made visible despite the expanded
    *                                         settings, and client side persistence. Will not affect
    *                                         some presentation styles, such as Listbox. Default is false
    * @param  array $events An array of javascript events and the corresponding event handlers.
    *                       Additionally to the standard javascript events you can specify handlers
    *                       for the 'onexpand', 'oncollapse' and 'ontoggle' events which will be fired
    *                       whenever a node is collapsed and/or expanded.
    */
    function HTML_TreeNode($options = array(), $events = array())
    {
        $this->text          = '';
        $this->link          = '';
        $this->icon          = '';
        $this->cssClass      = '';
        $this->expanded      = false;
        $this->isDynamic     = true;
        $this->ensureVisible = false;

        $this->parent        = null;
        $this->events        = $events;
        
        foreach ($options as $option => $value) {
            $this->$option = $value;
        }
    }

    /**
    * Allows setting of various parameters after the initial
    * constructor call. Possible options you can set are:
    *  o text
    *  o link
    *  o icon
    *  o cssClass
    *  o expanded
    *  o isDynamic
    *  o ensureVisible
    * ie The same options as in the constructor
    *
    * @access public
    * @param  string $option Option to set
    * @param  string $value  Value to set the option to
    */
    function setOption($option, $value)
    {
        $this->$option = $value;
    }

    /**
    * Adds a new subnode to this node.
    *
    * @access public
    * @param  object $node The new node
    */
    function &addItem(&$node)
    {
        $node->parent  = &$this;
        $this->items[] = &$node;
        
        /**
        * If the subnode has ensureVisible set it needs
        * to be handled, and all parents set accordingly.
        */
        if ($node->ensureVisible) {
            $this->_ensureVisible();
        }

        return $this->items[count($this->items) - 1];
    }
    
    /**
    * Private function to handle ensureVisible stuff
    *
    * @access private
    */
    function _ensureVisible()
    {
        $this->ensureVisible = true;
        $this->expanded      = true;

        if (!is_null($this->parent)) {
            $this->parent->_ensureVisible();
        }
    }
} // HTML_TreeNode


/**
* HTML_TreeMenu_Presentation class
* 
* Base class for other presentation classes to
* inherit from.
* @package HTML_TreeMenu
*/
class HTML_TreeMenu_Presentation
{
    /**
    * The TreeMenu structure
    * @var object
    */
    var $menu;

    /**
    * Base constructor simply sets the menu object
    *
    * @param object $structure The menu structure
    */
    function HTML_TreeMenu_Presentation(&$structure)
    {
        $this->menu = &$structure;
    }

    /**
    * Prints the HTML generated by the toHTML() method.
    * toHTML() must therefore be defined by the derived
    * class.
    *
    * @access public
    * @param  array  Options to set. Any options taken by
    *                the presentation class can be specified
    *                here.
    */
    function printMenu($options = array())
    {
        foreach ($options as $option => $value) {
            $this->$option = $value;
        }

        echo $this->toHTML();
    }
}

/**
* HTML_TreeMenu_DHTML class
*
* This class is a presentation class for the tree structure
* created using the TreeMenu/TreeNode. It presents the 
* traditional tree, static for browsers that can't handle
* the DHTML.
* @package HTML_TreeMenu
*/
class HTML_TreeMenu_DHTML extends HTML_TreeMenu_Presentation
{
    /**
    * Dynamic status of the treemenu. If true (default) this has no effect. If
    * false it will override all dynamic status vars and set the menu to be
    * fully expanded an non-dynamic.
    */
    var $isDynamic;

    /**
    * Path to the images
    * @var string
    */
    var $images;
    
    /**
    * Target for the links generated
    * @var string
    */
    var $linkTarget;
    
    /**
    * Whether to use clientside persistence or not
    * @var bool
    */
    var $userPersistence;
    
    /**
    * The default CSS class for the nodes
    */
    var $defaultClass;
    
    /**
    * Whether to skip first level branch images
    * @var bool
    */
    var $noTopLevelImages;

    /**
    * Constructor, takes the tree structure as
    * an argument and an array of options which
    * can consist of:
    *  o images            -  The path to the images folder. Defaults to "images"
    *  o linkTarget        -  The target for the link. Defaults to "_self"
    *  o defaultClass      -  The default CSS class to apply to a node. Default is none.
    *  o usePersistence    -  Whether to use clientside persistence. This persistence
    *                         is achieved using cookies. Default is true.
    *  o noTopLevelImages  -  Whether to skip displaying the first level of images if
    *                         there is multiple top level branches.
    *
    * And also a boolean for whether the entire tree is dynamic or not.
    * This overrides any perNode dynamic settings.
    *
    * @param object $structure The menu structure
    * @param array  $options   Array of options
    * @param bool   $isDynamic Whether the tree is dynamic or not
    */
    function HTML_TreeMenu_DHTML(&$structure, $options = array(), $isDynamic = true)
    {
        $this->menu      = &$structure;
        $this->isDynamic = $isDynamic;

        // Defaults
        $this->images           = 'images';
        $this->linkTarget       = '_self';
        $this->defaultClass     = '';
        $this->usePersistence   = true;
        $this->noTopLevelImages = false;

        foreach ($options as $option => $value) {
            $this->$option = $value;
        }
    }

    /**
    * Returns the HTML for the menu. This method can be
    * used instead of printMenu() to use the menu system
    * with a template system.
    *
    * @access public
    * @return string The HTML for the menu
    */    
    function toHTML()
    {
        static $count = 0;
        $menuObj = 'objTreeMenu_' . ++$count;

        $html  = "\n";
        $html .= '<script language="javascript" type="text/javascript">' . "\n\t";
        $html .= sprintf('%s = new TreeMenu("%s", "%s", "%s", "%s", %s, %s);',
                         $menuObj,
                         $this->images,
                         $menuObj,
                         $this->linkTarget,
                         $this->defaultClass,
                         $this->usePersistence ? 'true' : 'false',
                         $this->noTopLevelImages ? 'true' : 'false');
 
        $html .= "\n";

        /**
        * Loop through subnodes
        */
        if (isset($this->menu->items)) {
            for ($i=0; $i<count($this->menu->items); $i++) {
                $html .= $this->_nodeToHTML($this->menu->items[$i], $menuObj);
            }
        }

         $html .= sprintf("\n\t%s.drawMenu();", $menuObj);
        if ($this->usePersistence && $this->isDynamic) {
            $html .= sprintf("\n\t%s.resetBranches();", $menuObj);
        }
        $html .= "\n</script>";

        return $html;
    }
    
    /**
    * Prints a node of the menu
    *
    * @access private
    */
    function _nodeToHTML($nodeObj, $prefix, $return = 'newNode')
    {
        $expanded  = $this->isDynamic ? ($nodeObj->expanded  ? 'true' : 'false') : 'true';
        $isDynamic = $this->isDynamic ? ($nodeObj->isDynamic ? 'true' : 'false') : 'false';
        $html = sprintf("\t %s = %s.addItem(new TreeNode('%s', %s, %s, %s, %s, '%s'));\n",
                        $return,
                        $prefix,
                        $nodeObj->text,
                        !empty($nodeObj->icon) ? "'" . $nodeObj->icon . "'" : 'null',
                        !empty($nodeObj->link) ? "'" . addslashes($nodeObj->link) . "'" : 'null',
                        $expanded,
                        $isDynamic,
                        $nodeObj->cssClass);

        foreach ($nodeObj->events as $event => $handler) {
            $html .= sprintf("\t %s.setEvent('%s', '%s');\n",
                             $return,
                             $event,
                             str_replace(array("\r", "\n", "'"), array('\r', '\n', "\'"), $handler));
        }

        /**
        * Loop through subnodes
        */
        if (!empty($nodeObj->items)) {
            for ($i=0; $i<count($nodeObj->items); $i++) {
                $html .= $this->_nodeToHTML($nodeObj->items[$i], $return, $return . '_' . ($i + 1));
            }
        }

        return $html;
    }
}


/**
* HTML_TreeMenu_Listbox class
* 
* This class presents the menu as a listbox
* @package HTML_TreeMenu
*/
class HTML_TreeMenu_Listbox extends HTML_TreeMenu_Presentation
{
    /**
    * The text that is displayed in the first option
    * @var string
    */
    var $promoText;
    
    /**
    * The character used for indentation
    * @var string
    */
    var $indentChar;
    
    /**
    * How many of the indent chars to use
    * per indentation level
    * @var integer
    */
    var $indentNum;
    
    /**
    * Target for the links generated
    * @var string
    */
    var $linkTarget;

    /**
    * Constructor
    *
    * @param object $structure The menu structure
    * @param array  $options   Options whic affect the display of the listbox.
    *                          These can consist of:
    *                           o promoText  The text that appears at the the top of the listbox
    *                                        Defaults to "Select..."
    *                           o indentChar The character to use for indenting the nodes
    *                                        Defaults to "&nbsp;"
    *                           o indentNum  How many of the indentChars to use per indentation level
    *                                        Defaults to 2
    *                           o linkTarget Target for the links. Defaults to "_self"
    *                           o submitText Text for the submit button. Defaults to "Go"
    */
    function HTML_TreeMenu_Listbox($structure, $options = array())
    {
        $this->menu       = $structure;
        $this->promoText  = 'Select...';
        $this->indentChar = '&nbsp;';
        $this->indentNum  = 2;
        $this->linkTarget = '_self';
        $this->submitText = 'Go';
        
        foreach ($options as $option => $value) {
            $this->$option = $value;
        }
    }

    /**
    * Returns the HTML generated
    */
    function toHTML()
    {
        static $count = 0;
        $nodeHTML = '';

        /**
        * Loop through subnodes
        */
        if (isset($this->menu->items)) {
            for ($i=0; $i<count($this->menu->items); $i++) {
                $nodeHTML .= $this->_nodeToHTML($this->menu->items[$i]);
            }
        }

        return sprintf('<form target="%s" action="" onsubmit="var link = this.%s.options[this.%s.selectedIndex].value; if (link) {this.action = link; return true} else return false"><select name="%s"><option value="">%s</option>%s</select> <input type="submit" value="%s" /></form>',
                       $this->linkTarget,
                       'HTML_TreeMenu_Listbox_' . ++$count,
                       'HTML_TreeMenu_Listbox_' . $count,
                       'HTML_TreeMenu_Listbox_' . $count,
                       $this->promoText,
                       $nodeHTML,
                       $this->submitText);
    }
    
    /**
    * Returns HTML for a single node
    * 
    * @access private
    */
    function _nodeToHTML($node, $prefix = '')
    {
        $html = sprintf('<option value="%s">%s%s</option>', $node->link, $prefix, $node->text);
        
        /**
        * Loop through subnodes
        */
        if (isset($node->items)) {
            for ($i=0; $i<count($node->items); $i++) {
                $html .= $this->_nodeToHTML($node->items[$i], $prefix . str_repeat($this->indentChar, $this->indentNum));
            }
        }
        
        return $html;
    }
}
?>
