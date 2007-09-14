class MinMax
{
	public var y_min:Number=0;
	public var y_max:Number=0;
	public var y2_min:Number=0;
	public var y2_max:Number=0;
	
	function MinMax( lv:LoadVars )
	{
		if( lv.y_max == undefined )
			this.y_max = 10;
		else
			this.y_max = Number(lv.y_max)
			
		if( lv.y_min == undefined )
			this.y_min = 0;
		else
			this.y_min = Number(lv.y_min)
		
		// y 2
		if( lv.y2_max == undefined )
			this.y2_max = 10;
		else
			this.y2_max = Number(lv.y2_max)
			
		if( lv.y2_min == undefined )
			this.y2_min = 0;
		else
			this.y2_min = Number(lv.y2_min)
	}
	
	function range( right:Boolean )
	{
		if( right )
			return this.y2_max-this.y2_min;
		else
			return this.y_max-this.y_min;
	}
	
	function min( right:Boolean )
	{
		if( right )
			return this.y2_min;
		else
			return this.y_min;
	}
}