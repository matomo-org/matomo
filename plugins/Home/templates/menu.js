//DataTable constructor
function menu()
{
	this.param = new Object;
}
	
//Prototype of the DataTable object
menu.prototype =
{
	sectionLoaded: function( content )
	{
		$('#content').html( content );
	},
	
	init: function()
	{
		var self = this;

		$('.nav')
			.superfish({
				pathClass : 'current',
				animation : {opacity:'show'},
				delay : 1000
		});

		$('.nav a').click( function(){
		
			var urlAjax = $(this).attr('href');
			
			//prepare the ajax request
			var ajaxRequest = 
			{
				type: 'GET',
				url: urlAjax,
				dataType: 'html',
				async: true,
				error: ajaxHandleError,	// Callback when the request fails
				success: self.sectionLoaded, // Callback when the request succeeds
				data: new Object
			};
			$.ajax(ajaxRequest);
			
			return false;
		});
	}
}

$(document).ready( function(){
	piwikMenu = new menu();
	piwikMenu.init();
});