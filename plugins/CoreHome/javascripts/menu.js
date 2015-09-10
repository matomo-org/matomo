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

    onItemClick: function (e) {
        if (e.which === 2) {
            return;
        }
        $('.Menu--dashboard').trigger('piwikSwitchPage', this);
        broadcast.propagateAjax( $(this).attr('href').substr(1) );
        return false;
    },

    init: function () {
        this.menuNode = $('.Menu--dashboard');

        this.menuNode.find("li:has(ul),li#Searchmenu").hover(this.overMainLI, this.outMainLI);
        this.menuNode.find("li:has(ul),li#Searchmenu").focusin(this.overMainLI);

        this.menuNode.find('a.menuItem').click(this.onItemClick);

        menu.prototype.adaptSubMenuHeight();
    },

    activateMenu: function (module, action, params) {
        params = params || {};
        params.module = module;
        params.action = action;

        this.menuNode.find('li').removeClass('sfHover').removeClass('sfActive');
        var $activeLink = this.menuNode.find('a').filter(function () {
            var url = $(this).attr('href');
            if (!url) {
                return false;
            }

            for (var key in params) {
                if (!params.hasOwnProperty(key)
                    || !params[key]
                ) {
                    continue;
                }

                var actual = broadcast.getValueFromHash(key, url);
                if (actual != params[key]) {
                    return false;
                }
            }

            return true;
        });

        $activeLink.closest('li').addClass('sfHover');
        $activeLink.closest('li.menuTab').addClass('sfActive').addClass('sfHover');
    },

    // getting the right li is a little tricky since goals uses idGoal, and overview is index.
    getSubmenuID: function (module, id, action) {
        var $li = '';
        // So, if module is Goals, id is present, and action is not Index, must be one of the goals
        if ((module == 'Goals' || module == 'Ecommerce') && id != '' && (action != 'index')) {
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
