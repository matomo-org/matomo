<?php

class y_axis_labels
{
	function y_axis_labels(){}
	
	/**
	 * @param $steps which labels are generated
	 */
	function set_steps( $steps )
	{
		$this->steps = $steps;
	}
	
	/**
	 *
	 * @param $labels as an array of [y_axis_label or string]
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
	
	/**
	 * @param $text default text that all labels inherit
	 */
	function set_text( $text )
	{
		$this->text = $text;
	}
}