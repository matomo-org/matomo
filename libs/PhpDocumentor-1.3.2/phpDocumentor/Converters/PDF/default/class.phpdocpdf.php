<?php
/**
 * Cezpdf callback class customized for phpDocumentor
 *
 * phpDocumentor :: automatic documentation generator
 * 
 * PHP versions 4 and 5
 *
 * Copyright (c) 2000-2006 Joshua Eichorn, Gregory Beaver
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
 * @package    Converters
 * @subpackage PDFdefault
 * @author     Greg Beaver <cellog@php.net>
 * @copyright  2000-2006 Joshua Eichorn, Gregory Beaver
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    CVS: $Id: class.phpdocpdf.php,v 1.4 2006/04/30 22:18:14 cellog Exp $
 * @filesource
 * @link       http://www.phpdoc.org
 * @link       http://pear.php.net/PhpDocumentor
 * @since      1.2
 */

/** ezPdf libraries */
include_once 'phpDocumentor/Converters/PDF/default/class.ezpdf.php';
include_once 'phpDocumentor/Converters/PDF/default/ParserPDF.inc';

// define a class extension to allow the use of a callback to get the table of
// contents, and to put the dots in the toc
/**
 * @package Converters
 * @subpackage PDFdefault
 */
class phpdocpdf extends Cezpdf
{
    var $reportContents = array();
    var $indexContents = array();
    var $indents = array();
    var $font_dir = false;
    var $set_pageNumbering = false;
    var $converter;
    var $_save = '';
    var $listType = 'ordered';
    var $_colorStack = array();

    function phpdocpdf(&$pdfconverter,$fontdir,$paper='a4',$orientation='portrait')
    {
        Cezpdf::Cezpdf($paper,$orientation);
        $this->converter = $pdfconverter;
        $this->font_dir = $fontdir;
    }
    
    /**
     * This really should be in the parent class
     */
    function getColor()
    {
        return $this->currentColour;
    }
    
    function setColorArray($color)
    {
        $this->setColor($color['r'], $color['g'], $color['b']);
    }
    
    /**
     * Extract Pdfphp-format color from html-format color
     * @return array
     * @access private
     */
    function _extractColor($htmlcolor)
    {
        preg_match('/#([a-fA-F0-9][a-fA-F0-9])([a-fA-F0-9][a-fA-F0-9])([a-fA-F0-9][a-fA-F0-9])/', $htmlcolor, $color);
        if (count($color) != 4)
        {
            return false;
        }
        $red = hexdec($color[1]) / hexdec('FF');
        $green = hexdec($color[2]) / hexdec('FF');
        $blue = hexdec($color[3]) / hexdec('FF');
        return array('r' => $red, 'g' => $green, 'b' => $blue);
    }
    
    function validHTMLColor($color)
    {
        return $this->_extractColor($htmlcolor);
    }
    
    function setHTMLColor($color)
    {
        fancy_debug('toplevel setting to', $color);
        $this->setColor($color['r'], $color['g'], $color['b']);
    }
    
    function textcolor($info)
    {
        if ($info['status'] == 'start')
        {
            array_push($this->_colorStack, $this->getColor());
            $color = $this->_extractColor($info['p']);
            if ($color)
            {
//                fancy_debug('set color to ',$info['p'],$color, $this->_colorStack);
                $this->setColorArray($color);
            } else
            {
                array_pop($this->_colorStack);
            }
        } elseif ($info['status'] == 'end')
        {
//            debug('unsetting');
            $this->setColorArray(array_pop($this->_colorStack));
        }
    }

    function rf($info)
    {
        $tmp = $info['p'];
        $lvl = $tmp[0];
        $lbl = rawurldecode(substr($tmp,1));
        $num=$this->ezWhatPageNumber($this->ezGetCurrentPageNumber());
        $this->reportContents[] = array($lbl,$num,$lvl );
        $this->addDestination('toc'.(count($this->reportContents)-1),'FitH',$info['y']+$info['height']);
    }
    
    function index($info)
    {
        $res = explode('|||',rawurldecode($info['p']));
        $name = $res[0];
        $descrip = $res[1];
        $letter = $name[0];
        if ($letter == '$') $letter = $name[1];
        $this->indexContents[strtoupper($letter)][] = array($name,$descrip,$this->ezWhatPageNumber($this->ezGetCurrentPageNumber()),count($this->reportContents) - 1);
    }
    
    function IndexLetter($info)
    {
        $letter = $info['p'];
        $this->transaction('start');
        $ok=0;
        while (!$ok){
          $thisPageNum = $this->ezPageCount;
          $this->saveState();
          $this->setColor(0.9,0.9,0.9);
          $this->filledRectangle($this->ez['leftMargin'],$this->y-$this->getFontHeight(18)+$this->getFontDecender(18),$this->ez['pageWidth']-$this->ez['leftMargin']-$this->ez['rightMargin'],$this->getFontHeight(18));
          $this->restoreState();
          $this->_ezText($letter,18,array('justification'=>'left'));
          if ($this->ezPageCount==$thisPageNum){
            $this->transaction('commit');
            $ok=1;
          } else {
            // then we have moved onto a new page, bad bad, as the background colour will be on the old one
            $this->transaction('rewind');
            $this->ezNewPage();
          }
        }
    }
    
