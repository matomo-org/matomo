/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function ($) {

    $.widget('piwik.dashboardWidget', {

        /**
         * Boolean indicating wether the widget is currently maximised
         * @type {Boolean}
         */
        isMaximised: false,
        /**
         * Unique Id of the widget
         * @type {String}
         */
        uniqueId: null,
        /**
         * Object holding the widget parameters
         * @type {Object}
         */
        widgetParameters: {},

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
        _create: function () {

            if (!this.options.uniqueId) {
                piwikHelper.error('widgets can\'t be created without an uniqueId');
                return;
            } else {
                this.uniqueId = this.options.uniqueId;
            }

            if (this.options.widgetParameters) {
                this.widgetParameters = this.options.widgetParameters;
            }

            this._createDashboardWidget(this.uniqueId);

            var self = this;
            this.element.on('setParameters.dashboardWidget', function (e, params) { self.setParameters(params); });

            this.reload(true, true);
        },

        /**
         * Cleanup some events and dialog
         * Called automatically upon removing the widgets domNode
         */
        destroy: function () {
            if (this.isMaximised) {
                $('[widgetId=' + this.uniqueId + ']').dialog('destroy');
            }
            $('*', this.element).off('.dashboardWidget'); // unbind all events
            $('.widgetContent', this.element).trigger('widget:destroy');
            require('piwik/UI').UIControl.cleanupUnusedControls();
            return this;
        },

        /**
         * Returns the data currently set for the widget
         * @return {object}
         */
        getWidgetObject: function () {
            return {
                uniqueId: this.uniqueId,
                parameters: this.widgetParameters,
                isHidden: this.options.isHidden
            };
        },

        /**
         * Show the current widget in an ui.dialog
         */
        maximise: function () {
            this.isMaximised = true;

            $('.button#close, .button#maximise', this.element).hide();
            this.element.before('<div id="' + this.uniqueId + '-placeholder" class="widgetPlaceholder widget"> </div>');
            $('#' + this.uniqueId + '-placeholder').height(this.element.height());
            $('#' + this.uniqueId + '-placeholder').width(this.element.width() - 16);

            var width = Math.floor($('body').width() * 0.7);

            var self = this;
            this.element.dialog({
                title: '',
                modal: true,
                width: width,
                position: ['center', 'center'],
                resizable: true,
                autoOpen: true,
                close: function (event, ui) {
                    self.isMaximised = false;
                    $('.button#minimise, .button#refresh', $(this)).hide();
                    $('body').off('.dashboardWidget');
                    $(this).dialog("destroy");
                    $('#' + self.uniqueId + '-placeholder').replaceWith(this);
                    $(this).removeAttr('style');
                    self.options.onChange();
                    $(this).find('div.piwik-graph').trigger('resizeGraph');
                    $('.widgetContent', self.element).trigger('widget:minimise');
                }
            });
            this.element.find('div.piwik-graph').trigger('resizeGraph');

            var currentWidget = this.element;
            $('body').on('click.dashboardWidget', function (ev) {
                if (/ui-widget-overlay/.test(ev.target.className)) {
                    $(currentWidget).dialog("close");
                }
            });
            $('.widgetContent', currentWidget).trigger('widget:maximise');
            return this;
        },

        /**
         * Reloads the widgets content with the currently set parameters
         */
        reload: function (hideLoading, notJQueryUI, overrideParams) {
            if (!notJQueryUI) {
                piwikHelper.log('widget.reload() was called by jquery.ui, ignoring', arguments.callee.caller);
                return;
            }

            var self = this, currentWidget = this.element;

            function onWidgetLoadedReplaceElementWithContent(loadedContent) {
                $('.widgetContent', currentWidget).html(loadedContent);
                $('.widgetContent', currentWidget).removeClass('loading');
                $('.widgetContent', currentWidget).trigger('widget:create', [self]);
            }

            // Reading segment from hash tag (standard case) or from the URL (when embedding dashboard)
            var segment = broadcast.getValueFromHash('segment') || broadcast.getValueFromUrl('segment');
            if (segment.length) {
                this.widgetParameters.segment = segment;
            }

            if (!hideLoading) {
                $('.widgetContent', currentWidget).addClass('loading');
            }

            var params = $.extend(this.widgetParameters, overrideParams || {});
            widgetsHelper.loadWidgetAjax(this.uniqueId, params, onWidgetLoadedReplaceElementWithContent);

            return this;
        },

        /**
         * Update widget parameters
         *
         * @param {object} parameters
         */
        setParameters: function (parameters) {

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
        _createDashboardWidget: function (uniqueId) {

            var widgetName = widgetsHelper.getWidgetNameFromUniqueId(uniqueId);
            if (!widgetName) {
                widgetName = _pk_translate('Dashboard_WidgetNotFound');
            }

            var emptyWidgetContent = widgetsHelper.getEmptyWidgetHtml(uniqueId, widgetName);
            this.element.html(emptyWidgetContent);

            var widgetElement = $('#' + uniqueId, this.element);
            var self = this;
            widgetElement
                .on('mouseenter.dashboardWidget', function () {
                    if (!self.isMaximised) {
                        $(this).addClass('widgetHover');
                        $('.widgetTop', this).addClass('widgetTopHover');
                        $('.button#close, .button#maximise', this).show();
                        if (!$('.widgetContent', this).hasClass('hidden')) {
                            $('.button#minimise, .button#refresh', this).show();
                        }
                    }
                })
                .on('mouseleave.dashboardWidget', function () {
                    if (!self.isMaximised) {
                        $(this).removeClass('widgetHover');
                        $('.widgetTop', this).removeClass('widgetTopHover');
                        $('.button#close, .button#maximise, .button#minimise, .button#refresh', this).hide();
                    }
                });

            if (this.options.isHidden) {
                $('.widgetContent', widgetElement).toggleClass('hidden');
            }

            $('.button#close', widgetElement)
                .on('click.dashboardWidget', function (ev) {
                    piwikHelper.modalConfirm('#confirm', {yes: function () {
                        self.element.remove();
                        self.options.onChange();
                    }});
                });

            $('.button#maximise', widgetElement)
                .on('click.dashboardWidget', function (ev) {
                    if ($('.widgetContent', $(this).parents('.widget')).hasClass('hidden')) {
                        self.isMaximised = false;
                        self.options.isHidden = false;
                        $('.widgetContent', $(this).parents('.widget')).removeClass('hidden');
                        $('.button#minimise, .button#refresh', $(this).parents('.widget')).show();
                        $(this).parents('.widget').find('div.piwik-graph').trigger('resizeGraph');
                        self.options.onChange();
                        $('.widgetContent', widgetElement).trigger('widget:minimise');
                    } else {
                        self.maximise();
                    }
                });

            $('.button#minimise', widgetElement)
                .on('click.dashboardWidget', function (ev) {
                    if (!self.isMaximised) {
                        $('.widgetContent', $(this).parents('.widget')).addClass('hidden');
                        $('.button#minimise, .button#refresh', $(this).parents('.widget')).hide();
                        self.options.isHidden = true;
                        self.options.onChange();
                    } else {
                        self.element.dialog("close");
                    }
                });

            $('.button#refresh', widgetElement)
                .on('click.dashboardWidget', function (ev) {
                    self.reload(false, true);
                });

            widgetElement.show();
        }

    });

})(jQuery);
