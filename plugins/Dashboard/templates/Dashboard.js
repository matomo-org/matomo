/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function( $ ){

    var dashboardLayout     = {};
    var idDashboard         = 1;
    var dashboardElement    = null;

    /**
     * public methods of dashboard plugin
     */
    var methods = {
        
        /**
         * creates a dashboard object
         * 
         * @param object  options
         */
        init: function(options) {

            dashboardElement = this;

            if(options.idDashboard) {
                idDashboard = options.idDashboard;
            }

            methods.loadDashboard.apply(this, [idDashboard]);
            
            return this;
        },

        destroy: function() {
            $(dashboardElement).remove();
            var widgets = $('[widgetId]');
            for(var i=0; i < widgets.length; i++) {
                $(widgets[i]).dashboardWidget('destroy');
            }
        },

        /**
         * Load dashboard with the given id
         * 
         * @param int dashboardIdToLoad
         */
        loadDashboard: function(dashboardIdToLoad) {

            $(dashboardElement).empty();
            idDashboard = dashboardIdToLoad;
            fetchLayout(generateLayout);
            
            return this;
        },

        setColumnLayout: function(newLayout) {
            adjustDashboardColumns(newLayout);
        },

        getColumnLayout: function() {
            return dashboardLayout.config.layout;
        },

        addWidget: function(uniqueId, columnNumber, widgetParameters, addWidgetOnTop, isHidden) {
            addWidgetTemplate(uniqueId, columnNumber, widgetParameters, addWidgetOnTop, isHidden);
            reloadWidget(uniqueId);
        },

        resetLayout: function()
        {
            var ajaxRequest =
            {
                type: 'POST',
                url: 'index.php?module=Dashboard&action=resetLayout&token_auth='+piwik.token_auth,
                dataType: 'html',
                async: false,
                error: piwikHelper.ajaxHandleError,
                success: function() { window.location.reload(); },
                data: { "idDashboard": this.idDashboard, "idSite": piwik.idSite }
            };
            $.ajax(ajaxRequest);
            piwikHelper.showAjaxLoading();
        }
    };

    /**
     * Generates the dashboard out of the given layout
     *
     * @private
     * @param layout
     */
    function generateLayout(layout) {
        
        dashboardLayout = parseLayout(layout);

        adjustDashboardColumns(dashboardLayout.config.layout);

        for(var column=0; column < dashboardLayout.columns.length; column++) {
            for(var i in dashboardLayout.columns[column]) {
                var widget = dashboardLayout.columns[column][i];
                addWidgetTemplate(widget.uniqueId, column+1, widget.parameters, false, widget.isHidden)
            }
        }

        makeWidgetsSortable();
    };

    /**
     * Fetches the layout for the current set dashboard id
     *
     * @param callback
     */
    function fetchLayout(callback) {
        var ajaxRequest =
        {
            type: 'GET',
            url: 'index.php?module=Dashboard&action=getDashboardLayout',
            dataType: 'json',
            async: true,
            error: piwikHelper.ajaxHandleError,
            success: callback,
            data: {
                idDashboard: idDashboard,
                token_auth: piwik.token_auth,
                idSite: piwik.idSite
            }
        };
        $.ajax(ajaxRequest);
    }
    
    /**
     * Adjust the dashboard columns to fit the new layout
     * removes or adds new columns if needed and sets the column sizes.
     * 
     * @param layout new layout in format xx-xx-xx
     * @return void
     */
    function adjustDashboardColumns(layout)
    {
        var columnWidth = layout.split('-');
        var columnCount = columnWidth.length;
        
        var currentCount = $('.col', dashboardElement).length;

        if(currentCount < columnCount) {
            $('.menuClear', dashboardElement).remove();
            for(var i=currentCount;i<columnCount;i++) {
                if(dashboardLayout.columns.length < i) {
                    dashboardLayout.columns.push({});
                }
                $(dashboardElement).append('<div class="col"> </div>');
            }
            $(dashboardElement).append('<div class="menuClear"> </div>');
        } else if(currentCount > columnCount) {
            for(var i=columnCount;i<currentCount;i++) {
                if(dashboardLayout.columns.length >= i) {
                    dashboardLayout.columns.pop();
                }
                $('.col:last').remove();
            }
        }
        
        for(var i=0; i < columnCount; i++) {
            $('.col', dashboardElement)[i].className = 'col width-'+columnWidth[i];
        }
        
        makeWidgetsSortable();
        
        // if dashboard column count is changed (not on initial load)
        if(currentCount > 0 && dashboardLayout.config.layout != layout) {
            dashboardLayout.config.layout = layout;
            saveLayout();
        }
        
        // reload all widgets containing a graph to make them display correct
        $('.widget:has(".piwik-graph")').each(function(id, elem){
            reloadWidget($(elem).attr('id'));
        });
    };

    /**
     * Returns the given layout as an layout object
     * Used to parse old layout format into the new syntax
     * 
     * @param layout  layout object or string
     * @return object
     */
    function parseLayout(layout) {

        // Handle old dashboard layout format used in piwik before 0.2.33
        // A string that looks like 'Actions.getActions~Actions.getDownloads|UserCountry.getCountry|Referers.getSearchEngines';
        // '|' separate columns
        // '~' separate widgets
        // '.' separate plugin name from action name
        if(typeof layout == 'string') {
            var newLayout = {};
            var columns = layout.split('|');
            for(var columnNumber=0; columnNumber<columns.length; columnNumber++) {
                if(columns[columnNumber].length == 0) {
                    continue;
                }
                var widgets = columns[columnNumber].split('~');
                newLayout[columnNumber] = {};
                for(var j=0; j<widgets.length; j++) {
                    var wid = widgets[j].split('.');
                    var uniqueId = 'widget'+wid[0]+wid[1];
                    newLayout[columnNumber][j] = {
                        uniqueId: uniqueId,
                        parameters: {
                            module: wid[0],
                            action: wid[1]
                        }
                    };
                }
            }
            layout = newLayout;
        }
        
        // Handle layout array used in piwik before 1.7
        // column count was always 3, so use layout 33-33-33 as default
        if($.isArray(layout)) {
            layout = {
                    config: {layout: '33-33-33'},
                    columns: layout
            };
        }

        if(!layout.config.layout) {
            layout.config.layout = '33-33-33';
        }

        return layout;
    };
    
    /**
     * Reloads the widget with the given uniqueId
     *
     * @param uniqueId
     */
    function reloadWidget(uniqueId) {
        $('[widgetId='+uniqueId+']', dashboardElement).dashboardWidget('reload');
    };

    /**
     * Adds an empty widget template to the dashboard in the given column
     * @param uniqueId
     * @param columnNumber
     * @param widgetParameters
     * @param addWidgetOnTop
     * @param isHidden
     */
    function addWidgetTemplate(uniqueId, columnNumber, widgetParameters, addWidgetOnTop, isHidden) {
        if(!columnNumber) {
            columnNumber = 1;
        }

        // do not try to add widget if given columnnumber is to high
        if(columnNumber > $('.col', dashboardElement).length) {
            return;
        }

        var widgetContent = '<div class="sortable" widgetId="'+uniqueId+'"></div>';

        if(addWidgetOnTop) {
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
            if(!jQuery.support.noCloneEvent) {
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
    };
    
    /**
     * Save the current layout in database if it has changed
     */
    function saveLayout() {

        var columns = [];

        var columnNumber = 0;
        $('.col').each(function() {
            columns[columnNumber] = new Array;
            var items = $('[widgetId]', this);
            for(var j=0; j<items.size(); j++) {
                columns[columnNumber][j] = $(items[j]).dashboardWidget('getWidgetObject');
            }
            columnNumber++;
        });

        if(JSON.stringify(dashboardLayout.columns) != JSON.stringify(columns)) {

            dashboardLayout.columns = JSON.parse(JSON.stringify(columns));
            delete columns;

            var ajaxRequest =
            {
                type: 'POST',
                url: 'index.php?module=Dashboard&action=saveLayout&token_auth='+piwik.token_auth,
                dataType: 'html',
                async: true,
                success: function() {
                },
                error: piwikHelper.ajaxHandleError,
                data: {
                    layout: JSON.stringify(dashboardLayout),
                    idDashboard: idDashboard
                }
            };
            $.ajax(ajaxRequest);
        }
    };
    
    /**
     * Make plugin methods available
     */
    $.fn.dashboard = function( method ) {
        if ( methods[method] ) {
            return methods[method].apply( this, Array.prototype.slice.call( arguments, 1 ));
        } else if ( typeof method === 'object' || ! method ) {
            return methods.init.apply( this, arguments );
        } else {
            $.error( 'Method ' +  method + ' does not exist on jQuery.dashboard' );
        }
    };

})( jQuery );