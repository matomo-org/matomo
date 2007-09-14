class Square
{
	public var top:Number=0;
	public var left:Number=0;
	public var right:Number=0;
	public var bottom:Number=0;
	public var width:Number=0;
	public var height:Number=0;

	public function Square( left:Number, top:Number, right:Number, bottom:Number )
	{
		this.top = top;
		this.left = left;
		this.right = right;
		this.bottom = bottom;
		this.width = right-left;
		this.height = bottom-top;
	}
}