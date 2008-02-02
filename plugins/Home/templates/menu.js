// menu constructor
function menu()
{
	this.param = new Object;
}

// this should be in the menu prototype but I couldn't figure out 
// how to use it as a callback in the jquery ajax request
function menuSectionLoaded(content)
{
	$('#content').html( content );
}

//Prototype of the DataTable object
menu.prototype =
{	
	overMainLI: function ()
	{
		$(this).siblings().removeClass('sfHover');
	},
	
	outMainLI: function ()
	{
	},
	
	onClickLI: function ()
	{
		// we are in the SUB UL LI
		if($(this).find('ul li').size() == 0)
		{
		//	console.log('clicked SUB LI');
			$(this).addClass('sfHover');
		}
		// we clicked on a MAIN LI
		else
		{	
			$(this).find('>ul li:first').addClass('sfHover');
		}
		
		var urlAjax = $('a',this).attr('href');
	
		var self = this;
		
		//prepare the ajax request
		var ajaxRequest = 
		{
			type: 'GET',
			url: urlAjax,
			dataType: 'html',
			async: true,
			error: ajaxHandleError,	// Callback when the request fails
			success: menuSectionLoaded, // Callback when the request succeeds
			data: new Object
		};
		$.ajax(ajaxRequest);
		
		return false;
		
	},

	init: function()
	{
		var self = this;

		var superfish = $('.nav')
			.superfish({
				pathClass : 'current',
				animation : {opacity:'show'},
				delay : 1000
			});
		superfish.find("li")
			.click( self.onClickLI )
			;
			
		superfish
			.find("li:has(ul)")
			.hover(self.overMainLI, self.outMainLI)
			;
	}
}

$(document).ready( function(){
	piwikMenu = new menu();
	piwikMenu.init();
});