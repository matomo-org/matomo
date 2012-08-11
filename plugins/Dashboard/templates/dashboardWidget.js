/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function( $ ){

    $.widget('piwik.dashboardWidget', {

        /**
         * Boolean indicating wether the widget is currently maximised
         * @type {boolean}
         */
        isMaximised:        false,
        /**
         * Unique Id of the widget
         * @type {string}
         */
        uniqueId:           null,
        /**
         * Object holding the widget parameters
         * @type {object}
         */
        widgetParameters:   {},

        /**
         * Options available for initialization
         */
        options: {
            uniqueId: null,
            isHidden: false,
            onChange: null,
            widgetParameters: {}
        },

        /**
         * creates a widget object
         */
        _create: function() {

            if(!this.options.uniqueId) {
                console.error('widgets can\'t be created without an uniqueId');
                return;
            } else {
                this.uniqueId = this.options.uniqueId;
            }

            if(this.options.widgetParameters) {
                this.widgetParameters = this.options.widgetParameters;
            }

            this._createDashboardWidget(this.uniqueId);

            var self = this;
            this.element.on('setParameters.dashboardWidget', function(e, params) { self.setParameters(params); });

            this.reload(true);
        },

        /**
         * Cleanup some events and dialog
         * Called automaticly upon removing the widgets domNode
         */
        destroy: function() {
            if(this.isMaximised) {
                $('[widgetId='+this.uniqueId+']').dialog('destroy');
            }
            $('*', this.element).off('.dashboardWidget'); // unbind all events
            return this;
        },

        /**
         * Returns the data currently set for the widget
         * @return {object}
         */
        getWidgetObject: function() {
            return {
                uniqueId: this.uniqueId,
                parameters: this.widgetParameters,
                isHidden: this.options.isHidden
            };
        },

        /**
         * Show the current widget in an ui.dialog
         */
        maximise: function() {
            this.isMaximised = true;

            var minWidth = this.element.width() < 500 ? 500 : this.element.width();
            var maxWidth = minWidth > 1000 ? minWidth+100 : 1000;

            this.element.css({'minWidth': minWidth+'px', 'maxWidth': maxWidth+'px'});
            $('.button#close, .button#maximise', this.element).hide();
            this.element.before('<div id="'+this.uniqueId+'-placeholder" class="widgetPlaceholder widget"> </div>');
            $('#'+this.uniqueId+'-placeholder').height(this.element.height());
            $('#'+this.uniqueId+'-placeholder').width(this.element.width()-16);

            var self = this;

            this.element.dialog({
                title: '',
                modal: true,
                width: 'auto',
                position: ['center', 'center'],
                resizable: true,
                autoOpen: true,
                close: function(event, ui) {
                    self.isMaximised = false;
                    $('.button#minimise', $(this)).hide()
                    $('body').off('.dashboardWidget');
                    $(this).dialog("destroy");
                    $('#'+self.uniqueId+'-placeholder').replaceWith(this);
                    $(this).removeAttr('style');
                    self.options.onChange();
                    $(this).find('div.piwik-graph').trigger('resizeGraph');
                }
            });
            this.element.find('div.piwik-graph').trigger('resizeGraph');

            var currentWidget = this.element;
            $('body').on('click.dashboardWidget', function(ev) {
                if(ev.target.className == "ui-widget-overlay") {
                    $(currentWidget).dialog("close");
                }
            });
            return this;
        },

        /**
         * Reloads the widgets content with the currently set parameters
         */
        reload: function(hideLoading) {

            var currentWidget = this.element;
            function onWidgetLoadedReplaceElementWithContent(loadedContent)
            {
                $('.widgetContent', currentWidget).html(loadedContent);
                $('.widgetContent', currentWidget).removeClass('loading');
            }

            // Reading segment from hash tag (standard case) or from the URL (when embedding dashboard) 
            var segment = broadcast.getValueFromHash('segment') || broadcast.getValueFromUrl('segment');
            if(segment.length) {
                this.widgetParameters.segment = segment;
            }

            if (!hideLoading) {
                $('.widgetContent', currentWidget).addClass('loading');
            }

            piwikHelper.queueAjaxRequest( $.ajax(widgetsHelper.getLoadWidgetAjaxRequest(this.uniqueId, this.widgetParameters, onWidgetLoadedReplaceElementWithContent)) );

            return this;
        },

        /**
         * Update widget parameters
         *
         * @param {object} parameters
         */
        setParameters: function(parameters) {

            if (!this.isMaximised && (parameters.viewDataTable == 'tableAllColumns' || parameters.viewDataTable == 'tableGoals')) {
                this.maximise();
            }
            for (var name in parameters) {
                this.widgetParameters[name] = parameters[name];
            }
            if (!this.isMaximised) {
                this.options.onChange();
            }

            return this;
        },

        /**
         * Creaates the widget markup for the given uniqueId
         *
         * @param {String} uniqueId
         */
        _createDashboardWidget: function(uniqueId) {

            var widgetName = widgetsHelper.getWidgetNameFromUniqueId(uniqueId);
            if(widgetName == false) {
                widgetName = _pk_translate('Dashboard_WidgetNotFound_js');
            }

            var emptyWidgetContent = widgetsHelper.getEmptyWidgetHtml(uniqueId, widgetName);
            this.element.html(emptyWidgetContent);

            var widgetElement = $('#'+ uniqueId, this.element);
            var self = this;
            widgetElement
                .on( 'mouseenter.dashboardWidget', function() {
                    if(!self.isMaximised) {
                        $(this).addClass('widgetHover');
                        $('.widgetTop', this).addClass('widgetTopHover');
                        $('.button#close, .button#maximise', this).show();
                        if(!$('.widgetContent', this).hasClass('hidden')) {
                            $('.button#minimise, .button#refresh', this).show();
                        }
                    }
                })
                .on( 'mouseleave.dashboardWidget', function() {
                    if(!self.isMaximised) {
                        $(this).removeClass('widgetHover');
                        $('.widgetTop', this).removeClass('widgetTopHover');
                        $('.button#close, .button#maximise, .button#minimise, .button#refresh', this).hide();
                    }
                });

            if(this.options.isHidden) {
                $('.widgetContent', widgetElement).toggleClass('hidden');
            }

            var self = this;
            $('.button#close', widgetElement)
                .on( 'click.dashboardWidget', function(ev){
                    piwikHelper.modalConfirm('#confirm',{yes: function(){
                        self.element.remove();
                        self.options.onChange();
                    }});
                });

            $('.button#maximise', widgetElement)
                .on( 'click.dashboardWidget', function(ev){
                    if($('.widgetContent', $(this).parents('.widget')).hasClass('hidden')) {
                        self.isMaximised = false;
                        self.options.isHidden = false;
                        $('.widgetContent', $(this).parents('.widget')).removeClass('hidden');
                        $('.button#minimise, .button#refresh', $(this).parents('.widget')).show();
                        $(this).parents('.widget').find('div.piwik-graph').trigger('resizeGraph');
                        self.options.onChange();
                    } else {
                        self.maximise();
                    }
                });

            $('.button#minimise', widgetElement)
                .on( 'click.dashboardWidget', function(ev){
                    if(!self.isMaximised) {
                        $('.widgetContent', $(this).parents('.widget')).addClass('hidden');
                        $('.button#minimise, .button#refresh', $(this).parents('.widget')).hide();
                        self.options.isHidden = true;
                        self.options.onChange();
                    } else {
                        self.element.dialog("close");
                    }
                });

            $('.button#refresh', widgetElement)
                .on('click.dashboardWidget', function(ev){
                    self.reload();
                });

            widgetElement.show();
        }
    });

})( jQuery );
