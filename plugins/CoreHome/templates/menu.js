/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function menu()
{
    this.param = {};
}

menu.prototype =
{    
    resetTimer: null,
    
    overMainLI: function ()
    {
        $(this).siblings().removeClass('sfHover');
        $(this).addClass('sfHover');
        clearTimeout(menu.prototype.resetTimer);
    },
    
    outMainLI: function ()
    {
        clearTimeout(menu.prototype.resetTimer);
        menu.prototype.resetTimer = setTimeout(function(){
            $('.nav>.sfHover').removeClass('sfHover');
            $('.nav>.sfActive').addClass('sfHover');
        }, 2000);
    },
    
    onItemClick: function (item)
    {
        $('ul.nav').trigger('piwikSwitchPage');
        broadcast.propagateAjax($(item).attr('name'));
        return false;
    },
    
    init: function()
    {
        this.menuNode = $('.nav');
        
        //sub LI auto height
        $('.nav li li a').each(function(){$(this).css({width:$(this).width()+30, paddingLeft:0, paddingRight:0});});
        
        this.menuNode.find("li:has(ul)").hover(this.overMainLI, this.outMainLI);
        
        // add id to all li menu to suport menu identification.
        // for all sub menu we want to have a unique id based on their module and action
        // for main menu we want to add just the module as its id.
        this.menuNode.find('li').each(function(){
            var url = $(this).find('a').attr('name');
            var module = broadcast.getValueFromUrl("module",url);
            var action = broadcast.getValueFromUrl("action",url);
            var moduleId = broadcast.getValueFromUrl("idGoal",url) || broadcast.getValueFromUrl("idDashboard",url);
            var main_menu = $(this).parent().hasClass('nav') ? true : false;
            if(main_menu)
            {
                $(this).attr({id: module});
            }
            else
            {
                // so Goals plugin and Dashboard is a little different than other
                // we can't identify by it's modules_action so we uses its ids.
                if(moduleId != '') {
                    $(this).attr({id: module + '_' + action + '_' + moduleId});
                }
                else {
                    $(this).attr({id: module + '_' + action});
                }
            }
        });
    },

    activateMenu : function(module,action,id)
    {
        this.menuNode.find('li').removeClass('sfHover').removeClass('sfActive');
        
        // getting the right li is a little tricky since goals uses idGoal, and overview is index.
        var $li = '';
        // So, if module is Goals, id is present, and action is not Index, must be one of the goals
        if(module == 'Goals' && id != '' && (action != 'index')) {
            $li = $("#" + module + "_" + action + "_" + id);
        // if module is Dashboard and id is present, must be one of the dashboards
        } else if(module == 'Dashboard') {
            if(!id) id = 1;
            $li = $("#" + module + "_" + action + "_" + id);
        } else {
            $li = $("#" + module + "_" + action);
        }
        
        var mainLi = $("#" + module);
        if(!mainLi.length) {
            mainLi = $li.parents('li');
        }
        
        mainLi.addClass('sfActive').addClass('sfHover');

        $li.addClass('sfHover');
    },

    loadFirstSection: function()
    {
        if(broadcast.isHashExists() == false) {
            $('li:first a:first', this.menuNode).click().addClass('sfHover').addClass('sfActive');
        }
    }
};

$(document).ready( function(){
    if($('.nav').size()) {
        piwikMenu = new menu();
        piwikMenu.init();
        piwikMenu.loadFirstSection();
        broadcast.init();
    }
});
