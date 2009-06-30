<?php

/* ***** BEGIN LICENSE BLOCK *****
 *  
 * This file is part of FirePHP (http://www.firephp.org/).
 * 
 * Software License Agreement (New BSD License)
 * 
 * Copyright (c) 2006-2009, Christoph Dorn
 * All rights reserved.
 * 
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 * 
 *     * Redistributions of source code must retain the above copyright notice,
 *       this list of conditions and the following disclaimer.
 * 
 *     * Redistributions in binary form must reproduce the above copyright notice,
 *       this list of conditions and the following disclaimer in the documentation
 *       and/or other materials provided with the distribution.
 * 
 *     * Neither the name of Christoph Dorn nor the names of its
 *       contributors may be used to endorse or promote products derived from this
 *       software without specific prior written permission.
 * 
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 * 
 * ***** END LICENSE BLOCK *****
 * 
 * @copyright   Copyright (C) 2007-2009 Christoph Dorn
 * @author      Christoph Dorn <christoph@christophdorn.com>
 * @author      Michael Day <manveru.alma@gmail.com>
 * @license     http://www.opensource.org/licenses/bsd-license.php
 * @package     FirePHP
 */

require_once dirname(__FILE__).'/FirePHP.class.php4';

/**
 * Sends the given data to the FirePHP Firefox Extension.
 * The data can be displayed in the Firebug Console or in the
 * "Server" request tab.
 * 
 * @see http://www.firephp.org/Wiki/Reference/Fb
 * @param mixed $Object
 * @return true
 * @throws Exception
 */
function fb()
{
  $instance =& FirePHP::getInstance(true);

  $args = func_get_args();
  return call_user_func_array(array(&$instance,'fb'),$args);
}


class FB
{
  /**
   * Enable and disable logging to Firebug
   * 
   * @see FirePHP->setEnabled()
   * @param boolean $Enabled TRUE to enable, FALSE to disable
   * @return void
   */
  function setEnabled($Enabled) {
    $instance =& FirePHP::getInstance(true);
    $instance->setEnabled($Enabled);
  }
  
  /**
   * Check if logging is enabled
   * 
   * @see FirePHP->getEnabled()
   * @return boolean TRUE if enabled
   */
  function getEnabled() {
    $instance =& FirePHP::getInstance(true);
    return $instance->getEnabled();
  }  
  
  /**
   * Specify a filter to be used when encoding an object
   * 
   * Filters are used to exclude object members.
   * 
   * @see FirePHP->setObjectFilter()
   * @param string $Class The class name of the object
   * @param array $Filter An array or members to exclude
   * @return void
   */
  function setObjectFilter($Class, $Filter) {
    $instance =& FirePHP::getInstance(true);
    $instance->setObjectFilter($Class, $Filter);
  }
  
  /**
   * Set some options for the library
   * 
   * @see FirePHP->setOptions()
   * @param array $Options The options to be set
   * @return void
   */
  function setOptions($Options) {
    $instance =& FirePHP::getInstance(true);
    $instance->setOptions($Options);
  }

  /**
   * Get options for the library
   * 
   * @see FirePHP->getOptions()
   * @return array The options
   */
  function getOptions() {
    $instance =& FirePHP::getInstance(true);
    return $instance->getOptions();
  }

  /**
   * Log object to firebug
   * 
   * @see http://www.firephp.org/Wiki/Reference/Fb
   * @param mixed $Object
   * @return true
   */
  function send()
  {
    $instance =& FirePHP::getInstance(true);
    $args = func_get_args();
    return call_user_func_array(array(&$instance,'fb'),$args);
  }

  /**
   * Start a group for following messages
   * 
   * Options:
   *   Collapsed: [true|false]
   *   Color:     [#RRGGBB|ColorName]
   *
   * @param string $Name
   * @param array $Options OPTIONAL Instructions on how to log the group
   * @return true
   */
  function group($Name, $Options=null) {
    $instance =& FirePHP::getInstance(true);
    return $instance->group($Name, $Options);
  }

  /**
   * Ends a group you have started before
   *
   * @return true
   */
  function groupEnd() {
    return FB::send(null, null, FirePHP_GROUP_END);
  }

  /**
   * Log object with label to firebug console
   *
   * @see FirePHP::LOG
   * @param mixes $Object
   * @param string $Label
   * @return true
   */
  function log($Object, $Label=null) {
    return FB::send($Object, $Label, FirePHP_LOG);
  } 

  /**
   * Log object with label to firebug console
   *
   * @see FirePHP::INFO
   * @param mixes $Object
   * @param string $Label
   * @return true
   */
  function info($Object, $Label=null) {
    return FB::send($Object, $Label, FirePHP_INFO);
  } 

  /**
   * Log object with label to firebug console
   *
   * @see FirePHP::WARN
   * @param mixes $Object
   * @param string $Label
   * @return true
   */
  function warn($Object, $Label=null) {
    return FB::send($Object, $Label, FirePHP_WARN);
  } 

  /**
   * Log object with label to firebug console
   *
   * @see FirePHP::ERROR
   * @param mixes $Object
   * @param string $Label
   * @return true
   */
  function error($Object, $Label=null) {
    return FB::send($Object, $Label, FirePHP_ERROR);
  } 

  /**
   * Dumps key and variable to firebug server panel
   *
   * @see FirePHP::DUMP
   * @param string $Key
   * @param mixed $Variable
   * @return true
   */
  function dump($Key, $Variable) {
    return FB::send($Variable, $Key, FirePHP_DUMP);
  } 

  /**
   * Log a trace in the firebug console
   *
   * @see FirePHP::TRACE
   * @param string $Label
   * @return true
   */
  function trace($Label) {
    return FB::send($Label, FirePHP_TRACE);
  } 

  /**
   * Log a table in the firebug console
   *
   * @see FirePHP::TABLE
   * @param string $Label
   * @param string $Table
   * @return true
   */
  function table($Label, $Table) {
    return FB::send($Table, $Label, FirePHP_TABLE);
  } 
}
