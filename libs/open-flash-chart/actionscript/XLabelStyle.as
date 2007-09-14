class XLabelStyle
{
	public var size:Number = 10;
	public var colour:Number = 0x000000;
	public var vertical:Boolean = false;
	public var diag:Boolean = false;
	public var step:Number = 1;

	public function XLabelStyle( lv:LoadVars )
	{
		if( lv.x_label_style == undefined )
			return;
			
		var tmp:Array = lv.x_label_style.split(',');
		if( tmp.length > 0 )
			this.size = tmp[0];
			
		if( tmp.length > 1 )
			this.colour = _root.get_colour(tmp[1]);
			
		if( tmp.length > 2 )
		{
			this.vertical = (Number(tmp[2])==1);
			this.diag = (Number(tmp[2])==2);
		}
		
		if( tmp.length > 3 )
			if( Number(tmp[3]) > 0 )
				this.step = Number(tmp[3]);
	}
}