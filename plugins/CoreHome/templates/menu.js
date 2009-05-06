function menu()
{
	this.param = new Object;
}

menu.prototype =
{	
	menuSectionLoaded: function (content, urlLoaded)
	{
		if(urlLoaded == menu.prototype.lastUrlRequested)
		{
			$('#content').html( content ).show();
			$('#loadingPiwik').hide();
			menu.prototype.lastUrlRequested = null;
		}
	},
	
	customAjaxHandleError: function ()
	{
		menu.prototype.lastUrlRequested = null;
		piwikHelper.ajaxHandleError();		
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
		 broadcast.propagateAjax(urlAjax);
	
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
        // add id to all li menu to suport menu identification.
        // for all sub menu we want to have a unique id based on their module and action
        // for main menu we want to add just the module as its id.
        this.param.superfish.find('li').each(function(){
            var url = $(this).find('a').attr('name');
            var module = broadcast.getValueFromUrl("module",url);
            var action = broadcast.getValueFromUrl("action",url);

            var main_menu = ($(this).parent().attr("class").match(/nav/)) ? true : false;
            if(main_menu)
            {
                $(this).attr({id: module});
            }
            else
            {
                $(this).attr({id: module + '_' + action});
            }
        });
	},

    activateMenu : function(module,action)
    {
		// we are in the SUB UL LI
		var $li = $("#" + module + "_" + action);
		piwikMenu.param.superfish.find("li").removeClass('sfHover');
		// we are in the SUB UL LI
		if($li.find('ul li').size() == 0) {
	        $.fn.superfish.currentActiveMenu = $li.parents('li');
	        $li.addClass('sfHover');
	        $li.parents('ul').css({'display':'block','visibility': 'visible'});
		} else {
			// we clicked on a MAIN LI
	        $.fn.superfish.currentActiveMenu = $li;
	        $li.find('>ul li:first').addClass('sfHover');
	        $li.find('ul').css({'display':'block','visibility': 'visible'});
		}
	    $.fn.superfish.currentActiveMenu.showSuperfishUl().siblings().hideSuperfishUl();
    },

	loadFirstSection: function()
	{
		var self=this;
        if(broadcast.isHashExists() == false) {
            $('li:first', self.param.superfish)
		    .click()
		    .each(function(){ $(this).showSuperfishUl(); });
        }
	}
};

$(document).ready( function(){
	piwikMenu = new menu();
	piwikMenu.init();
	piwikMenu.loadFirstSection();
	broadcast.init();
});
