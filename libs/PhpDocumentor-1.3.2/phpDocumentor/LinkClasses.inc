<?php
/**
 * Linking to element documentation is performed by the classes in this file.
 *
 * abstractLink descendants contain enough information to differentiate every
 * documentable element, and so can be converted to a link string by
 * {@link Converter::returnSee()}
 * 
 * phpDocumentor :: automatic documentation generator
 * 
 * PHP versions 4 and 5
 *
 * Copyright (c) 2002-2006 Gregory Beaver
 * 
 * LICENSE:
 * 
 * This library is free software; you can redistribute it
 * and/or modify it under the terms of the GNU Lesser General
 * Public License as published by the Free Software Foundation;
 * either version 2.1 of the License, or (at your option) any
 * later version.
 * 
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU
 * Lesser General Public License for more details.
 * 
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package    phpDocumentor
 * @subpackage Links
 * @author     Gregory Beaver <cellog@php.net>
 * @copyright  2002-2006 Gregory Beaver
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    CVS: $Id$
 * @filesource
 * @link       http://www.phpdoc.org
 * @link       http://pear.php.net/PhpDocumentor
 * @since      1.2.0
 */

/**
 * linking classes parent
 * @package phpDocumentor
 * @subpackage Links
 */
class abstractLink
{
    /**#@+ @var string */
    var $path;
    /**
     * phpdoc alias _phpdoc_inc for phpdoc.inc
     */
    var $fileAlias = '';
    /**
     * element type linked to.
     * can only be a documentable element
     */
    var $type = '';
    var $name = '';
    var $category = '';
    var $package = '';
    var $subpackage = '';
    /**#@-*/

    /**
     * @param string full path to file containing element
     * @param string page name, as configured by {@link Parser::parse}
     * @param string element name
     * @param string package element is in
     * @param string subpackage element is in
     * @param string optional category that documentation is in
     */
    function addLink($path, $fileAlias, $name, $package, $subpackage, $category = false)
    {
        $this->path = $path;
        $this->fileAlias = $fileAlias;
        $this->name = $name;
        $this->category = $category;
        $this->package = $package;
        $this->subpackage = $subpackage;
    }
}

/**
 * procedural page link
 * @package phpDocumentor
 * @subpackage Links
 */
class pageLink extends abstractLink
{
    /** @var string */
    var $type = 'page';
}

/**
 * function link
 * @package phpDocumentor
 * @subpackage Links
 */
class functionLink extends abstractLink
{
    /** @var string */
    var $type = 'function';
}

/**
 * define link
 * @package phpDocumentor
 * @subpackage Links
 */
class defineLink extends abstractLink
{
    /** @var string */
    var $type = 'define';
}

/**
 * global variable link
 * @package phpDocumentor
 * @subpackage Links
 */
class globalLink extends abstractLink
{
    /** @var string */
    var $type = 'global';
}

/**
 * class link
 * @package phpDocumentor
 * @subpackage Links
 */
class classLink extends abstractLink
{
    /** @var string */
    var $type = 'class';
}

/**
 * method link
 * @package phpDocumentor
 * @subpackage Links
 */
class methodLink extends abstractLink
{
    /** @var string */
    var $type = 'method';
    /** @var string */
    var $class = '';
    
    /**
     * @param string class name
     * @param string full path to file containing element
     * @param string page name, as configured by {@link Parser::parse}
     * @param string element name
     * @param string package element is in
     * @param string subpackage element is in
     */
    function addLink($class, $path ,$fileAlias,$name,$package,$subpackage, $category = false)
    {
        $this->class = $class;
        abstractLink::addLink($path, $fileAlias,$name,$package,$subpackage, $category);
    }
}

/**
 * class variable link
 * @package phpDocumentor
 * @subpackage Links
 */
class varLink extends methodLink
{
    /** @var string */
    var $type = 'var';
}

/**
 * class constant link
 * @package phpDocumentor
 * @subpackage Links
 */
class constLink extends methodLink
{
    /** @var string */
    var $type = 'const';
}

/**
 * tutorial link
 * @package phpDocumentor
 * @subpackage Links
 */
class tutorialLink extends abstractLink
{
    /**#@+ @var string */
    var $type = 'tutorial';
    var $section = '';
    var $title = false;
    /**#@-*/
    
    /**
     * @param string section/subsection name
     * @param string full path to file containing element
     * @param string page name, as configured by {@link Parser::parse}
     * @param string element name
     * @param string package element is in
     * @param string subpackage element is in
     * @param string title of tutorial
     */
    function addLink($section,$path,$name,$package,$subpackage,$title = false, $category = false)
    {
        $this->section = $section;
        $this->title = $title;
        parent::addLink($path,'',$name,$package,$subpackage, $category);
    }
}
?>