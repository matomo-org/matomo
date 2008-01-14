<?php
/**
 * Piwik - Open source web analytics
 * 
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html Gpl v3 or later
 * @version $Id$
 * 
 * @package Piwik_Visualization
 */

require_once "iView.php";

/**
 * Original class provided by Open Flash Chart
 *  
 * @package Piwik_Visualization
 */

abstract class Piwik_Visualization_OpenFlashChart implements iView
{	
    function __construct()
    {
		$this->data = array();
		$this->x_labels = array();
		$this->y_min = 0;
		$this->y_max = 20;
		$this->y_steps = 5;
		$this->title = '';
		$this->title_style = '';

		$this->x_tick_size = -1;

		$this->y2_max = '';
		$this->y2_min = '';
		
		// GRID styles:
		$this->x_axis_colour = '';
		$this->x_axis_3d = '';
		$this->x_grid_colour = '';
		$this->x_axis_steps = 1;
		$this->y_axis_colour = '';
		$this->y_grid_colour = '';
		$this->y2_axis_colour = '';
    
		// AXIS LABEL styles:         
		$this->x_label_style = '';
		$this->y_label_style = '';
		$this->y_label_style_right = '';
		

		// AXIS LEGEND styles:
		$this->x_legend = '';
		$this->x_legend_size = 20;
		$this->x_legend_colour = '#000000';

		$this->y_legend = '';
		$this->y_legend_right = '';
		//$this->y_legend_size = 20;
		//$this->y_legend_colour = '#000000';

		$this->lines = array();
		$this->line_default = '&line=3,#87421F&'. "\r\n";

		$this->bg_colour = '';
		$this->bg_image = '';

		$this->inner_bg_colour = '';
		$this->inner_bg_colour_2 = '';
		$this->inner_bg_angle = '';

		// PIE chart ------------
		$this->pie = '';
		$this->pie_values = '';
		$this->pie_colours = '';
		$this->pie_labels = '';

		$this->tool_tip = '';
		
		// which data lines are attached to the
		// right Y axis?
		$this->y2_lines = array();
    }

    function set_data( $a )
    {
    	if( count( $this->data ) == 0 )
        	$this->data[] = '&values='.implode(',',$a).'&'."\r\n";
        else
        	$this->data[] = '&values_'. (count( $this->data )+1) .'='.implode(',',$a).'&'."\r\n";
    }

    function set_tool_tip( $tip )
    {
        $this->tool_tip = $tip;
    }
    
    function set_x_labels( $a )
    {
        $this->x_labels = $a;
    }
    
    function set_x_label_style( $size, $colour='', $orientation=0, $step=-1, $grid_colour='' )
    {
        
        $this->x_label_style = '&x_label_style='. $size;
        
        if( strlen( $colour ) > 0 )
            $this->x_label_style .= ','. $colour;

        if( $orientation > -1 )
            $this->x_label_style .= ','. $orientation;

        if( $step > 0 )
            $this->x_label_style .= ','. $step;
        
        if( strlen( $grid_colour ) > 0 )
            $this->x_label_style .= ','. $grid_colour;
            
        $this->x_label_style .= "&\r\n";
    }

    function set_bg_colour( $colour )
    {
        $this->bg_colour = $colour;
    }

    function set_bg_image( $url, $x='center', $y='center' )
    {
        $this->bg_image = $url;
        $this->bg_image_x = $x;
        $this->bg_image_y = $y;
    }

	function attach_to_y_right_axis( $data_number )
	{
		$this->y2_lines[] = $data_number;
	}
    
    function set_inner_background( $col, $col2='', $angle=-1 )
    {

         $this->inner_bg_colour = $col;


         if( strlen($col2) > 0 )
             $this->inner_bg_colour_2 = $col2;

         if( $angle != -1 )
             $this->inner_bg_angle = $angle;

    }

    function _set_y_label_style( $name, $size, $colour )
    {
    	$tmp = '&'. $name .'='. $size;
    	
    	if( strlen( $colour ) > 0 )
                $tmp .= ','. $colour;
    	
    	return $tmp;
	}
	
