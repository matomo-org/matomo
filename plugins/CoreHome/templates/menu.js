// menu constructor
function menu()
{
	this.param = new Object;
}

// this should be in the menu prototype but I couldn't figure out 
// how to use it as a callback in the jquery ajax request


//Prototype of the DataTable object
menu.prototype =
{	
	menuSectionLoaded: function (content, urlLoaded)
	{
		if(urlLoaded == menu.prototype.lastUrlRequested)
		{
			$('#content').html( content ).show();
			$('#loadingPiwik').hide();
			menu.prototype.lastUrlRequested = null;
			//console.log('display '+urlLoaded);
		}
		else
		{
			//console.log('loaded '+urlLoaded+' but expecting to display '+menu.prototype.lastUrlRequested);
		}
	},
	
	customAjaxHandleError: function ()
	{
		menu.prototype.lastUrlRequested = null;
		ajaxHandleError();		
	},
	
	overMainLI: function ()
	{
		$(this).siblings().removeClass('sfHover');
	},
	
	outMainLI: function ()
	{
	},
	
	onClickLI: function ()
	{
		var self = this;
		var urlAjax = $('a',this).attr('name');
		function menuSectionLoaded(content)
		{
			menu.prototype.menuSectionLoaded(content, urlAjax);
		}
		
		// showing loading...
		$('#loadingPiwik').show();
		$('#content').hide();
		
		if(menu.prototype.lastUrlRequested == urlAjax)
		{
			return false;
		}
		menu.prototype.lastUrlRequested = urlAjax;
		
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
		
		//prepare the ajax request
		ajaxRequest = 
		{
			type: 'GET',
			url: urlAjax,
			dataType: 'html',
			async: true,
			error: menu.prototype.customAjaxHandleError,	// Callback when the request fails
			success: menuSectionLoaded, // Callback when the request succeeds
			data: new Object
		};
		$.ajax(ajaxRequest);
	
		return false;
		
	},

	init: function()
	{
		var self = this;
		this.param.superfish = $('.nav')
			.superfish({
				pathClass : 'current',
				animation : {opacity:'show'},
				delay : 1000
			});
		this.param.superfish.find("li")
			.click( self.onClickLI )
			;
			
		this.param.superfish
			.find("li:has(ul)")
			.hover(self.overMainLI, self.outMainLI)
			;
	},
	
	loadFirstSection: function()
	{
		var self=this;
		$('li:first', self.param.superfish)
			.click()
			.each(function(){
				$(this).showSuperfishUl(); 
		});
	}
}

$(document).ready( function(){
	piwikMenu = new menu();
	piwikMenu.init();
	piwikMenu.loadFirstSection();
});