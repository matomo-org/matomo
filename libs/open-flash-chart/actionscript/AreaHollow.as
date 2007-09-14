class AreaHollow extends LineHollow
{
	public var bottom:Number=-1;
	public var alpha:Number=50;
	private var mc_area:MovieClip
	private var fill_colour:Number;
	
	public function AreaHollow( val:String, bgColour:Number, name:String )
	{
		//this.values = values;
		this.bgColour = bgColour;
		this.name = name;
		
		var vals:Array = val.split(",");
		this.line_width = Number( vals[0] );
		this.circle_size = Number( vals[1] );
		this.alpha =  Number( vals[2] );
		this.colour = _root.get_colour( vals[3] );
		
		if( vals.length > 4 )
			this.key = vals[4];
			
		if( vals.length > 5 )
			this.font_size = Number( vals[5] );

		// patch from Will Henry
		if( vals.length > 6 )
			this.fill_colour = _root.get_colour( vals[6] );
		else
			this.fill_colour = this.colour;
			
		// draw the area behine the line:
		this.mc_area = _root.createEmptyMovieClip( name+'_area', _root.getNextHighestDepth());
		this.mc = _root.createEmptyMovieClip( name, _root.getNextHighestDepth());
	}
	
	public function valPos( b:Box, right_axis:Boolean, min:Number )
	{
		// we need this to draw the area:
		this.bottom = b.getY( 0, right_axis );
		super.valPos( b, right_axis, min );
	}
	
	public function draw()
	{
		var colour:Number = 0x000000;
		if(this.fill_colour == '') {
			this.fill_colour = this.colour;
		}
		
		this.mc_area.clear();
		
//		this.mc_area.beginFill( this.colour, this.alpha );

		this.mc_area.beginFill(this.fill_colour, this.alpha );
    	
		var pos:Number = 0;
		while( this.ExPoints[pos] == null )
			pos++;
			
		this.mc_area.moveTo( this.ExPoints[pos].center, this.bottom );
		this.mc_area.lineTo( this.ExPoints[pos].center, this.ExPoints[pos].y );
		
		var last:ExPoint = null;
		for( var i:Number=pos+1; i < this.ExPoints.length; i++ )
		{
			if( this.ExPoints[i] != null )
			{
				this.mc_area.lineTo( this.ExPoints[i].center, this.ExPoints[i].y );
				last = this.ExPoints[i];
			}
		}
		
		if( last != null )
			this.mc_area.lineTo( last.center, this.bottom );
			
		this.mc_area.endFill();
		
		// now draw the line + hollow dots
		super.draw();
	}
}