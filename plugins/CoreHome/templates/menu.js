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
            var idGoal = broadcast.getValueFromUrl("idGoal",url);
			
            var main_menu = ($(this).parent().attr("class").match(/nav/)) ? true : false;
            if(main_menu)
            {
                $(this).attr({id: module});
            }
            else
            {
				// so Goals plugin is a little different than other
				// we can't identify by it's modules_action so we uses its idGoals.
				if(idGoal != '') {
					$(this).attr({id: module + '_' + action + '_' + idGoal});
				}
				else {
					$(this).attr({id: module + '_' + action});
				}
            }
        });
	},

    activateMenu : function(module,action,idGoal)
    {
		// getting the right li is a little tricky since goals uses idGoal, and overview is index.
		var $li = '';
		// So, if module is Goals, idGoal is present, and action is not Index, must be one of the goals
		if(module == 'Goals' && idGoal != '' && action != 'index') {
			$li = $("#" + module + "_" + action + "_" + idGoal);
		} else {
			$li = $("#" + module + "_" + action);
		}

		// we can't find this li based on Module_action? then li only be the main menu. e.g Dashboard.
		var no_sub_menu = false;
		if($li.size() == 0) {
			$li = $("#" + module);
			no_sub_menu = true;
		}
		
		piwikMenu.param.superfish.find("li").removeClass('sfHover');
		if($li.find('ul li').size() != 0 || no_sub_menu == true) {
			// we clicked on a MAIN LI
			$.fn.superfish.currentActiveMenu = $li;
			$li.find('>ul li:first').addClass('sfHover');
			$li.find('ul').css({'display':'block','visibility': 'visible'});
		} else {
		// we are in the SUB UL LI
			$.fn.superfish.currentActiveMenu = $li.parents('li');
			$li.addClass('sfHover');
			$li.parents('ul').css({'display':'block','visibility': 'visible'});
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