    function set_y_label_style( $size, $colour='' )
    {
        $this->y_label_style = $this->_set_y_label_style( 'y_label_style', $size, $colour );
    }
    
    function set_y_right_label_style( $size, $colour='' )
    {
        $this->y_label_style_right = $this->_set_y_label_style( 'y2_label_style', $size, $colour );
    }
    
    function set_y_max( $max )
    {

        $this->y_max = intval( $max );
    }

    function set_y_min( $min )
    {

        $this->y_min = intval( $min );
    }
        
    function set_y_right_max( $max )
    {
    	$this->y2_max = '&y2_max='. $max .'&'."\r\n";
	}
    
    function set_y_right_min( $min )
    {
    	$this->y2_min = '&y2_min='. $min .'&'."\r\n";
	}
	
    function y_label_steps( $val )
    {
         $this->y_steps = intval( $val );
    }
    
    function title( $title, $style='' )
    {
        $this->title = $title;
        if( strlen( $style ) > 0 )
                $this->title_style = $style;
    }
    
    function set_x_legend( $text, $size=-1, $colour='' )
    {
         $this->x_legend = $text;
         if( $size > -1 )
                $this->x_legend_size = $size;
                
         if( strlen( $colour )>0 )
                $this->x_legend_colour = $colour;
    }
    
    function set_x_tick_size( $size )
    {
        if( $size > 0 )
                $this->x_tick_size = $size;
    }

    function set_x_axis_steps( $steps )
    {
        if ( $steps > 0 )
            $this->x_axis_steps = $steps;
    }
    
    function set_x_axis_3d( $size )
    {
    	if( $size > 0 )
    		$this->x_axis_3d = '&x_axis_3d='. $size ."&\r\n";
	}
	
	// PRIVATE METHOD
	function _set_y_legend( $label, $text, $size, $colour )
	{
		$tmp = '&'. $label .'=';
		$tmp .= $text;
    	
         if( $size > -1 )
         	$tmp .= ','. $size;

         if( strlen( $colour )>0 )
         	$tmp .= ','. $colour;
                
         $tmp .= "&\r\n";
         
         return $tmp;
	}

    function set_y_legend( $text, $size=-1, $colour='' )
    {
    	$this->y_legend = $this->_set_y_legend( 'y_legend', $text, $size, $colour );
    }
    
    function set_y_right_legend( $text, $size=-1, $colour='' )
    {
    	$this->y_legend_right = $this->_set_y_legend( 'y2_legend', $text, $size, $colour );
    }
    
    function line( $width, $colour='', $text='', $size=-1, $circles=-1 )
    {
    	$tmp = '&line';
    	
    	if( count( $this->lines ) > 0 )
        	$tmp .= '_'. (count( $this->lines )+1);
        	
    	$tmp .= '=';
    	
        if( $width > 0 )
        {
                $tmp .= $width;
                $tmp .= ','. $colour;
        }
                
        if( strlen( $text ) > 0 )
        {
                $tmp .= ','. $text;
                $tmp .= ','. $size;
        }
        
        if( $circles > 0 )
                $tmp .= ','. $circles;
        
        $tmp .= "&\r\n";;
        
        $this->lines[] = $tmp;
    }

    function line_dot( $width, $dot_size, $colour, $text='', $font_size='' )
    {
    	$tmp = '&line_dot';
    	
    	if( count( $this->lines ) > 0 )
        	$tmp .= '_'. (count( $this->lines )+1);
        	
    	$tmp .= "=$width,$colour,$text";

        if( strlen( $font_size ) > 0 )
            $tmp .= ",$font_size,$dot_size";
        
        $tmp .= "&\r\n";
        
        $this->lines[] = $tmp;
    }

    function line_hollow( $width, $dot_size, $colour, $text='', $font_size='' )
    {
    	$tmp = '&line_hollow';
    	
    	if( count( $this->lines ) > 0 )
        	$tmp .= '_'. (count( $this->lines )+1);
        	
    	$tmp .= "=$width,$colour,$text";

        if( strlen( $font_size ) > 0 )
            $tmp .= ",$font_size,$dot_size";
        
        $tmp .= "&\r\n";
        $this->lines[] = $tmp;
    }

