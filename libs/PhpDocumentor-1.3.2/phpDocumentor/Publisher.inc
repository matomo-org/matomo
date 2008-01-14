<?php
/**
 * a class for handling the publishing of data
 * 
 * phpDocumentor :: automatic documentation generator
 * 
 * PHP versions 4 and 5
 *
 * Copyright (c) 2000-2006 Kellin, Joshua Eichorn
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
 * @author     Kellin <passionplay@hotmail.com>
 * @author     Joshua Eichorn <jeichorn@phpdoc.org>
 * @copyright  2000-2006 Kellin, Joshua Eichorn
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @version    CVS: $Id$
 * @link       http://www.phpdoc.org
 * @link       http://pear.php.net/PhpDocumentor
 * @since      0.1
 */
/**
 * a class for handling the publishing of data
 *
 * @author Kellin <passionplay@hotmail.com>
 * @author Joshua Eichorn <jeichorn@phpdoc.org>
 * @version	$Id$
 * @package 	phpDocumentor
 */
class Publisher
{
    /**#@+
     * @var array
     */
	/**
     * Array of references objects that have Subscribed to this publisher
     */
	var $subscriber	=	array();

	var $tokens	=	array();

	var $pushEvent	=	array();
	var $popEvent	=	array();
    /**#@-*/


	/**
     * Adds a subscriber to the {@link $subscriber} array().
     * if $event is '*', the publisher will use $object as the default event handler
     * @param integer $event see {@link Parser.inc} PARSER_EVENT_* constants
     * @param class $object any class that has a HandleEvent() method like {@link phpDocumentor_IntermediateParser::HandleEvent()} or {@link Classes::HandleEvent()}
     */
	function subscribe($event, &$object)
	{
		$this->subscriber[$event] =& $object;
	}

	/**
     * @param integer $event see {@link Parser.inc} PARSER_EVENT_* constants
     * @param mixed $data anything the subscribed event handler is expecting
     */
	function publishEvent($event,$data)
	{
		
		// see if there is a specific event handler
		if (!empty($this->subscriber[$event]))
		{
			$this->subscriber[$event]->HandleEvent($event,$data);
		} 
		 else if (isset($this->subscriber['*']) && is_object($this->subscriber['*'])) // check to see if a generic handler exists
		{
			$this->subscriber['*']->HandleEvent($event,$data);
		}
	}	
}
?>
