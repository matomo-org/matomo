<?php

class x_axis_labels
{
	function x_axis_labels(){}
	
	function set_steps( $steps )
	{
		$this->steps = $steps;
	}
	
	/**
	 *
	 * @param $labels as an array of [x_axis_label or string]
	 */
	function set_labels( $labels )
	{
		$this->labels = $labels;
	}
	
	function set_colour( $colour )
	{
		$this->colour = $colour;
	}
	
	/**
	 * font size in pixels
	 */
	function set_size( $size )
	{
		$this->size = $size;
	}
	
	/**
	 * rotate labels
	 */
	function set_vertical()
	{
		$this->rotate = 270;
	}
	
	function rotate( $angle )
	{
		$this->rotate = $angle;
	}
}