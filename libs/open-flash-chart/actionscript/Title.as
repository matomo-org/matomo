class Title
{
	public var mc:MovieClip;
	public var title:String = '';
	public var colour:Number;
	public var size:Number;
	private var top_padding:Number = 0;
	
	private var style:Css;
	
	function Title( lv:LoadVars )
	{
		if( lv.title == undefined )
			return;
			
		var tmp:Array = lv.title.split(',');
		
		this.style = new Css( tmp[1] );
		this.build( tmp[0] );
	}
	
	function build( text:String )
	{
		this.title = text;
		
		if( this.mc == undefined )
		{
			this.mc = _root.createEmptyMovieClip( "title", _root.getNextHighestDepth() );
			this.mc.txt = this.mc.createTextField( 'title', _root.getNextHighestDepth(), 0, 0, 200, 200 );
		}
			
		this.mc.txt.text = this.title;
		
		var fmt:TextFormat = new TextFormat();
		fmt.color = this.style.get( 'color' );
		fmt.font = "Verdana";
		fmt.size = this.style.get( 'font-size' );
		
		fmt.align = "center";
	
		this.mc.txt.setTextFormat(fmt);
		this.mc.txt.autoSize = "left";
		
		this.mc.txt._y = this.style.padding_top;
		this.mc.txt._x = this.style.padding_left;
		
		var height:Number = this.style.padding_top+this.mc.txt._height+this.style.padding_bottom;
		var width:Number = this.style.padding_left+this.mc.txt._width+this.style.padding_right;

		this.mc.beginFill( this.style.get( 'background-color' ), 100);
		this.mc.moveTo(0, 0);
		this.mc.lineTo(width, 0);
		this.mc.lineTo(width, height);
		this.mc.lineTo(0, height);
		this.mc.lineTo(0, 0);
		this.mc.endFill();
	}
	
	function move()
	{
		if( this.mc != undefined )
		{
			//
			// is the title aligned (text-align: xxx)?
			//
			var tmp:String = this.style.get( 'text-align' );
			switch( tmp )
			{
				case 'left':
					this.mc._x = this.style.get( 'margin-left' );
					break;
					
				case 'right':
					this.mc._x = Stage.width - ( this.mc._width + this.style.get( 'margin-right' ) );
					break;
					
				case 'center':
				default:
					this.mc._x = (Stage.width/2) - (this.mc._width/2);
					break;
			}
			
			this.mc._y = this.style.get( 'margin-top' );
		}
	}
	
	function height()
	{
		// the title may be turned off:
		if( this.mc == undefined )
			return 0;
		else
		{
			return this.style.padding_top+
				this.style.margin_top+
				this.mc.txt._height+
				this.style.padding_bottom+
				this.style.margin_bottom;
		}
	}
}