    function area_hollow( $width, $dot_size, $colour, $alpha, $text='', $font_size='', $fill_colour='' )
    {
    	$tmp = '&area_hollow';
    	
    	if( count( $this->lines ) > 0 )
        	$tmp .= '_'. (count( $this->lines )+1);
        	
    	$tmp .= "=$width,$dot_size,$colour,$alpha";

        if( strlen( $text ) > 0 )
            $tmp .= ",$text,$font_size";
            
        if( strlen( $fill_colour ) > 0 )
        	$tmp .= ','. $fill_colour;

        $tmp .= "&\r\n";
        
        $this->lines[] = $tmp;
    }


    function bar( $alpha, $colour='', $text='', $size=-1 )
    {
    	$tmp = '&bar';
    	
    	if( count( $this->lines ) > 0 )
        	$tmp .= '_'. (count( $this->lines )+1);
        	
    	$tmp .= '=';
        $tmp .= $alpha .','. $colour .','. $text .','. $size;
        $tmp .= "&\r\n";;
        
        $this->lines[] = $tmp;
    }

    function bar_filled( $alpha, $colour, $colour_outline, $text='', $size=-1 )
    {
    	$tmp = '&filled_bar';
    	
    	if( count( $this->lines ) > 0 )
        	$tmp .= '_'. (count( $this->lines )+1);
        	
    	$tmp .= "=$alpha,$colour,$colour_outline,$text,$size&\r\n";
        
        $this->lines[] = $tmp;
    }
    
    function bar_3D( $alpha, $colour='', $text='', $size=-1 )
    {
    	$tmp = '&bar_3d';
    	
    	if( count( $this->lines ) > 0 )
        	$tmp .= '_'. (count( $this->lines )+1);
        	
    	$tmp .= '=';
        $tmp .= $alpha .','. $colour .','. $text .','. $size;
        $tmp .= "&\r\n";;
        
        $this->lines[] = $tmp;
    }
    
	function bar_glass( $alpha, $colour, $outline_colour, $text='', $size=-1 )
	{
		$tmp = '&bar_glass';

		if( count( $this->lines ) > 0 )
			$tmp .= '_'. (count( $this->lines )+1);

		$tmp .= '=';
		$tmp .= $alpha .','. $colour .','. $outline_colour .','. $text .','. $size;
		$tmp .= "&\r\n";;

		$this->lines[] = $tmp;
	}
	
	function bar_fade( $alpha, $colour='', $text='', $size=-1 )
	{
		$tmp = '&bar_fade';

		if( count( $this->lines ) > 0 )
			$tmp .= '_'. (count( $this->lines )+1);

		$tmp .= '=';
		$tmp .= $alpha .','. $colour .','. $text .','. $size;
		$tmp .= "&\r\n";;

		$this->lines[] = $tmp;
	}
    
    function x_axis_colour( $axis, $grid='' )
    {
		$this->x_axis_colour = $axis;
		$this->x_grid_colour = $grid;
	}
		
    function y_axis_colour( $axis, $grid='' )
    {
    	$this->y_axis_colour = '&y_axis_colour='. $axis .'&'."\r\n";
		
		if( strlen( $grid ) > 0 )
			$this->y_grid_colour = '&y_grid_colour='. $grid .'&'."\r\n";
    }
    
    function y_right_axis_colour( $colour )
    {
         $this->y2_axis_colour = '&y2_axis_colour='. $colour .'&'."\r\n";
    }
    

    function pie( $alpha, $line_colour, $label_colour )
    {
         $this->pie = $alpha.','.$line_colour.','.$label_colour;

    }

    function pie_values( $values, $labels )
    {
         $this->pie_values = implode(',',$values);
         $this->pie_labels = implode(',',$labels);
    }


    function pie_slice_colours( $colours )
    {
        $this->pie_colours = implode(',',$colours);
    }

    

