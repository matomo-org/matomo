<?PHP
/**
 * XML/Beautifier.php
 *
 * Format XML files containing unknown entities (like all of peardoc)
 *
 * phpDocumentor :: automatic documentation generator
 * 
 * PHP versions 4 and 5
 *
 * Copyright (c) 2004-2006 Gregory Beaver
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
 * @subpackage Parsers
 * @author     Greg Beaver <cellog@php.net>
 * @copyright  2004-2006 Gregory Beaver
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    CVS: $Id: Beautifier.php,v 1.3 2006/04/30 22:18:14 cellog Exp $
 * @filesource
 * @link       http://www.phpdoc.org
 * @link       http://pear.php.net/PhpDocumentor
 * @since      1.3.0
 */


/**
 * This is just like XML_Beautifier, but uses {@link phpDocumentor_XML_Beautifier_Tokenizer}
 * @package phpDocumentor
 * @subpackage Parsers
 * @since 1.3.0
 */
class phpDocumentor_peardoc2_XML_Beautifier extends XML_Beautifier {

   /**
    * format a file or URL
    *
    * @access public
    * @param  string    $file       filename
    * @param  mixed     $newFile    filename for beautified XML file (if none is given, the XML string will be returned.)
    *                               if you want overwrite the original file, use XML_BEAUTIFIER_OVERWRITE
    * @param  string    $renderer   Renderer to use, default is the plain xml renderer
    * @return mixed                 XML string of no file should be written, true if file could be written
    * @throws PEAR_Error
    * @uses   _loadRenderer() to load the desired renderer
    */   
    function formatFile($file, $newFile = null, $renderer = "Plain")
    {
        if ($this->apiVersion() != '1.0') {
            return $this->raiseError('API version must be 1.0');
        }
        /**
         * Split the document into tokens
         * using the XML_Tokenizer
         */
        require_once dirname(__FILE__) . '/Tokenizer.php';
        $tokenizer = new phpDocumentor_XML_Beautifier_Tokenizer();
        
        $tokens = $tokenizer->tokenize( $file, true );

        if (PEAR::isError($tokens)) {
            return $tokens;
        }
        
        include_once dirname(__FILE__) . '/Plain.php';
        $renderer = new PHPDoc_XML_Beautifier_Renderer_Plain($this->_options);
        
        $xml = $renderer->serialize($tokens);
        
        if ($newFile == null) {
            return $xml;
        }
        
        $fp = @fopen($newFile, "w");
        if (!$fp) {
            return PEAR::raiseError("Could not write to output file", XML_BEAUTIFIER_ERROR_NO_OUTPUT_FILE);
        }
        
        flock($fp, LOCK_EX);
        fwrite($fp, $xml);
        flock($fp, LOCK_UN);
        fclose($fp);
        return true;    }

   /**
    * format an XML string
    *
    * @access public
    * @param  string    $string     XML
    * @return string    formatted XML string
    * @throws PEAR_Error
    */   
    function formatString($string, $renderer = "Plain")
    {
        if ($this->apiVersion() != '1.0') {
            return $this->raiseError('API version must be 1.0');
        }
        /**
         * Split the document into tokens
         * using the XML_Tokenizer
         */
        require_once dirname(__FILE__) . '/Tokenizer.php';
        $tokenizer = new phpDocumentor_XML_Beautifier_Tokenizer();
        
        $tokens = $tokenizer->tokenize( $string, false );

        if (PEAR::isError($tokens)) {
            return $tokens;
        }

        include_once dirname(__FILE__) . '/Plain.php';
        $renderer = new PHPDoc_XML_Beautifier_Renderer_Plain($this->_options);
        
        $xml = $renderer->serialize($tokens);
        
        return $xml;
    }
}
?>