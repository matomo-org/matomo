class YLabelStyle
{
	public var size:Number;
	public var colour:Number = 0x000000;
	
	public var show_labels:Boolean;
	public var show_y2:Boolean;

	public function YLabelStyle( lv:LoadVars, nr:Number )
	{
		this.size = 10;
		this.colour = 0x000000;
		this.show_labels = true;
		
		if(nr != 2) {
		
		if( lv.y_label_style == undefined )
			return;
			
		// is it CSV?
		var comma:Number = lv.y_label_style.lastIndexOf(',');
		
		if( comma<0 )
		{
			var none:Number = lv.y_label_style.lastIndexOf('none',0);
			if( none>-1 )
			{
				this.show_labels = false;
			}
		}
		else
		{
			var tmp:Array = lv.y_label_style.split(',');
			if( tmp.length > 0 )
				this.size = tmp[0];
			
			if( tmp.length > 1 )
				this.colour = _root.get_colour(tmp[1]);
		}
		}else{ // if (nr..
		
			if( lv.y2_label_style == undefined )
				return;
				
			// is it CSV?
			var comma:Number = lv.y2_label_style.lastIndexOf(',');
			
			if( comma<0 )
			{
				var none:Number = lv.y2_label_style.lastIndexOf('none',0);
				if( none>-1 )
				{
					this.show_labels = false;
				}
			}
			else
			{
				var tmp:Array = lv.y2_label_style.split(',');
				if( tmp.length > 0 )
					this.size = tmp[0];
				
				if( tmp.length > 1 )
					this.colour = _root.get_colour(tmp[1]);
			}
		
		}
	}
}