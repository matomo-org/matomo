class YLegend
{
	public var mc:TextField = undefined;
	
	function YLegend( lv:LoadVars, nr:Number )
	{

		if( lv.y_legend == undefined && lv.y2_legend == undefined)
			return;
		
		// parse the data file string:
		if(nr == 2) 
			var tmp:Array = lv.y2_legend.split(',');
		else
			var tmp:Array = lv.y_legend.split(',');
			
		var text:String = tmp[0];
		var size:Number = Number( tmp[1] );
		var colour:Number = _root.get_colour( tmp[2] );

		if(text == undefined) return;

		if(nr == 1) 
			this.mc = _root.createTextField("y_legend", _root.getNextHighestDepth(), 0, 0, 200, 200);
		else
			this.mc = _root.createTextField("y2_legend", _root.getNextHighestDepth(), 0, 0, 200, 200);
			

			
		this.mc.text = text;
		// so we can rotate the text
		this.mc.embedFonts = true;
		
		var fmt:TextFormat = new TextFormat();
		fmt.color = colour;
		// our embedded font - so we can rotate it
		// library->new font, linkage
		fmt.font = "Verdana_embed";
		
		fmt.size = size;
		fmt.align = "center";
		
		this.mc.setTextFormat(fmt);
		this.mc.autoSize = "left";
		this.mc._rotation = 270;
		this.mc.autoSize = "left";
	}
	
	function move(nr:Number)
	{

		if( this.mc == undefined )
			return;
			
		this.mc._y = (Stage.height/2)+(this.mc._height/2);
		if(nr == 2) 
			this.mc._x = Stage.width-this.mc._width;
		else
			this.mc._x = 0;
	}
	
	function width()
	{
		if( this.mc == undefined )
			return 0;
		else
			return this.mc._width;
	}
}