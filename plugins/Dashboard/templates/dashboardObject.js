/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function( $ ){

    /**
     * Current dashboard column layout
     * @type {object}
     */
    var dashboardLayout     = {};
    /**
     * Id of current dashboard
     * @type {int}
     */
    var dashboardId         = 1;
    /**
     * Name of current dashboard
     * @type {string}
     */
    var dashboardName       = '';
    /**
     * Holds a reference to the dashboard element
     * @type {object}
     */
    var dashboardElement    = null;
    /**
     * Boolean indicating wether the layout config has been changed or not
     * @type {boolean}
     */
    var dashboardChanged    = false;

    /**
     * public methods of dashboard plugin
     * all methods defined here are accessible with $(selector).dashboard('method', param, param, ...)
     */
    var methods = {

        /**
         * creates a dashboard object
         *
         * @param {object} options
         */
        init: function(options) {

            dashboardElement = this;

            if (options.idDashboard) {
                dashboardId = options.idDashboard;
            }

            if (options.name) {
                dashboardName = options.name;
            }

            if (options.layout) {
                generateLayout(options.layout);
                buildMenu();
            } else {
                methods.loadDashboard.apply(this, [dashboardId]);
            }

            return this;
        },

        /**
         * Destroys the dashboard object and all its childrens
         *
         * @return void
         */
        destroy: function() {
            $(dashboardElement).remove();
            dashboardElement = null;
            var widgets = $('[widgetId]');
            for (var i=0; i < widgets.length; i++) {
                $(widgets[i]).dashboardWidget('destroy');
            }
        },

        /**
         * Load dashboard with the given id
         *
         * @param {int} dashboardIdToLoad
         */
        loadDashboard: function(dashboardIdToLoad) {

            $(dashboardElement).empty();
            dashboardName   = '';
            dashboardLayout = null;
            dashboardId     = dashboardIdToLoad;
            piwikHelper.showAjaxLoading();
            fetchLayout(generateLayout);
            buildMenu();
            return this;
        },

        /**
         * Change current column layout to the given one
         *
         * @param {String} newLayout
         */
        setColumnLayout: function(newLayout) {
            adjustDashboardColumns(newLayout);
        },

        /**
         * Returns the current column layout
         *
         * @return {String}
         */
        getColumnLayout: function() {
            return dashboardLayout.config.layout;
        },

        /**
         * Return the current dashboard name
         *
         * @return {String}
         */
        getDashboardName: function() {
            return dashboardName;
        },

        /**
         * Sets a new name for the current dashboard
         *
         * @param {String} newName
         */
        setDashboardName: function(newName) {
            dashboardName    = newName;
            dashboardChanged = true;
            saveLayout();
        },

        /**
         * Adds a new widget to the dashboard
         *
         * @param {String}  uniqueId
         * @param {int}     columnNumber
         * @param {object}  widgetParameters
         * @param {boolean} addWidgetOnTop
         * @param {boolean} isHidden
         */
        addWidget: function(uniqueId, columnNumber, widgetParameters, addWidgetOnTop, isHidden) {
            addWidgetTemplate(uniqueId, columnNumber, widgetParameters, addWidgetOnTop, isHidden);
            reloadWidget(uniqueId);
            saveLayout();
        },

        /**
         * Resets the current layout to the defaults
         */
        resetLayout: function()
        {
            var ajaxRequest =
            {
                type: 'POST',
                url: 'index.php?module=Dashboard&action=resetLayout&token_auth='+piwik.token_auth,
                dataType: 'html',
                async: false,
                error: piwikHelper.ajaxHandleError,
                success: function() { methods.loadDashboard.apply(this, [dashboardId])},
                data: { "idDashboard": dashboardId, "idSite": piwik.idSite }
            };
            piwikHelper.showAjaxLoading();
            $.ajax(ajaxRequest);
        },

        /**
         * Removes the current dashboard
         */
        removeDashboard: function() {
            removeDashboard();
        },

        /**
         * Saves the current layout aus new default widget layout
         */
        saveLayoutAsDefaultWidgetLayout: function() {
            saveLayout('saveLayoutAsDefault');
        },

        /**
         * Returns if the current loaded dashboard is the default dashboard
         */
        isDefaultDashboard: function() {
            return (dashboardId == 1);
        }
    };

    /**
     * Generates the dashboard out of the given layout
     *
     * @param {object|string} layout
     */
    function generateLayout(layout) {

        dashboardLayout = parseLayout(layout);
        piwikHelper.hideAjaxLoading();
        adjustDashboardColumns(dashboardLayout.config.layout);

        var dashboardContainsWidgets = false;
        for (var column=0; column < dashboardLayout.columns.length; column++) {
            for (var i in dashboardLayout.columns[column]) {
                var widget = dashboardLayout.columns[column][i];
                dashboardContainsWidgets = true;
                addWidgetTemplate(widget.uniqueId, column+1, widget.parameters, false, widget.isHidden)
            }
        }

        if (!dashboardContainsWidgets) {
            $(dashboardElement).trigger('dashboardempty');
        }

        makeWidgetsSortable();
    }

    /**
     * Fetches the layout for the currently set dashboard id
     * and passes the response to given callback function
     *
     * @param {function} callback
     */
    function fetchLayout(callback)
    {
        piwikHelper.abortQueueAjax();
        var ajaxRequest =
        {
            type: 'GET',
            url: 'index.php?module=Dashboard&action=getDashboardLayout',
            dataType: 'json',
            async: true,
            error: piwikHelper.ajaxHandleError,
            success: callback,
            data: {
                idDashboard: dashboardId,
                token_auth: piwik.token_auth,
                idSite: piwik.idSite
            }
        };
        piwikHelper.queueAjaxRequest($.ajax(ajaxRequest));
    }

    /**
     * Adjust the dashboard columns to fit the new layout
     * removes or adds new columns if needed and sets the column sizes.
     *
     * @param {String} layout new layout in format xx-xx-xx
     * @return {void}
     */
    function adjustDashboardColumns(layout)
    {
        var columnWidth = layout.split('-');
        var columnCount = columnWidth.length;

        var currentCount = $('.col', dashboardElement).length;

        if (currentCount < columnCount) {
            $('.menuClear', dashboardElement).remove();
            for (var i=currentCount;i<columnCount;i++) {
                if (dashboardLayout.columns.length < i) {
                    dashboardLayout.columns.push({});
                }
                $(dashboardElement).append('<div class="col"> </div>');
            }
            $(dashboardElement).append('<div class="menuClear"> </div>');
        } else if (currentCount > columnCount) {
            for (var i=columnCount;i<currentCount;i++) {
                if(dashboardLayout.columns.length >= i) {
                    dashboardLayout.columns.pop();
                }
                $('.col:last').remove();
            }
        }

        for (var i=0; i < columnCount; i++) {
            $('.col', dashboardElement)[i].className = 'col width-'+columnWidth[i];
        }

        makeWidgetsSortable();

        // if dashboard column count is changed (not on initial load)
        if(currentCount > 0 && dashboardLayout.config.layout != layout) {
            dashboardChanged                 = true;
            dashboardLayout.config.layout = layout;
            saveLayout();
        }

        // reload all widgets containing a graph to make them display correct
        $('.widget:has(".piwik-graph")').each(function(id, elem){
            reloadWidget($(elem).attr('id'));
        });
    }

    /**
     * Returns the given layout as an layout object
     * Used to parse old layout format into the new syntax
     *
     * @param {object}  layout  layout object or string
     * @return {object}
     */
    function parseLayout(layout) {

        // Handle layout array used in piwik before 1.7
        // column count was always 3, so use layout 33-33-33 as default
        if ($.isArray(layout)) {
            layout = {
                    config: {layout: '33-33-33'},
                    columns: layout
            };
        }

        if (!layout.config.layout) {
            layout.config.layout = '33-33-33';
        }

        return layout;
    }

    /**
     * Reloads the widget with the given uniqueId
     *
     * @param {String} uniqueId
     */
    function reloadWidget(uniqueId) {
        $('[widgetId='+uniqueId+']', dashboardElement).dashboardWidget('reload');
    }

    /**
     * Adds an empty widget template to the dashboard in the given column
     * @param {String}    uniqueId
     * @param {int}       columnNumber
     * @param {object}    widgetParameters
     * @param {boolean}   addWidgetOnTop
     * @param {boolean}   isHidden
     */
    function addWidgetTemplate(uniqueId, columnNumber, widgetParameters, addWidgetOnTop, isHidden) {
        if (!columnNumber) {
            columnNumber = 1;
        }

        // do not try to add widget if given columnnumber is to high
        if(columnNumber > $('.col', dashboardElement).length) {
            return;
        }

        var widgetContent = '<div class="sortable" widgetId="'+uniqueId+'"></div>';

        if (addWidgetOnTop) {
            $('.col::nth-child('+columnNumber+')', dashboardElement).prepend(widgetContent);
        } else {
            $('.col::nth-child('+columnNumber+')', dashboardElement).append(widgetContent);
        }

        $('[widgetId='+uniqueId+']', dashboardElement).dashboardWidget({
            uniqueId: uniqueId,
            widgetParameters: widgetParameters,
            onChange: function() {
                saveLayout();
            },
            isHidden: isHidden
        });
    }

    /**
     * Make all widgets on the dashboard sortable
     */
    function makeWidgetsSortable() {
        function onStart(event, ui) {
            if (!jQuery.support.noCloneEvent) {
                $('object', this).hide();
            }
        }

        function onStop(event, ui) {
            $('object', this).show();
            $('.widgetHover', this).removeClass('widgetHover');
            $('.widgetTopHover', this).removeClass('widgetTopHover');
            $('.button#close, .button#maximise', this).hide();
            if($('.widget:has(".piwik-graph")', ui.item).length) {
                reloadWidget($('.widget', ui.item).attr('id'));
            }
            saveLayout();
        }

        //launch 'sortable' property on every dashboard widgets
        $('div.col', dashboardElement)
                    .sortable('destroy')
                    .sortable({
                        items: 'div.sortable',
                        opacity: 0.6,
                        forceHelperSize: true,
                        forcePlaceholderSize: true,
                        placeholder: 'hover',
                        handle: '.widgetTop',
                        helper: 'clone',
                        start: onStart,
                        stop: onStop,
                        connectWith: 'div.col'
                    });
    }

    /**
     * Builds the menu for choosing between available dashboards
     */
    function buildMenu() {
        var ajaxRequest =
        {
            type: 'POST',
            url: 'index.php?module=Dashboard&action=getAllDashboards&token_auth='+piwik.token_auth,
            dataType: 'json',
            async: true,
            success: function(dashboards) {
                var dashboardMenuList = $('#Dashboard > ul');
                dashboardMenuList.empty();
                if (dashboards.length > 1) {
                    dashboardMenuList.show();
                    for (var i=0; i<dashboards.length; i++) {
                        dashboardMenuList.append('<li id="Dashboard_embeddedIndex_'+dashboards[i].iddashboard+'" class="dashboardMenuItem"><a dashboardId="'+dashboards[i].iddashboard+'">'+dashboards[i].name+'</a></li>');
                        if(dashboards[i].iddashboard == dashboardId) {
                            dashboardName = dashboards[i].name;
                        }
                    }
                    $('li a', dashboardMenuList).each(function(){$(this).css({width:$(this).width()+30, paddingLeft:0, paddingRight:0});});
                    $('#Dashboard_embeddedIndex_'+dashboardId).addClass('sfHover');
                } else {
                    dashboardMenuList.hide();
                }

                $('.dashboardMenuItem').on('click', function() {
                    if (typeof piwikMenu != 'undefined') {
                        piwikMenu.activateMenu('Dashboard', 'embeddedIndex');
                    }
                    $('.dashboardMenuItem').removeClass('sfHover');
                    if ($(dashboardElement).length) {
                        $(dashboardElement).dashboard('loadDashboard', $('a', this).attr('dashboardId'));
                    } else {
                        broadcast.propagateAjax('module=Dashboard&action=embeddedIndex&idDashboard='+$('a', this).attr('dashboardId'));
                    }
                    $(this).addClass('sfHover');
                });
            },
            error: piwikHelper.ajaxHandleError
        };
        piwikHelper.queueAjaxRequest( $.ajax(ajaxRequest) );
    }

    /**
     * Save the current layout in database if it has changed
     * @param {string} action
     */
    function saveLayout(action) {

        var columns = [];

        var columnNumber = 0;
        $('.col').each(function() {
            columns[columnNumber] = new Array;
            var items = $('[widgetId]', this);
            for (var j=0; j<items.size(); j++) {
                columns[columnNumber][j] = $(items[j]).dashboardWidget('getWidgetObject');
                
                // Do not store segment in the dashboard layout
                delete columns[columnNumber][j].parameters.segment;
                
            }
            columnNumber++;
        });

        if (JSON.stringify(dashboardLayout.columns) != JSON.stringify(columns) || dashboardChanged || action) {

            dashboardLayout.columns = JSON.parse(JSON.stringify(columns));
            columns = null;

            if (!action) {
                action = 'saveLayout';
            }

            var ajaxRequest =
            {
                type: 'POST',
                url: 'index.php?module=Dashboard&action='+action+'&token_auth='+piwik.token_auth,
                dataType: 'html',
                async: true,
                success: function() {
                    if(dashboardChanged) {
                        dashboardChanged = false;
                        buildMenu();
                    }
                },
                error: piwikHelper.ajaxHandleError,
                data: {
                    layout: JSON.stringify(dashboardLayout),
                    name: dashboardName,
                    idDashboard: dashboardId
                }
            };
            $.ajax(ajaxRequest);
        }
    }

    /**
     * Removes the current dashboard
     */
    function removeDashboard() {
        if (dashboardId == 1) {
            return; // dashboard with id 1 should never be deleted, as it is the default
        }
        var ajaxRequest =
        {
            type: 'POST',
            url: 'index.php?module=Dashboard&action=removeDashboard&token_auth='+piwik.token_auth,
            dataType: 'html',
            async: false,
            success: function() {
                methods.loadDashboard.apply(this, [1]);
            },
            error: piwikHelper.ajaxHandleError,
            data: {
                idDashboard: dashboardId
            }
        };
        piwikHelper.showAjaxLoading();
        $.ajax(ajaxRequest);
    }

    /**
     * Make plugin methods available
     */
    $.fn.dashboard = function( method ) {
        if ( methods[method] ) {
            return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
            return methods.init.apply( this, arguments );
        } else {
            $.error('Method ' +  method + ' does not exist on jQuery.dashboard');
        }
    }

})( jQuery );