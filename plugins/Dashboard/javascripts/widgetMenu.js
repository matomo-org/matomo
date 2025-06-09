/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function widgetsHelper() {
}

// a Promise for the first call to getAvailableWidgets. this should not be aborted,
// so any code that aborts all ajax requests should make sure this promise is resolved
// first.
widgetsHelper.firstGetAvailableWidgetsCall = null;

/**
 * Returns the available widgets fetched via AJAX (if not already done)
 *
 * @return {object} object containing available widgets
 */
widgetsHelper.getAvailableWidgets = function (callback) {

    function mergeCategoriesAndSubCategories(availableWidgets)
    {
        var categorized = {};

        $.each(availableWidgets, function (index, widget) {
            var category = widget.category.name;

            if (!categorized[category]) {
                categorized[category] = {'-': []};
            }

            var subcategory = '-';
            if (widget.subcategory && widget.subcategory.name) {
                subcategory = widget.subcategory.name;
            }

            if (!categorized[category][subcategory]) {
                categorized[category][subcategory] = [];
            }

            categorized[category][subcategory].push(widget);
        });

        var moved = {};

        $.each(categorized, function (category, widgets) {
            $.each(widgets, function (subcategory, subwidgets) {
                if (!subwidgets.length) {
                  return;
                }

                var categoryToUse = category;
                if (subwidgets.length >= 3 && subcategory !== '-') {
                    categoryToUse = category + ' - ' + subcategory;
                }

                if (!moved[categoryToUse]) {
                    moved[categoryToUse] = [];
                }

                $.each(subwidgets, function (index, widget) {
                    moved[categoryToUse].push(widget);
                });
            });
        });

        return moved;
    }

    var promise = new Promise(function (resolve, reject) {
      if (!widgetsHelper.availableWidgets) {
        var ajaxRequest = new ajaxHelper();
        ajaxRequest._mixinDefaultGetParams = function (params) {
          return params;
        };
        ajaxRequest.addParams({
          module: 'API',
          method: 'API.getWidgetMetadata',
          filter_limit: '-1',
          format: 'JSON',
          deep: '1',
          idSite:  piwik.idSite || broadcast.getValueFromUrl('idSite')
        }, 'get');
        ajaxRequest.setCallback(
          function (data) {
            widgetsHelper.availableWidgets = mergeCategoriesAndSubCategories(data);

            resolve();
          }
        );
        ajaxRequest.setErrorCallback(function (deferred, status) {
          if (status == 'abort' || !deferred || deferred.status < 400 || deferred.status >= 600) {
            return;
          }
          $('#loadingError').show();
          reject();
        });
        ajaxRequest.send();
        return;
      }

      resolve();
    });

    if (!widgetsHelper.firstGetAvailableWidgetsCall) {
      widgetsHelper.firstGetAvailableWidgetsCall = promise;
    }

    promise.then(function () {
      if (callback) {
        callback(widgetsHelper.availableWidgets);
      }
    });
};

/**
 * Clear the current collection of availableWidgets so that getAvailableWidgets() will load a fresh
 * collection from the API. This is useful when we added/deleted a report and need a refresh.
 */
widgetsHelper.clearAvailableWidgets = function () {
    delete widgetsHelper.availableWidgets;
};

/**
 * Determines the complete widget object by its unique id and sends it to callback method
 *
 * @param {string} uniqueId
 * @param {function} callback
 */
widgetsHelper.getWidgetObjectFromUniqueId = function (uniqueId, callback) {
    widgetsHelper.getAvailableWidgets(function(widgets){
        for (var widgetCategory in widgets) {
            var widgetInCategory = widgets[widgetCategory];
            for (var i in widgetInCategory) {
                if (widgetInCategory[i]["uniqueId"] == uniqueId) {
                    callback(widgetInCategory[i]);
                    return;
                }
            }
        }
        callback(false);
    });
};

/**
 * Determines the name of a widget by its unique id and sends it to the callback
 *
 * @param {string} uniqueId  unique id of the widget
 * @param {function} callback
 * @return {string}
 */
widgetsHelper.getWidgetNameFromUniqueId = function (uniqueId, callback) {
    this.getWidgetObjectFromUniqueId(uniqueId, function(widget) {
        if (widget == false) {
            callback(false);
        }
        callback(widget["name"]);
    });
};

