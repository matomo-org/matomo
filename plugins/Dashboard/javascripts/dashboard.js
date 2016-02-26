/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function initDashboard(dashboardId, dashboardLayout) {

    $('.dashboardSettings').show();
    initTopControls();

    // Embed dashboard
    if (!$('#header .navbar-right').length) {
        $('.dashboardSettings').after($('#Dashboard'));
        $('#Dashboard_embeddedIndex_' + dashboardId).addClass('sfActive');
    }

    widgetsHelper.getAvailableWidgets();

    $('#dashboardWidgetsArea')
        .on('dashboardempty', showEmptyDashboardNotification)
        .dashboard({
            idDashboard: dashboardId,
            layout: dashboardLayout
        });

    $('#columnPreview').find('>div').each(function () {
        var width = [];
        $('div', this).each(function () {
            width.push(this.className.replace(/width-/, ''));
        });
        $(this).attr('layout', width.join('-'));
    });

    $('#columnPreview').find('>div').on('click', function () {
        $('#columnPreview').find('>div').removeClass('choosen');
        $(this).addClass('choosen');
    });
}

function createDashboard() {
    $('#createDashboardName').val('');
    piwikHelper.modalConfirm('#createDashboardConfirm', {yes: function () {
        var dashboardName = $('#createDashboardName').val();
        var type = ($('#dashboard_type_empty:checked').length > 0) ? 'empty' : 'default';

        var ajaxRequest = new ajaxHelper();
        ajaxRequest.setLoadingElement();
        ajaxRequest.addParams({
            module: 'Dashboard',
            action: 'createNewDashboard'
        }, 'get');
        ajaxRequest.addParams({
            name: encodeURIComponent(dashboardName),
            type: type
        }, 'post');
        ajaxRequest.setCallback(
            function (id) {
                $('#dashboardWidgetsArea').dashboard('loadDashboard', id);
            }
        );
        ajaxRequest.send(true);
    }});
}

function resetDashboard() {
    piwikHelper.modalConfirm('#resetDashboardConfirm', {yes: function () { $('#dashboardWidgetsArea').dashboard('resetLayout'); }});
}

function renameDashboard() {
    $('#newDashboardName').val($('#dashboardWidgetsArea').dashboard('getDashboardName'));
    piwikHelper.modalConfirm('#renameDashboardConfirm', {yes: function () { $('#dashboardWidgetsArea').dashboard('setDashboardName', $('#newDashboardName').val()); }});
}

function removeDashboard() {
    $('#removeDashboardConfirm').find('h2 span').text($('#dashboardWidgetsArea').dashboard('getDashboardName'));
    piwikHelper.modalConfirm('#removeDashboardConfirm', {yes: function () { $('#dashboardWidgetsArea').dashboard('removeDashboard'); }});
}

function showChangeDashboardLayoutDialog() {
    $('#columnPreview').find('>div').removeClass('choosen');
    $('#columnPreview').find('>div[layout=' + $('#dashboardWidgetsArea').dashboard('getColumnLayout') + ']').addClass('choosen');
    piwikHelper.modalConfirm('#changeDashboardLayout', {yes: function () {
        $('#dashboardWidgetsArea').dashboard('setColumnLayout', $('#changeDashboardLayout').find('.choosen').attr('layout'));
    }});
}

function showEmptyDashboardNotification() {
    piwikHelper.modalConfirm('#dashboardEmptyNotification', {
        resetDashboard: function () { $('#dashboardWidgetsArea').dashboard('resetLayout'); },
        addWidget: function () { $('.dashboardSettings > a').trigger('click'); }
    });
}

function setAsDefaultWidgets() {
    piwikHelper.modalConfirm('#setAsDefaultWidgetsConfirm', {
        yes: function () { $('#dashboardWidgetsArea').dashboard('saveLayoutAsDefaultWidgetLayout'); }
    });
}

function copyDashboardToUser() {
    $('#copyDashboardName').val($('#dashboardWidgetsArea').dashboard('getDashboardName'));
    var ajaxRequest = new ajaxHelper();
    ajaxRequest.addParams({
        module: 'API',
        method: 'UsersManager.getUsers',
        format: 'json'
    }, 'get');
    ajaxRequest.setCallback(
        function (availableUsers) {
            $('#copyDashboardUser').empty();
            $('#copyDashboardUser').append(
                $('<option></option>').val(piwik.userLogin).text(piwik.userLogin)
            );
            $.each(availableUsers, function (index, user) {
                if (user.login != 'anonymous' && user.login != piwik.userLogin) {
                    $('#copyDashboardUser').append(
                        $('<option></option>').val(user.login).text(user.login + ' (' + user.alias + ')')
                    );
                }
            });
        }
    );
    ajaxRequest.send(true);

    piwikHelper.modalConfirm('#copyDashboardToUserConfirm', {
        yes: function () {
            var copyDashboardName = $('#copyDashboardName').val();
            var copyDashboardUser = $('#copyDashboardUser').val();

            var ajaxRequest = new ajaxHelper();
            ajaxRequest.addParams({
                module: 'Dashboard',
                action: 'copyDashboardToUser'
            }, 'get');
            ajaxRequest.addParams({
                name: encodeURIComponent(copyDashboardName),
                dashboardId: $('#dashboardWidgetsArea').dashboard('getDashboardId'),
                user: encodeURIComponent(copyDashboardUser)
            }, 'post');
            ajaxRequest.setCallback(
                function (id) {
                    $('#alert').find('h2').text(_pk_translate('Dashboard_DashboardCopied'));
                    piwikHelper.modalConfirm('#alert', {});
                }
            );
            ajaxRequest.send(true);
        }
    });
}

