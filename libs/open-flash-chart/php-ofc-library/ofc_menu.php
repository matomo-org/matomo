<?php

class ofc_menu_item
{
	/**
	 * @param $text as string. The menu item text.
	 * @param $javascript_function_name as string. The javascript function name, the
	 * js function takes one parameter, the chart ID. See ofc_menu_item_camera for
	 * some example code.
	 */
	function ofc_menu_item($text, $javascript_function_name)
	{
		$this->type = "text";
		$this->text = $text;
		$tmp = 'javascript-function';
		$this->$tmp = $javascript_function_name;
	}
}

class ofc_menu_item_camera
{
	/**
	 * @param $text as string. The menu item text.
	 * @param $javascript_function_name as string. The javascript function name, the
	 * js function takes one parameter, the chart ID. So for example, our js function
	 * could look like this:
	 *
	 * function save_image( chart_id )
	 * {
	 *     alert( chart_id );
	 * }
	 *
	 * to make a menu item call this: ofc_menu_item_camera('Save chart', 'save_image');
	 */
	function ofc_menu_item_camera($text, $javascript_function_name)
	{
		$this->type = "camera-icon";
		$this->text = $text;
		$tmp = 'javascript-function';
		$this->$tmp = $javascript_function_name;
	}
}

class ofc_menu
{
	function ofc_menu($colour, $outline_colour)
	{
		$this->colour = $colour;
		$this->outline_colour = $outline_colour;
	}
	
	function values($values)
	{
		$this->values = $values;
	}
}