/**
 * Sends and ajax request to query for the widgets html
 *
 * @param {string} widgetUniqueId             unique id of the widget
 * @param {object} widgetParameters           parameters to be used for loading the widget
 * @param {function} onWidgetLoadedCallback   callback to be executed after widget is loaded
 * @return {object}
 */
widgetsHelper.loadWidgetAjax = function (widgetUniqueId, widgetParameters, onWidgetLoadedCallback, onWidgetErrorCallback) {
    var disableLink = broadcast.getValueFromUrl('disableLink');
    widgetParameters['disableLink'] = disableLink.length || $('body#standalone').length;

    widgetParameters['widget'] = 1;

    var ajaxRequest = new ajaxHelper();
    ajaxRequest.addParams(widgetParameters, 'get');
    ajaxRequest.setCallback(onWidgetLoadedCallback);
    if (onWidgetErrorCallback) {
        ajaxRequest.setErrorCallback(onWidgetErrorCallback);
    }
    ajaxRequest.setFormat('html');
    ajaxRequest.send();
    return ajaxRequest;
};

(function ($, require) {
    var exports = require('piwik/UI/Dashboard');

    /**
     * Singleton instance that creates widget elements. Normally not needed even
     * when embedding/re-using dashboard widgets, but it can be useful when creating
     * elements with the same look and feel as dashboard widgets, but different
     * behavior (such as the widget preview in the dashboard manager control).
     *
     * @constructor
     */
    var WidgetFactory = function () {
        // empty
    };

    /**
     * Creates an HTML element for displaying a widget.
     *
     * @param {string} uniqueId     unique id of the widget
     * @param {string} widgetName   name of the widget
     * @return {Element} the empty widget
     */
    WidgetFactory.prototype.make = function (uniqueId, widgetName) {
        var $result = this.getWidgetTemplate().clone();
        $result.attr('id', uniqueId).find('.widgetName').append(widgetName);
        return $result;
    };

    /**
     * Returns the base widget template element. The template is stored in the
     * element with id == 'widgetTemplate'.
     *
     * @return {Element} the widget template
     */
    WidgetFactory.prototype.getWidgetTemplate = function () {
        if (!this.widgetTemplate) {
            this.widgetTemplate = $('#widgetTemplate').find('>.widget').detach();
        }
        return this.widgetTemplate;
    };

    exports.WidgetFactory = new WidgetFactory();
})(jQuery, require);

/**
 * widgetPreview jQuery Extension
 *
 * Converts an dom element to a widget preview
 * Widget preview contains an categorylist, widgetlist and a preview
 */
