<?php

include_once 'ofc_bar_base.php';

class candle_value
{
	/**
	 *
	 */
	function candle_value( $high, $open, $close, $low )
	{
		$this->high = $high;
		$this->top = $open;
		$this->bottom = $close;
		$this->low = $low;
	}
	
	function set_colour( $colour )
	{
		$this->colour = $colour;
	}
	
	function set_tooltip( $tip )
	{
		$this->tip = $tip;
	}
}

class candle extends bar_base
{
	function candle($colour)
	{
		$this->type      = "candle";
		parent::bar_base();
		
		$this->set_colour( $colour );
	}
}

