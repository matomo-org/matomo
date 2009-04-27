<?php

class line_base
{
	function line_base()
	{
		$this->type      = "line";
		$this->text      = "Page views";
		$tmp = 'font-size';
		$this->$tmp = 10;
		
		$this->values    = array();
	}
	
	function set_values( $v )
	{
		$this->values = $v;		
	}
	
	/**
     * Append a value to the line.
     *
     * @param mixed $v
     */
    function append_value($v)
    {
        $this->values[] = $v;       
    }
	
	function set_width( $width )
	{
		$this->width = $width;		
	}
	
	function set_colour( $colour )
	{
		$this->colour = $colour;
	}
	
	function set_dot_size( $size )
	{
		$tmp = 'dot-size';
		$this->$tmp = $size;		
	}
	
	function set_halo_size( $size )
	{
		$tmp = 'halo-size';
		$this->$tmp = $size;		
	}
	
	function set_key( $text, $font_size )
	{
		$this->text      = $text;
		$tmp = 'font-size';
		$this->$tmp = $font_size;
	}
	
	function set_tooltip( $tip )
	{
		$this->tip = $tip;
	}
	
	function set_on_click( $text )
	{
		$tmp = 'on-click';
		$this->$tmp = $text;
	}
	
	function loop()
	{
		$this->loop = true;
	}
	
	function line_style( $s )
	{
		$tmp = "line-style";
		$this->$tmp = $s;
	}
	
	    /**
     * Sets the text for the line.
     *
     * @param string $text
     */   
    function set_text($text)
    {
        $this->text = $text;
    }
	
	
}