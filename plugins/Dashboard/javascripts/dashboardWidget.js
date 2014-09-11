/*!
 * Piwik - free/libre analytics platform
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
            widgetParameters: {},
            title: null,
            onRemove: null,
            onRefresh: null,
            onMaximise: null,
            onMinimise: null,
            autoMaximiseVisualizations: ['tableAllColumns', 'tableGoals']
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

            if (this.options.onMaximise) {
                this.options.onMaximise(this.element);
            } else {
                this._maximiseImpl();
            }

            $('.widgetContent', this.element).trigger('widget:maximise');
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
            widgetsHelper.loadWidgetAjax(this.uniqueId, params, onWidgetLoadedReplaceElementWithContent, function (deferred, status) {
                if (status == 'abort' || !deferred || deferred.status < 400 || deferred.status >= 600) {
                    return;
                }

                $('.widgetContent', currentWidget).removeClass('loading');
                var errorMessage = _pk_translate('General_ErrorRequest', ['', '']);
                if ($('#loadingError').html()) {
                    errorMessage = $('#loadingError').html();
                }

                $('.widgetContent', currentWidget).html('<div class="widgetLoadingError">' + errorMessage + '</div>');
            });

            return this;
        },

        /**
         * Update widget parameters
         *
         * @param {object} parameters
         */
        setParameters: function (parameters) {
            if (!this.isMaximised
                && this.options.autoMaximiseVisualizations.indexOf(parameters.viewDataTable) !== -1
            ) {
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
         * Get widget parameters
         *
         * @param {object} parameters
         */
        getParameters: function () {
            return $.extend({}, this.widgetParameters);
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

            var title = this.options.title === null ? $('<span/>').text(widgetName) : this.options.title;
            var emptyWidgetContent = require('piwik/UI/Dashboard').WidgetFactory.make(uniqueId, title);
            this.element.html(emptyWidgetContent);

            var widgetElement = $('#' + uniqueId, this.element);
            var self = this;
            widgetElement
                .on('mouseenter.dashboardWidget', function () {
                    if (!self.isMaximised) {
                        $(this).addClass('widgetHover');
                        $('.widgetTop', this).addClass('widgetTopHover');
                    }
                })
                .on('mouseleave.dashboardWidget', function () {
                    if (!self.isMaximised) {
                        $(this).removeClass('widgetHover');
                        $('.widgetTop', this).removeClass('widgetTopHover');
                    }
                });

            if (this.options.isHidden) {
                $('.widgetContent', widgetElement).toggleClass('hidden').closest('.widget').toggleClass('hiddenContent');
            }

            $('.button#close', widgetElement)
                .on('click.dashboardWidget', function (ev) {
                    piwikHelper.modalConfirm('#confirm', {yes: function () {
                        if (self.options.onRemove) {
                            self.options.onRemove(self.element);
                        } else {
                            self.element.remove();
                            self.options.onChange();
                        }
                    }});
                });

            $('.button#maximise', widgetElement)
                .on('click.dashboardWidget', function (ev) {
                    if (self.options.onMaximise) {
                        self.options.onMaximise(self.element);
                    } else {
                        if ($('.widgetContent', $(this).parents('.widget')).hasClass('hidden')) {
                            self.showContent();
                        } else {
                            self.maximise();
                        }
                    }
                });

            $('.button#minimise', widgetElement)
                .on('click.dashboardWidget', function (ev) {
                    if (self.options.onMinimise) {
                        self.options.onMinimise(self.element);
                    } else {
                        if (!self.isMaximised) {
                            self.hideContent();
                        } else {
                            self.element.dialog("close");
                        }
                    }
                });

            $('.button#refresh', widgetElement)
                .on('click.dashboardWidget', function (ev) {
                    if (self.options.onRefresh) {
                        self.options.onRefresh(self.element);
                    } else {
                        self.reload(false, true);
                    }
                });
        },

        /**
         * Hide the widget content. Triggers the onChange event.
         */
        hideContent: function () {
            $('.widgetContent', this.element.find('.widget').addClass('hiddenContent')).addClass('hidden');
            this.options.isHidden = true;
            this.options.onChange();
        },

        /**
         * Show the widget content. Triggers the onChange event.
         */
        showContent: function () {
            this.isMaximised = false;
            this.options.isHidden = false;
            this.element.find('.widget').removeClass('hiddenContent').find('.widgetContent').removeClass('hidden');
            this.element.find('.widget').find('div.piwik-graph').trigger('resizeGraph');
            this.options.onChange();
            $('.widgetContent', this.element).trigger('widget:minimise');
        },

        /**
         * Default maximise behavior. Will create a dialog that is 70% of the document's width,
         * displaying the widget alone.
         */
        _maximiseImpl: function () {
            this.detachWidget();

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
        },

        /**
         * Detaches the widget from the DOM and replaces it with a placeholder element.
         * The placeholder element will have the save dimensions as the widget and will have
         * the widgetPlaceholder CSS class.
         *
         * @return {jQuery} the detached widget
         */
        detachWidget: function () {
            this.element.before('<div id="' + this.uniqueId + '-placeholder" class="widgetPlaceholder widget"> </div>');
            $('#' + this.uniqueId + '-placeholder').height(this.element.height());
            $('#' + this.uniqueId + '-placeholder').width(this.element.width() - 16);

            return this.element.detach();
        }
    });

})(jQuery);