    function dots($info)
    {
        // draw a dotted line over to the right and put on a page number
        $tmp = $info['p'];
        $lvl = $tmp[0];
        $lbl = substr($tmp,1);
        $xpos = 520;
        
        switch($lvl)
        {
            case '1':
                $size=16;
                $thick=1;
            break;
            case '2':
                $size=14;
                $thick=1;
            break;
            case '3':
                $size=12;
                $thick=1;
            break;
            case '4':
                $size=11;
                $thick=1;
            break;
        }
        
        $adjust = 0;
        if ($size != 16) $adjust = 1;
        $this->saveState();
        $this->setLineStyle($thick,'round','',array(0,10));
        $this->line($xpos - (5*$adjust),$info['y'],$info['x']+5,$info['y']);
        $this->restoreState();
        $this->addText($xpos - (5*$adjust)+5,$info['y'],$size,$lbl);
    }
    
    /**
     * @uses PDFParser extracts all meta-tags and processes text for output
     */
    function ezText($text,$size=0,$options=array(),$test=0)
    {
        $text = str_replace("\t","   ",$text);
        // paragraph breaks
        $text = str_replace("<##P##>","\n    ",$text);
        $text = str_replace("<<c:i",'< <c:i',$text);
        $text = str_replace("ilink>>","ilink> >",$text);
        $this->_save .= $text;
    }
    
    function setupTOC()
    {
        $parser = new PDFParser;
        $parser->parse($this->_save,$this->font_dir,$this);
        $this->_save = '';
    }
    
    function ezOutput($debug = false, $template)
    {
        if ($debug) return $this->_save;
        $this->setupTOC();
        if ($template)
        {
            uksort($this->indexContents,'strnatcasecmp');
            $xpos = 520;
            $z = 0;
            foreach($this->indexContents as $letter => $contents)
            {
                if ($z++/50 == 0) {phpDocumentor_out('.');flush();}
                uksort($this->indexContents[$letter],array($this->converter,'mystrnatcasecmp'));
            }
            $template->assign('indexcontents',$this->indexContents);
            $this->ezText($template->fetch('index.tpl'));
            $this->setupTOC();
        }
        return parent::ezOutput();
    }
    
    function _ezText($text,$size=0,$options=array(),$test=0)
    {
        return parent::ezText($text,$size,$options,$test);
    }
    
    function getYPlusOffset($offset)
    {
        return $this->y + $offset;
    }
    
    function addMessage($message)
    {
        return parent::addMessage($message);
        phpDocumentor_out($message."\n");
        flush();
    }
    
    function ezProcessText($text){
      // this function will intially be used to implement underlining support, but could be used for a range of other
      // purposes
      $text = parent::ezProcessText($text);
      $text = str_replace(array('<UL>','</UL>','<LI>','</LI>','<OL>','</OL>','</ol>','<blockquote>','</blockquote>'),
                          array('<ul>','</ul>','<li>','</li>','<ol>','</ul>','</ul>',"<C:indent:20>\n","<C:indent:-20>"),$text);
//      $text = str_replace("<ul>\n","<ul>",$text);
      $text = preg_replace("/\n+\s*(<ul>|<ol>)/", "\n\\1", $text);
      // some problemos fixed here - hack
      $text = preg_replace('/<text [^]]+>/', '', $text);
      $text = str_replace("<li>\n","<li>",$text);
      $text = preg_replace("/\n+\s*<li>/", "<li>", $text);
      $text = str_replace("<mybr>","\n",$text);
      $text = str_replace('</li></ul>','</ul>',$text);
      $text = preg_replace("/^\n(\d+\s+.*)/", '\\1', $text);
      $search = array('<ul>','</ul>','<ol>','<li>','</li>');
      $replace = array("<C:indent:20>\n","\n<C:indent:-20>","\n<C:indent:20:ordered>\n",'<C:bullet>',"\n");
      $text = str_replace($search,$replace,$text);
      $text = preg_replace("/([^\n])<C:bullet/", "\\1\n<C:bullet", $text);
      if (false) {
        $fp = @fopen("C:/Documents and Settings/Owner/Desktop/pdfsourceorig.txt",'a');
        if ($fp)
        {
            fwrite($fp, $text);
            fclose($fp);
        }
      }
      return $text;
    }
    
    function indent($info)
    {
        $stuff = explode(':', $info['p']);
        $margin = $stuff[0];
        if (count($stuff) - 1)
        {
            $this->listType = 'ordered';
            $this->listIndex = 1;
        } else
        {
            if ($margin > 0)
            {
                $this->listIndex = 1;
            }
            $this->listType = 'unordered';
        }
        $this->ez['leftMargin'] += $margin;
    }
    
    /**
     * @author Murray Shields
     */
    function bullet($Data) 
    {
        if ($this->listType == 'ordered')
        {
            return $this->orderedBullet($Data);
        }
        $D = abs($Data["decender"]); 
        $X = $Data["x"] - ($D * 2) - 10; 
        $Y = $Data["y"] + ($D * 1.5); 
        $this->setLineStyle($D, "butt", "miter", array()); 
        $this->setColor(0,0,0); 
        $this->ellipse($X, $Y, 1); 
    }
    
    function orderedBullet($info)
    {
        $this->addText($info['x']-20, $info['y']-1, 10, $this->listIndex++ . '.');
    }
    
    function ezNewPage($debug=false)
    {
        parent::ezNewPage();
        if (!$this->set_pageNumbering)
        {
            $template = $this->converter->newSmarty();
            $parser = new PDFParser;
            $parser->parse($template->fetch('pagenumbering.tpl'),$this->font_dir,$this);
        }
        $this->set_pageNumbering = true;
    }
}
?>
