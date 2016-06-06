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

    onItemClick: function (e) {
        if (e.which === 2) {
            return;
        }

        $('#secondNavBar').removeClass('open fadeInLeft');

        var $link = $(this);
        var href = $link.attr('href');

        if (!$('#content.admin').size()) {
            if (!href && $link.parent().is('.menuTab')) {
                var $li = $link.parents('li').first();

                if ($li.hasClass('sfActive')) {
                    $li.removeClass('sfActive');
                } else {
                    $li.siblings().removeClass('sfActive');
                    $li.addClass('sfActive');
                }
                
                var $children = $li.find('ul li > .item');
                if ($children.length === 1) {
                    $children.first().click();
                }


            } else if (href) {
                $('#secondNavBar').trigger('piwikSwitchPage', this);

                broadcast.propagateAjax(href.substr(1));
            }

            return false;
        }

        return !!href;
    },

    isAdmin: function () {
      return !!$('#content.admin').size();
    },

    init: function () {
        this.menuNode = $('#secondNavBar');

        // add id to all li menu to support menu identification.
        // for all sub menu we want to have a unique id based on their module and action
        // for main menu we want to add just the module as its id.
        this.menuNode.find('li').each(function () {
            var $this = $(this);
            var link = $this.find('a');

            var main_menu = $this.parent().hasClass('navbar') ? true : false;

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

            if (main_menu) {
                $this.attr({id: module});
            }
            // if there's a idGoal or idDashboard, use this in the ID
            else if (moduleId != '') {
                $(this).attr({id: module + '_' + action + '_' + moduleId});
            }
            else {
                $(this).attr({id: module + '_' + action});
            }
        });

        this.menuNode.find('a.item').click(this.onItemClick);

        var self = this;
        $('#header .toggle-second-menu').click(function () {
            self.menuNode.toggleClass('open fadeInLeft');
        });
    },

    activateMenu: function (module, action, params) {
        params = params || {};
        params.module = module;
        params.action = action;

        this.menuNode.find('li').removeClass('sfActive');

        var isAdmin = this.isAdmin();

        var $activeLink = this.menuNode.find('a').filter(function () {
            var url = $(this).attr('href');
            if (!url) {
                return false;
            }

            var found = false;
            for (var key in params) {
                if (!params.hasOwnProperty(key)
                    || !params[key]
                ) {
                    continue;
                }

                var actual;

                if (isAdmin) {
                    actual = broadcast.getValueFromUrl(key, url);
                } else {
                    actual = broadcast.getValueFromHash(key, url);
                }

                if (actual != params[key]) {
                    return false;
                }

                found = true;
                // at least one param must match. Otherwise all menu items might be highlighted if params[key] = null;
            }

            return found;
        });

        $activeLink.closest('li').addClass('sfActive');
        $activeLink.closest('li.menuTab').addClass('sfActive');
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
            $('.navbar li:first ul a:first', this.menuNode).click().addClass('sfActive');
        }
    }
};