class LineStyle extends Style
{
	private var mc:MovieClip;
	
	public function LineStyle( val:String, name:String )
	{
		
		var vals:Array = val.split(",");
		this.line_width = Number( vals[0] );
		this.colour = _root.get_colour( vals[1] );
		
		if( vals.length > 2 )
			this.key = vals[2];
			
		if( vals.length > 3 )
			this.font_size = Number( vals[3] );
		
		if( length( vals ) > 4 )
			this.circle_size = Number( vals[4] );
			
		this.mc = _root.createEmptyMovieClip(name, _root.getNextHighestDepth());
			
	}
	
	public function valPos( b:Box, right_axis:Boolean, min:Number )
	{
		this.ExPoints=Array();
		
		for( var i:Number=0; i < this.values.length; i++)
		{
			
			if( this.values[i] == 'null' )
			{
				this.ExPoints.push( null );
			}
			else
			{
				this.ExPoints.push(
					new ExPoint(
						0,													// x position of value
						b.get_x_pos( i ),
						b.getY( Number(this.values[i]), right_axis ),
						0,//bar_width,
						0,//b.bottom,
						Number( this.values[i] )
						)
					);
			}
		}
	}
	
	// Draw lines...
	public function draw()
	{
		this.mc.clear();
		mc.lineStyle( this.line_width, this.colour, 100); // <-- alpha 0 to 100
	
		var first:Boolean = true;
		
		for( var i:Number=0; i < this.ExPoints.length; i++ )
		{
			// skip null values
			if( this.ExPoints[i] != null )
			{
				if( first )
				{
					mc.moveTo(this.ExPoints[i].center,this.ExPoints[i].y);
					first = false;
				}
				else
					mc.lineTo(this.ExPoints[i].center,this.ExPoints[i].y);
			}
		}
	}
	
	private function rollOver()
	{
		
	}
	
	// called by AreaHollow, LineHollow
	public function make_dot( mc:MovieClip, col:Number, bg:Number, tool_tip_title:String, tool_tip_value:String )
	{
	
		if( tool_tip_title != undefined )
			mc.tool_tip_title = tool_tip_title;
		else
			mc.tool_tip_title = '';
			
		mc.tool_tip_value = tool_tip_value;
		
		//mc.onRollOver = _root.circleBig;
		
		//
		// extremely curious syntax, but it works.
		// add a roll over function to the MovieClip
		//
		var ref = mc;
		mc.onRollOver = function(){
			ref._width += 4;
			ref._height += 4;
			_root.show_tip( this, this._x, this._y-20, this.tool_tip_title, this.tool_tip_value );
		};

		// make the circle shrink and remove tooltip:
		mc.onRollOut = function(){
			_root.hide_tip( this );
			ref._width -= 4;
			ref._height -= 4;
		};

		mc.lineStyle( 0, bg, 100);
		mc.fillCircle( 0, 0, this.circle_size, 15, bg );
		mc.fillCircle( 0, 0, this.circle_size-1, 15, col);
	}
	
	public function move_dot( val:ExPoint, mc:MovieClip )
	{
		//trace(val.center);
		// Move and fix the dots...
		mc._x = val.center;
		mc._y = val.y;
	}
	
	public function add( val:String )
	{
		this.values.push( val );
	}
}