    function render()
    {
        //$tmp = "&padding=70,5,50,40&\r\n";
        $tmp = '';
        
        if( strlen( $this->title ) > 0 )
        {
                $tmp .= '&title='. $this->title .',';
                $tmp .= $this->title_style .'&';
                $tmp .= "\r\n";
        }
        
        if( strlen( $this->x_legend ) > 0 )
        {
                $tmp .= '&x_legend='. $this->x_legend .',';
                $tmp .= $this->x_legend_size .',';
                $tmp .= $this->x_legend_colour ."&\r\n";
        }

        if( strlen( $this->x_label_style ) > 0 )
            $tmp .= $this->x_label_style;
            
        if( $this->x_tick_size > 0 )
                $tmp .= "&x_ticks=". $this->x_tick_size ."&\r\n";
                
        if( $this->x_axis_steps > 0 )
        	$tmp .= "&x_axis_steps=". $this->x_axis_steps ."&\r\n";

		if( strlen( $this->x_axis_3d ) > 0 )
			$tmp .= $this->x_axis_3d;
        
        $tmp .= $this->y_legend;	
        $tmp .= $this->y_legend_right;

        if( strlen( $this->y_label_style ) > 0 )
            $tmp .= $this->y_label_style;

        $tmp .= '&y_ticks=5,10,'. $this->y_steps .'&'."\r\n";
        
        if( count( $this->lines ) == 0 )
        {
			$tmp .= $this->line_default;	
        }
        else
        {
			foreach( $this->lines as $line )
				$tmp .= $line;	
        }

        foreach( $this->data as $data )
				$tmp .= $data;
		
		if( count( $this->y2_lines ) > 0 )
		{
			$tmp .= '&y2_lines=';
			$tmp .= implode( ',', $this->y2_lines );
			$tmp .= '&'."\r\n";
			//
			// Should this be an option? I think so...
			//
			$tmp .= '&show_y2=true&'."\r\n";
		}	
        
        if( count( $this->x_labels ) > 0 )
                $tmp .= '&x_labels='.implode(',',$this->x_labels).'&'."\r\n";
                
        $tmp .= '&y_min='. $this->y_min .'&'."\r\n";
        $tmp .= '&y_max='. $this->y_max .'&'."\r\n";
        
        $tmp .= $this->y2_max;
		$tmp .= $this->y2_min;
        
        if( strlen( $this->bg_colour ) > 0 )
        	$tmp .= '&bg_colour='. $this->bg_colour .'&'."\r\n";

        if( strlen( $this->bg_image ) > 0 )
        {
                $tmp .= '&bg_image='. $this->bg_image .'&'."\r\n";
                $tmp .= '&bg_image_x='. $this->bg_image_x .'&'."\r\n";
                $tmp .= '&bg_image_y='. $this->bg_image_y .'&'."\r\n";
        }


        if( strlen( $this->x_axis_colour ) > 0 )
        {
            $tmp .= '&x_axis_colour='. $this->x_axis_colour .'&'."\r\n";
            $tmp .= '&x_grid_colour='. $this->x_grid_colour .'&'."\r\n";
        }

        if( strlen( $this->y_axis_colour ) > 0 )
        	$tmp .= $this->y_axis_colour;
		
		if( strlen( $this->y_grid_colour ) > 0 )
			$tmp .= $this->y_grid_colour;
			
		if( strlen( $this->y2_axis_colour ) > 0 )    
			$tmp .= $this->y2_axis_colour;

        if( strlen( $this->inner_bg_colour ) > 0 )
        {
            $tmp .= '&inner_background='.$this->inner_bg_colour;
            if( strlen( $this->inner_bg_colour_2 ) > 0 )
            {
             
                $tmp .= ','. $this->inner_bg_colour_2;
                $tmp .= ','. $this->inner_bg_angle;
            }
            $tmp .= '&'."\r\n";
        }

        if( strlen( $this->pie ) > 0 )
        {

             $tmp .= '&pie='.        $this->pie .'&'."\r\n";
             $tmp .= '&values='.     $this->pie_values .'&'."\r\n";
             $tmp .= '&pie_labels='. $this->pie_labels .'&'."\r\n";
             $tmp .= '&colours='.    $this->pie_colours .'&'."\r\n";
        }

        if( strlen( $this->tool_tip ) > 0 )
        {
             $tmp .= '&tool_tip='. $this->tool_tip .'&'."\r\n";
        }

        return $tmp;
    }
}