(function () {
    var exports = window.require('piwik/UI');
    var UIControl = exports.UIControl;

    /**
     * Contains logic common to all dashboard management controls. This is the JavaScript analog of
     * the DashboardSettingsControlBase PHP class.
     *
     * @param {Element} element The HTML element generated by the SegmentSelectorControl PHP class. Should
     *                          have the CSS class 'segmentEditorPanel'.
     * @constructor
     */
    var DashboardSettingsControlBase = function (element) {
        UIControl.call(this, element);

        // on menu item click, trigger action event on this
        var self = this;
        this.$element.on('click', 'ul.submenu li[data-action]', function (e) {
            if (!$(this).attr('disabled')) {
                self.$element.removeClass('expanded');
                $(self).trigger($(this).attr('data-action'));
            }
        });

        // open manager on open
        this.$element.on('click', function (e) {
            if ($(e.target).is('.dashboardSettings') || $(e.target).closest('.dashboardSettings').length) {
                self.onOpen();
            }
        });

        // handle manager close
        this.onBodyMouseUp = function (e) {
            if (!$(e.target).closest('.dashboardSettings').length
                && !$(e.target).is('.dashboardSettings')
            ) {
                self.$element.widgetPreview('reset');
                self.$element.removeClass('expanded');
            }
        };

        $('body').on('mouseup', this.onBodyMouseUp);

        // setup widgetPreview
        this.$element.widgetPreview({
            isWidgetAvailable: function (widgetUniqueId) {
                return self.isWidgetAvailable(widgetUniqueId);
            },
            onSelect: function (widgetUniqueId) {
                var widget = widgetsHelper.getWidgetObjectFromUniqueId(widgetUniqueId);
                self.$element.removeClass('expanded');

                self.widgetSelected(widget);
            },
            resetOnSelect: true
        });

        // on enter widget list category, reset widget preview
        this.$element.on('mouseenter', '.submenu > li', function (event) {
            if (!$('.widgetpreview-categorylist', event.target).length) {
                self.$element.widgetPreview('reset');
            }
        });
    };

    $.extend(DashboardSettingsControlBase.prototype, UIControl.prototype, {
        _destroy: function () {
            UIControl.prototype._destroy.call(this);

            $('body').off('mouseup', null, this.onBodyMouseUp);
        }
    });

    exports.DashboardSettingsControlBase = DashboardSettingsControlBase;

    /**
     * Sets up and handles events for the dashboard manager control.
     *
     * @param {Element} element The HTML element generated by the SegmentSelectorControl PHP class. Should
     *                          have the CSS class 'segmentEditorPanel'.
     * @constructor
     */
    var DashboardManagerControl = function (element) {
        DashboardSettingsControlBase.call(this, element);

        $(this).on('resetDashboard', function () {
            this.hide();
            resetDashboard();
        });

        $(this).on('showChangeDashboardLayoutDialog', function () {
            this.hide();
            showChangeDashboardLayoutDialog();
        });

        $(this).on('renameDashboard', function () {
            this.hide();
            renameDashboard();
        });

        $(this).on('removeDashboard', function () {
            this.hide();
            removeDashboard();
        });

        $(this).on('setAsDefaultWidgets', function () {
            this.hide();
            setAsDefaultWidgets();
        });

        $(this).on('copyDashboardToUser', function () {
            this.hide();
            copyDashboardToUser();
        });

        $(this).on('createDashboard', function () {
            this.hide();
            createDashboard();
        });
    };

    $.extend(DashboardManagerControl.prototype, DashboardSettingsControlBase.prototype, {
        onOpen: function () {
            if ($('#dashboardWidgetsArea').dashboard('isDefaultDashboard')) {
                $('[data-action=removeDashboard]', this.$element).attr('disabled', 'disabled');
                $(this.$element).tooltip({
                    items: '[data-action=removeDashboard]',
                    show: false,
                    hide: false,
                    track: true,
                    content: function() {
                        return _pk_translate('Dashboard_RemoveDefaultDashboardNotPossible')
                    },
                    tooltipClass: 'small'
                });
            } else {
                $('[data-action=removeDashboard]', this.$element).removeAttr('disabled');
                // try to remove tooltip if any
                try {
                    $(this.$element).tooltip('destroy');
                } catch (e) { }
             }
        },

        hide: function () {
            this.$element.removeClass('expanded');
        },

        isWidgetAvailable: function (widgetUniqueId) {
            return !$('#dashboardWidgetsArea').find('[widgetId=' + widgetUniqueId + ']').length;
        },

        widgetSelected: function (widget) {
            $('#dashboardWidgetsArea').dashboard('addWidget', widget.uniqueId, 1, widget.parameters, true, false);
        }
    });

    DashboardManagerControl.initElements = function () {
        UIControl.initElements(this, '.dashboard-manager');
    };

    exports.DashboardManagerControl = DashboardManagerControl;
}());