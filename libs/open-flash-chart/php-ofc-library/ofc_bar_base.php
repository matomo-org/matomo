<?php

/* this is a base class */

class bar_base
{
	function bar_base(){}

	/**
	 * @param $text as string the key text
	 * @param $size as integer, size in pixels
	 */
	function set_key( $text, $size )
	{
		$this->text = $text;
		$tmp = 'font-size';
		$this->$tmp = $size;
	}
	
	/**
	 * syntatical sugar.
	 */
	function key( $text, $size )
	{
		$this->set_key( $text, $size );
	}

	/**
	 * @param $v as an array, a mix of:
	 * 	- a bar_value class. You can use this to customise the paramters of each bar.
	 * 	- integer. This is the Y position of the top of the bar.
	 */
	function set_values( $v )
	{
		$this->values = $v;		
	}
	
	/**
	 * see set_values
	 */
	function append_value( $v )
	{
		$this->values[] = $v;		
	}
	
	/**
	 * @param $colour as string, a HEX colour, e.g. '#ff0000' red
	 */
	function set_colour( $colour )
	{
		$this->colour = $colour;	
	}
	
	/**
	 *syntatical sugar
	 */
	function colour( $colour )
	{
		$this->set_colour( $colour );
	}

	/**
	 * @param $alpha as real number (range 0 to 1), e.g. 0.5 is half transparent
	 */
	function set_alpha( $alpha )
	{
		$this->alpha = $alpha;	
	}
	
	/**
	 * @param $tip as string, the tip to show. May contain various magic variables.
	 */
	function set_tooltip( $tip )
	{
		$this->tip = $tip;	
	}
	
	/**
	 *@param $on_show as line_on_show object
	 */
	function set_on_show($on_show)
	{
		$this->{'on-show'} = $on_show;
	}
	
	function set_on_click( $text )
	{
		$tmp = 'on-click';
		$this->$tmp = $text;
	}
	
	function attach_to_right_y_axis()
	{
		$this->axis = 'right';
	}
}

