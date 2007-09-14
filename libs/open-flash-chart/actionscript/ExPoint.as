class ExPoint
{
	public var left:Number=0;		// <-- for bars
	public var center:Number=0;		// <-- for dots
	public var y:Number=0;
	public var tooltip:String = "";
	
	public var bar_width:Number=0;
	public var bar_bottom:Number=0;
	
	public function ExPoint( left:Number, center:Number, y:Number, width:Number, bar_bottom:Number, tooltip:Number )
	{
		this.left = left;
		this.center = center;
		this.y = y;
		this.bar_width = width;
		this.bar_bottom = bar_bottom;
		this.tooltip = _root.format(tooltip);
	}
	
	public function toString()
	{
		return "left :"+ this.left;
	}
}