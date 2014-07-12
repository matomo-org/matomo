/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * @constructor
 */
function menu() {
    this.param = {};
}

menu.prototype =
{
    resetTimer: null,

    adaptSubMenuHeight: function() {
        var subNavHeight = $('.sfHover > ul').outerHeight();
        $('.nav_sep').height(subNavHeight);
    },

    overMainLI: function () {
        var $this = $(this);
        $this.siblings().removeClass('sfHover');
        $this.addClass('sfHover');
        menu.prototype.adaptSubMenuHeight();
        clearTimeout(menu.prototype.resetTimer);
    },

    outMainLI: function () {
        clearTimeout(menu.prototype.resetTimer);
        menu.prototype.resetTimer = setTimeout(function() {
            $('.Menu-tabList > .sfHover', this.menuNode).removeClass('sfHover');
            $('.Menu-tabList > .sfActive', this.menuNode).addClass('sfHover');
            menu.prototype.adaptSubMenuHeight();
        }, 2000);
    },

    onItemClick: function (item) {
        $('.Menu--dashboard').trigger('piwikSwitchPage', item);
        broadcast.propagateAjax( $(item).attr('href').substr(1) );
        return false;
    },

    init: function () {
        this.menuNode = $('.Menu--dashboard');

        this.menuNode.find("li:has(ul),li#Searchmenu").hover(this.overMainLI, this.outMainLI);

        // add id to all li menu to support menu identification.
        // for all sub menu we want to have a unique id based on their module and action
        // for main menu we want to add just the module as its id.
        this.menuNode.find('li').each(function () {
            var link = $(this).find('a');
            if (!link) {
                return;
            }
            var href = link.attr('href');
            if (!href) {
                return;
            }
            var url = href.substr(1);

            var module = broadcast.getValueFromUrl('module', url);
            var action = broadcast.getValueFromUrl('action', url);

            var moduleId = broadcast.getValueFromUrl("idGoal", url) || broadcast.getValueFromUrl("idDashboard", url);
            var main_menu = $(this).parent().hasClass('Menu-tabList') ? true : false;
            if (main_menu) {
                $(this).attr({id: module});
            }
            // if there's a idGoal or idDashboard, use this in the ID
            else if (moduleId != '') {
                $(this).attr({id: module + '_' + action + '_' + moduleId});
            }
            else {
                $(this).attr({id: module + '_' + action});
            }
        });

        menu.prototype.adaptSubMenuHeight();
    },

    activateMenu: function (module, action, id) {
        this.menuNode.find('li').removeClass('sfHover').removeClass('sfActive');
        var $li = this.getSubmenuID(module, id, action);
        var mainLi = $("#" + module);
        if (!mainLi.length) {
            mainLi = $li.parents('li');
        }

        mainLi.addClass('sfActive').addClass('sfHover');

        $li.addClass('sfHover');
    },

    // getting the right li is a little tricky since goals uses idGoal, and overview is index.
    getSubmenuID: function (module, id, action) {
        var $li = '';
        // So, if module is Goals, id is present, and action is not Index, must be one of the goals
        if (module == 'Goals' && id != '' && (action != 'index')) {
            $li = $("#" + module + "_" + action + "_" + id);
            // if module is Dashboard and id is present, must be one of the dashboards
        } else if (module == 'Dashboard') {
            if (!id) id = 1;
            $li = $("#" + module + "_" + action + "_" + id);
        } else {
            $li = $("#" + module + "_" + action);
        }
        return $li;
    },

    loadFirstSection: function () {
        if (broadcast.isHashExists() == false) {
            $('li:first a:first', this.menuNode).click().addClass('sfHover').addClass('sfActive');
        }
    }
};