(function ($) {
    $.extend({
        widgetPreview: new function () {

            /**
             * Default settings for widgetPreview
             * @type {object}
             */
            var defaultSettings = {
                /**
                 * handler called after a widget preview is loaded in preview element
                 * @type {function}
                 */
                onPreviewLoaded: function () {},
                /**
                 * handler called on click on element in widgetlist or widget header
                 * @type {function}
                 */
                onSelect: function () {},
                /**
                 * callback used to determine if a widget is available or not
                 * unavailable widgets aren't chooseable in widgetlist
                 * @type {function}
                 */
                isWidgetAvailable: function (widgetUniqueId) { return true; },
                /**
                 * should the lists and preview be reset on widget selection?
                 * @type {boolean}
                 */
                resetOnSelect: false,
                /**
                 * css classes for various elements
                 * @type {string}
                 */
                baseClass: 'widgetpreview-base',
                categorylistClass: 'widgetpreview-categorylist',
                widgetlistClass: 'widgetpreview-widgetlist',
                widgetpreviewClass: 'widgetpreview-preview',
                choosenClass: 'widgetpreview-choosen',
                unavailableClass: 'widgetpreview-unavailable'
            };

            /**
             * Returns the div to show category list in
             * - if element doesn't exist it will be created and added
             * - if element already exist it's content will be removed
             *
             * @return {$} category list element
             */
            function createWidgetCategoryList(widgetPreview, availableWidgets) {

                var settings = widgetPreview.settings;

                if (!$('.' + settings.categorylistClass, widgetPreview).length) {
                    $(widgetPreview).append('<ul class="' + settings.categorylistClass + '"></ul>');
                } else {
                    $('.' + settings.categorylistClass, widgetPreview).empty();
                }

                for (var widgetCategory in availableWidgets) {
                    $('.' + settings.categorylistClass, widgetPreview).append($('<li>').text(widgetCategory));
                }

                return $('.' + settings.categorylistClass, widgetPreview);
            }

            /**
             * Returns the div to show widget list in
             * - if element doesn't exist it will be created and added
             * - if element already exist it's content will be removed
             *
             * @return {$} widget list element
             */
            function createWidgetList(widgetPreview) {
                var settings = widgetPreview.settings;

                if (!$('.' + settings.widgetlistClass, widgetPreview).length) {
                    $(widgetPreview).append('<ul class="' + settings.widgetlistClass + '"></ul>');
                } else {
                    $('.' + settings.widgetlistClass + ' li', widgetPreview).off('mouseover');
                    $('.' + settings.widgetlistClass + ' li', widgetPreview).off('click');
                    $('.' + settings.widgetlistClass, widgetPreview).empty();
                }

                if ($('.' + settings.categorylistClass + ' .' + settings.choosenClass, widgetPreview).length) {
                    var position = $('.' + settings.categorylistClass + ' .' + settings.choosenClass, widgetPreview).position().top -
                        $('.' + settings.categorylistClass, widgetPreview).position().top +
                        ($('.dashboard-manager .addWidget').outerHeight() || 0);

                    if (!$('#content.admin').length) {
                        position += 5; // + padding defined in dashboard view
                    }

                    $('.' + settings.widgetlistClass, widgetPreview).css({
                        top: position,
                        marginBottom: position
                    });
                }

                return $('.' + settings.widgetlistClass, widgetPreview);
            }

            /**
             * Display the given widgets in a widget list
             *
             * @param {object} widgets widgets to be displayed
             * @return {void}
             */
            function showWidgetList(widgets, widgetPreview) {
                var settings = widgetPreview.settings;

                var widgetList = createWidgetList(widgetPreview),
                    widgetPreviewTimer;

                for (var j = 0; j < widgets.length; j++) {
                    var widgetName = widgets[j]["name"];
                    var widgetUniqueId = widgets[j]["uniqueId"];
                    var widgetCategoryId = widgets[j].category ? widgets[j].category.id : null;
                    var widgetClass = '';
                    if (!settings.isWidgetAvailable(widgetUniqueId) && widgetCategoryId !== 'General_KpiMetric') {
                        widgetClass += ' ' + settings.unavailableClass;
                    }

                    widgetName = piwikHelper.escape(piwikHelper.htmlEntities(widgetName));

                    widgetList.append('<li class="' + widgetClass + '" uniqueid="' + widgetUniqueId + '">' + widgetName + '</li>');
                }

                // delay widget preview a few millisconds
                $('li', widgetList).on('mouseenter', function () {
                    var that = this,
                        widgetUniqueId = $(this).attr('uniqueid');
                    clearTimeout(widgetPreview);
                    widgetPreviewTimer = setTimeout(function () {
                        $('li', widgetList).removeClass(settings.choosenClass);
                        $(that).addClass(settings.choosenClass);

                        showPreview(widgetUniqueId, widgetPreview);
                    }, 400);
                });

                // clear timeout after mouse has left
                $('li:not(.' + settings.unavailableClass + ')', widgetList).on('mouseleave', function () {
                    clearTimeout(widgetPreview);
                });

                $('li', widgetList).on('click', function () {
                    if (!$('.widgetLoading', widgetPreview).length) {
                        settings.onSelect($(this).attr('uniqueid'));
                        $(widgetPreview).closest('.dashboard-manager').removeClass('expanded');
                        if (settings.resetOnSelect) {
                            resetWidgetPreview(widgetPreview);
                        }
                    }
                    return false;
                });
            }

            /**
             * Returns the div to show widget preview in
             * - if element doesn't exist it will be created and added
             * - if element already exist it's content will be removed
             *
             * @return {$} preview element
             */
            function createPreviewElement(widgetPreview) {
                var settings = widgetPreview.settings;

                if (!$('.' + settings.widgetpreviewClass, widgetPreview).length) {
                    $(widgetPreview).append('<div class="' + settings.widgetpreviewClass + '"></div>');
                } else {
                    $('.' + settings.widgetpreviewClass + ' .widgetTop', widgetPreview).off('click');
                    $('.' + settings.widgetpreviewClass, widgetPreview).empty();
                }

                return $('.' + settings.widgetpreviewClass, widgetPreview);
            }

            /**
             * Show widget with the given uniqueId in preview
             *
             * @param {string} widgetUniqueId unique id of widget to display
             * @param          widgetPreview
             * @return {void}
             */
            function showPreview(widgetUniqueId, widgetPreview) {
                // do not reload id widget already displayed
                if ($('[id="' + widgetUniqueId + '"]', widgetPreview).length) return;

                var settings = widgetPreview.settings;

                var previewElement = createPreviewElement(widgetPreview);

                widgetsHelper.getWidgetObjectFromUniqueId(widgetUniqueId, function(widget) {
                    var widgetParameters = widget['parameters'];

                    var emptyWidgetHtml = require('piwik/UI/Dashboard').WidgetFactory.make(
                        widgetUniqueId,
                        $('<span/>')
                            .attr('title', _pk_translate("Dashboard_AddPreviewedWidget"))
                            .text(_pk_translate('Dashboard_WidgetPreview'))
                    );
                    previewElement.html(emptyWidgetHtml);

                    var onWidgetLoadedCallback = function (response) {
                        var widgetElement = $(document.getElementById(widgetUniqueId));
                        // document.getElementById needed for widgets with uniqueid like widgetOpens+Contact+Form
                        $('.widgetContent', widgetElement).html($(response));
                        piwikHelper.compileVueEntryComponents($('.widgetContent', widgetElement));
                        $('.widgetContent', widgetElement).trigger('widget:create');
                        settings.onPreviewLoaded(widgetUniqueId, widgetElement);
                        $('.' + settings.widgetpreviewClass + ' .widgetTop', widgetPreview).on('click', function () {
                            settings.onSelect(widgetUniqueId);
                            $(widgetPreview).closest('.dashboard-manager').removeClass('expanded');
                            if (settings.resetOnSelect) {
                                resetWidgetPreview(widgetPreview);
                            }
                            return false;
                        });
                    };

                    // abort previous sent request
                    if (widgetPreview.widgetAjaxRequest) {
                        widgetPreview.widgetAjaxRequest.abort();
                    }

                    widgetPreview.widgetAjaxRequest = widgetsHelper.loadWidgetAjax(widgetUniqueId, widgetParameters, onWidgetLoadedCallback);
                });
            }

            /**
             * Reset function
             *
             * @return {void}
             */
            function resetWidgetPreview(widgetPreview) {
                var settings = widgetPreview.settings;

                $('.' + settings.categorylistClass + ' li', widgetPreview).removeClass(settings.choosenClass);
                createWidgetList(widgetPreview);
                createPreviewElement(widgetPreview);
            }

            /**
             * Constructor
             *
             * @param {object} userSettings Settings to be used
             * @return {void}
             */
            this.construct = function (userSettings) {

                if (userSettings == 'reset') {
                    resetWidgetPreview(this);
                    return;
                }

                this.widgetAjaxRequest = null;

                $(this).addClass('widgetpreview-base');

                this.settings = jQuery.extend({}, defaultSettings, userSettings);

                // set onSelect callback
                if (typeof this.settings.onSelect == 'function') {
                    this.onSelect = this.settings.onSelect;
                }

                // set onPreviewLoaded callback
                if (typeof this.settings.onPreviewLoaded == 'function') {
                    this.onPreviewLoaded = this.settings.onPreviewLoaded;
                }

                var self = this;
                widgetsHelper.getAvailableWidgets(function (availableWidgets) {

                    var categoryList = createWidgetCategoryList(self, availableWidgets);

                    $('li', categoryList).on('mouseover', function () {
                        var category = $(this).text();
                        var widgets = availableWidgets[category];
                        $('li', categoryList).removeClass(self.settings.choosenClass);
                        $(this).addClass(self.settings.choosenClass);
                        showWidgetList(widgets, self);
                        createPreviewElement(self); // empty preview
                    });
                });

            };
        }
    });

    /**
     * Makes widgetPreview available with $().widgetPreview()
     */
    $.fn.extend({
        widgetPreview: $.widgetPreview.construct
    })
})(jQuery);
