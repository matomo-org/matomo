<?php
/**
 * All abstract representations of html tags in DocBlocks are handled by the
 * classes in this file
 *
 * Before version 1.2, phpDocumentor simply passed html to converters, without
 * much thought, except the {@link adv_htmlentities()} function was provided
 * along with a list of allowed html.  That list is no longer used, in favor
 * of these classes.
 *
 * The PDF Converter output looked wretched in version 1.1.0 because line breaks
 * in DocBlocks were honored.  This meant that output often had just a few words
 * on every other line!  To fix this problem, DocBlock descriptions are now
 * parsed using the {@link ParserDescParser}, and split into paragraphs.  In
 * addition, html in DocBlocks are parsed into these objects to allow for easy
 * conversion in destination converters.  This design also allows different
 * conversion for different templates within a converter, which separates
 * design from logic almost 100%
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
 * @subpackage DescHTML
 * @author     Greg Beaver <cellog@php.net>
 * @copyright  2002-2006 Gregory Beaver
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    CVS: $Id: DescHTML.inc,v 1.3 2006/04/30 22:18:13 cellog Exp $
 * @filesource
 * @link       http://www.phpdoc.org
 * @link       http://pear.php.net/PhpDocumentor
 * @see        parserDocBlock, parserInclude, parserPage, parserClass
 * @see        parserDefine, parserFunction, parserMethod, parserVar
 * @since      1.2
 */
/**
 * Used for <<code>> in a description
 * @package phpDocumentor
 * @subpackage DescHTML
 */
class parserCode extends parserStringWithInlineTags
{
    /**
     * @param Converter
     * @uses Converter::ProgramExample()
     */
    function Convert(&$c)
    {
        if (!isset($this->value[0])) return '';
        if (is_string($this->value[0]) && $this->value[0]{0} == "\n")
        {
            $this->value[0] = substr($this->value[0],1);
        }
        $linktags = array();
        foreach($this->value as $val) {
            if (phpDocumentor_get_class($val) == 'parserlinkinlinetag' ||
                  phpDocumentor_get_class($val) == 'parsertutorialinlinetag') {
                $linktags[] = array($c->postProcess($val->Convert($c, false, false)), $val);
            }
        }
        $a = $c->ProgramExample(rtrim(ltrim(parent::Convert($c, false, false), "\n\r")));
        foreach ($linktags as $tag) {
            $a = str_replace($tag[0], $tag[1]->Convert($c, false, false), $a);
        }
        return $a;
//        else return $c->PreserveWhiteSpace($this->getString(false));
    }
}

/**
 * Used for <<pre>> in a description
 * @package phpDocumentor
 * @subpackage DescHTML
 */
class parserPre extends parserStringWithInlineTags
{
    /**
     * @param Converter
     * @uses Converter::PreserveWhiteSpace()
     */
    function Convert(&$c)
    {
        return $c->PreserveWhiteSpace(rtrim(ltrim(parent::Convert($c, false, false), "\n\r")));
    }
}

/**
 * Used for <<b>> in a description
 * @package phpDocumentor
 * @subpackage DescHTML
 */
class parserB extends parserStringWithInlineTags
{
    /**
     * @param Converter
     * @uses Converter::Bolden()
     */
    function Convert(&$c)
    {
        return $c->Bolden(parent::Convert($c));
    }
}

/**
 * Used for <<i>> in a description
 * @package phpDocumentor
 * @subpackage DescHTML
 */
class parserI extends parserStringWithInlineTags
{
    /**
     * @param Converter
     * @uses Converter::Italicize()
     */
    function Convert(&$c)
    {
        return $c->Italicize(parent::Convert($c));
    }
}

/**
 * Used for <<var>> in a description
 * @package phpDocumentor
 * @subpackage DescHTML
 */
class parserDescVar extends parserStringWithInlineTags
{
    /**
     * @param Converter
     * @uses Converter::Varize()
     */
    function Convert(&$c)
    {
        return $c->Varize(parent::Convert($c));
    }
}

/**
 * Used for <<samp>> in a description
 * @package phpDocumentor
 * @subpackage DescHTML
 */
class parserSamp extends parserStringWithInlineTags
{
    /**
     * @param Converter
     * @uses Converter::Sampize()
     */
    function Convert(&$c)
    {
        return $c->Sampize(parent::Convert($c));
    }
}

/**
 * Used for <<kbd>> in a description
 * @package phpDocumentor
 * @subpackage DescHTML
 */
class parserKbd extends parserStringWithInlineTags
{
    /**
     * @param Converter
     * @uses Converter::Kbdize()
     */
    function Convert(&$c)
    {
        return $c->Kbdize(parent::Convert($c));
    }
}

/**
 * Used for <<br>> in a description
 * @package phpDocumentor
 * @subpackage DescHTML
 */
class parserBr extends parserStringWithInlineTags
{
    /**
     * @param Converter
     * @uses Converter::Br()
     */
    function Convert(&$c)
    {
        return $c->Br($this->getString());
    }
}

/**
 * Used for lists <<ol>> and <<ul>>
 * @package phpDocumentor
 * @subpackage DescHTML
 */
class parserList extends parserStringWithInlineTags
{
    /** @var boolean */
    var $numbered;
    /** @var integer */
    var $items = 0;
    /**
     * @param integer
     */
    function parserList($numbered)
    {
        $this->numbered = $numbered;
    }
    
    /** @param parserStringWithInlineTags */
    function addItem($item)
    {
        $this->value[$this->items++] = $item;
    }
    
    /** @param parserList */
    function addList($list)
    {
        $this->value[$this->items++] = $list;
    }
    
    /**
     * @uses Converter::ListItem() enclose each item of the list
     * @uses Converter::EncloseList() enclose the list
     * @param Converter
     */
    function Convert(&$c)
    {
        $list = '';
        foreach($this->value as $item)
        {
            $list .= $c->ListItem(trim($item->Convert($c)));
        }
        return $c->EncloseList($list,$this->numbered);
    }
}

?>
