class Values
{
	public var styles:Array;

	public function Values( lv:LoadVars, bgColour:Number, labels:Array )
	{
		this.styles = [];
		var name:String = '';
		var c:Number=1;
		
		do
		{
			if( c>1 ) name = '_'+c;
			
			if( lv['values'+name ] != undefined )
			{
				this.styles[c-1] = this.make_style( lv, name, c, bgColour );
				this.styles[c-1].set_values( this.parseVal( lv['values'+name] ), labels );
			}
			else
				break;		// <-- stop loading data
				
			c++;
		}
		while( true );
	
	}
	
	private function make_style( lv:LoadVars, name:String, c:Number, bgColour:Number )
	{
		if( lv['line'+name] != undefined )
			return new LineStyle(lv['line'+name],'bar_'+c);
		if( lv['line_dot'+name] != undefined )
			return new LineDot(lv['line_dot'+name],bgColour,'bar_'+c);
		if( lv['line_hollow'+name] != undefined )
			return new LineHollow(lv['line_hollow'+name],bgColour,'bar_'+c);
		else if( lv['area_hollow'+name] != undefined )
			return new AreaHollow(lv['area_hollow'+name],bgColour,'bar_'+c);
		else if( lv['bar'+name] != undefined )
			return new BarStyle(lv['bar'+name],'bar_'+c);
		else if( lv['filled_bar'+name] != undefined )
			return new FilledBarStyle(lv['filled_bar'+name],'bar_'+c);
		else if( lv['bar_glass'+name] != undefined )
			return new BarGlassStyle(lv['bar_glass'+name],'bar_'+c);
		else if( lv['bar_fade'+name] != undefined )
			return new BarFade(lv['bar_fade'+name],'bar_'+c);
		else if( lv['bar_arrow'+name] != undefined )
			return new BarArrow(lv['bar_arrow'+name],'bar_'+c);
		else if( lv['bar_3d'+name] != undefined )
			return new Bar3D(lv['bar_3d'+name],'bar_'+c);
		else if( lv['pie'+name] != undefined )
			return new PieStyle(lv['pie'+name], lv.x_labels!=undefined ? lv['values'] : "", lv['links']);
	}
	
	private function parseVal( val:String ):Array
	{
		var tmp:Array = Array();
		
		var vals:Array = val.split(",");
		for( var i:Number=0; i < vals.length; i++ )
		{
			tmp.push( vals[i] );
		}
		return tmp;
	}
	
	public function length()
	{
		var max:Number = -1;

		for(var i:Number=0; i<this.styles.length; i++ )
			max = Math.max( max, this.styles[i].values.length );

		return max;
	}
	
	function _count_bars()
	{
		// count how many sets of bars we have
		var bar_count:Number = 0;
		for( var i=0; i<this.styles.length; i++ )
			if( this.styles[i].is_bar )
				bar_count++;

		return bar_count;
	}
	
	// If the current line is to be drawn on y2 (defined in data values, y2_lines)
	private function is_right( y2lines:Array, line:Number )
	{
			//var y2lines:Array = _root.lv.y2_lines.split(",");
			var right:Boolean = false;
			for( var i:Number=0; i<y2lines.length; i++ )
			{
				if(y2lines[i] == line)
					right = true; 
			}
			
			return right;
	}
	
	function _do_it()
	{
		
	}
	
	// get x, y co-ords of vals
	function move( b:Box, min:Number, max:Number, min2:Number, max2:Number )
	{
		// If we have a second y-axel
		if( 1==0 )//_root.lv.show_y2 && _root.lv.y2_lines)
		{
			var y2lines:Array = _root.lv.y2_lines.split(",");
			
			var bar_count:Number = this._count_bars();
			var bar:Number = 0;
			
			for( var c:Number=0; c<this.styles.length; c++ )
			{				
				// If the current axel is to be drawn on y2 (defined in data values, y2_lines)
				if( is_right(c+1) )
				{
					// move values.. 
					var tickY:Number = b.height / (max2-min2);
				}
				else
				{
					var tickY:Number = b.height / (max-min);
				}
					
					
						this.styles[c].valPos( b, tickY, min2, bar_count, bar );
						if( this.styles[c].is_bar )
							bar++;
			
				// draw the bars and dots ontop of the line
				for( var c:Number=0; c < this.styles.length; c++ )
				{
					this.styles[c].draw();
				}
			}
			
		}
		else
		{
		
			var bar_count:Number = this._count_bars();
			var bar:Number = 0;
			var y2:Boolean = false;
			var y2lines:Array;
			
			if( _root.lv.show_y2 != undefined )
				if( _root.lv.show_y2 != 'false' )
					if( _root.lv.y2_lines != undefined )
					{
						y2 = true;
						y2lines = _root.lv.y2_lines.split(",");
					}
			
			for( var c:Number=0; c<this.styles.length; c++ )
			{
				var right_axis:Boolean = false;
				
				// move values...
				if( y2 && is_right(y2lines,c+1) )
					right_axis = true;

				this.styles[c].valPos( b, right_axis, min, bar_count, bar );
				if( this.styles[c].is_bar )
					bar++;
			}
			
			// draw the bars and dots ontop of the line
			for( var c:Number=0; c < this.styles.length; c++ )
			{
				this.styles[c].draw();
			}
		}

	}
}