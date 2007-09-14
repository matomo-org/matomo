class XLegend extends Title
{

	// override the MovieClip name:
	private var name:String = 'x_legend';
	public var mc:TextField;
	
	function XLegend( lv:LoadVars )
	{
		if( lv.x_legend == undefined )
			return;
			
		var tmp:Array = lv.x_legend.split(',');
		
		var text:String = tmp[0];
		this.size = Number( tmp[1] );
		this.colour = _root.get_colour( tmp[2] );
		
		// call our parent (Title) constructor:
		// super.build( text );
		
		// while no CSS :
		this.build( text );
	}
	
	// remove when this gets CSS
	function build( text:String )
	{
		this.title = text;
		
		if( this.mc == undefined )
			this.mc = _root.createTextField( 'title', _root.getNextHighestDepth(), 0, 0, 200, 200 );
			
		this.mc.text = this.title;
		
		var fmt:TextFormat = new TextFormat();
		fmt.color = this.colour;
		fmt.font = "Verdana";
		fmt.size = this.size;
		
		fmt.align = "center";
	
		this.mc.setTextFormat(fmt);
		this.mc.autoSize = "left";
	}
	
	function get_legend()
	{
		return this.title;
	}
	
	function move()
	{
		// this will center it in the X
		super.move();
		// this will align bottom:
		this.mc._y = Stage.height - this.mc._height;
	}
	
	//
	// this is only here while title has CSS and x legend does not.
	// remove this when we put css in this object
	//
	function height()
	{
		// the title may be turned off:
		if( this.mc == undefined )
			return 0;
		else
			return this.mc._height;
	}
}