<?php
/**
 * Global functions
 *
 * @package   tests
 * @author    Mark van Renswoude
 * @since     26-03-2002 15:56
 * @version   1.0
*/

/**
 * Set a variable, used by the template engine but available to all scripts
 *
 * globalSetVar sets an internal variable. This variable may later be retrieved using globalGetVar,
 * and is automagically available to templates using the getvar-tag.
 *
 * @param string $name    the name of the variable to set
 * @param string $value   new value
 * @return string         an empty string to simplify the replacement of setvar-tags in the templates
 * @see                   globalGetVar()
*/
function globalSetVar($name, $value)
{
  global $variables;

  $variables[$name] = $value;
  return '';
}

/**
 * Get a variable's value
 *
 * globalGetVar returns the value of an internal variable. This variable must be previously
 * assigned using either globalSetVar, or an indirect setvar-tag in a loaded template.
 * 
 * @param string $name    the name of the variable to return
 * @return string         the variable's value
 * @see                   globalSetVar()
*/
function globalGetVar($name)
{
  global $variables;

  if (isset($variables[$name]))
  {
    return $variables[$name];
  }
  else
  {
    return '';
  }
}
?>