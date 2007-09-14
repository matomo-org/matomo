class YTicks
{
	// default tick sizes (small,big,big every step):
	public var big:Number = 5;
	public var small:Number = 2;
	public var steps:Number = 2;
	
	function YTicks( lv:LoadVars )
	{
		if( lv.y_ticks != undefined )
		{
			var ticks:Array = lv.y_ticks.split(',');
			if( ticks.length == 3 )
			{
				this.small = Number(ticks[0]);
				this.big = Number(ticks[1]);
				this.steps = Number(ticks[2]);
			}
		}
	}
}