/*!
 * Piwik - Web Analytics
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function widgetsHelper() {
}

/**
 * Returns the available widgets fetched via AJAX (if not already done)
 *
 * @return {object} object containing available widgets
 */
widgetsHelper.getAvailableWidgets = function () {
    if (!widgetsHelper.availableWidgets) {
        var ajaxRequest = new ajaxHelper();
        ajaxRequest.addParams({
            module: 'Dashboard',
            action: 'getAvailableWidgets'
        }, 'get');
        ajaxRequest.setCallback(
            function (data) {
                widgetsHelper.availableWidgets = data;
            }
        );
        ajaxRequest.send(true);
    }

    return widgetsHelper.availableWidgets;
};

/**
 * Returns the complete widget object by its unique id
 *
 * @param {string} uniqueId
 * @return {object} widget object
 */
widgetsHelper.getWidgetObjectFromUniqueId = function (uniqueId) {
    var widgets = widgetsHelper.getAvailableWidgets();
    for (var widgetCategory in widgets) {
        var widgetInCategory = widgets[widgetCategory];
        for (var i in widgetInCategory) {
            if (widgetInCategory[i]["uniqueId"] == uniqueId) {
                return widgetInCategory[i];
            }
        }
    }
    return false;
};

/**
 * Returns the name of a widget by its unique id
 *
 * @param {string} uniqueId  unique id of the widget
 * @return {string}
 */
widgetsHelper.getWidgetNameFromUniqueId = function (uniqueId) {
    var widget = this.getWidgetObjectFromUniqueId(uniqueId);
    if (widget == false) {
        return false;
    }
    return widget["name"];
};

/**
 * Sends and ajax request to query for the widgets html
 *
 * @param {string} widgetUniqueId             unique id of the widget
 * @param {object} widgetParameters           parameters to be used for loading the widget
 * @param {function} onWidgetLoadedCallback   callback to be executed after widget is loaded
 * @return {object}
 */
widgetsHelper.loadWidgetAjax = function (widgetUniqueId, widgetParameters, onWidgetLoadedCallback) {
    var disableLink = broadcast.getValueFromUrl('disableLink');
    if (disableLink.length) {
        widgetParameters['disableLink'] = disableLink;
    }

    widgetParameters['widget'] = 1;

    var ajaxRequest = new ajaxHelper();
    ajaxRequest.addParams(widgetParameters, 'get');
    ajaxRequest.setCallback(onWidgetLoadedCallback);
    ajaxRequest.setFormat('html');
    ajaxRequest.send(false);
    return ajaxRequest;
};

/**
 * Returns the base html use for displaying a widget
 *
 * @param {string} uniqueId     unique id of the widget
 * @param {string} widgetName   name of the widget
 * @return {string} html for empty widget
 */
widgetsHelper.getEmptyWidgetHtml = function (uniqueId, widgetName) {
    return '<div id="' + uniqueId + '" class="widget">' +
        '<div class="widgetTop">' +
        '<div class="button" id="close">' +
        '<img src="plugins/Zeitgeist/images/close.png" title="' + _pk_translate('General_Close') + '" />' +
        '</div>' +
        '<div class="button" id="maximise">' +
        '<img src="plugins/Zeitgeist/images/maximise.png" title="' + _pk_translate('Dashboard_Maximise') + '" />' +
        '</div>' +
        '<div class="button" id="minimise">' +
        '<img src="plugins/Zeitgeist/images/minimise.png" title="' + _pk_translate('Dashboard_Minimise') + '" />' +
        '</div>' +
        '<div class="button" id="refresh">' +
        '<img src="plugins/Zeitgeist/images/refresh.png" title="' + _pk_translate('General_Refresh') + '" />' +
        '</div>' +
        '<div class="widgetName">' + widgetName + '</div>' +
        '</div>' +
        '<div class="widgetContent">' +
        '<div class="widgetLoading">' +
        _pk_translate('Dashboard_LoadingWidget') +
        '</div>' +
        '</div>' +
        '</div>';
};

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
            var settings = {
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

            var availableWidgets, widgetPreview, widgetAjaxRequest = null;

            /**
             * Returns the div to show category list in
             * - if element doesn't exist it will be created and added
             * - if element already exist it's content will be removed
             *
             * @return {$} category list element
             */
            function createWidgetCategoryList() {

                if (!$('.' + settings.categorylistClass, widgetPreview).length) {
                    $(widgetPreview).append('<ul class="' + settings.categorylistClass + '"></ul>');
                } else {
                    $('.' + settings.categorylistClass, widgetPreview).empty();
                }

                for (var widgetCategory in availableWidgets) {

                    $('.' + settings.categorylistClass, widgetPreview).append('<li>' + widgetCategory + '</li>');
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
            function createWidgetList() {

                if (!$('.' + settings.widgetlistClass, widgetPreview).length) {
                    $(widgetPreview).append('<ul class="' + settings.widgetlistClass + '"></ul>');
                } else {
                    $('.' + settings.widgetlistClass + ' li', widgetPreview).off('mouseover');
                    $('.' + settings.widgetlistClass + ' li', widgetPreview).off('click');
                    $('.' + settings.widgetlistClass, widgetPreview).empty();
                }

                if ($('.' + settings.categorylistClass + ' .' + settings.choosenClass, widgetPreview).length) {
                    var position = $('.' + settings.categorylistClass + ' .' + settings.choosenClass, widgetPreview).position().top -
                        $('.' + settings.categorylistClass).position().top;

                    $('.' + settings.widgetlistClass, widgetPreview).css('top', position);
                    $('.' + settings.widgetlistClass, widgetPreview).css('marginBottom', position);
                }

                return $('.' + settings.widgetlistClass, widgetPreview);
            }

            /**
             * Display the given widgets in a widget list
             *
             * @param {object} widgets widgets to be displayed
             * @return {void}
             */
            function showWidgetList(widgets) {

                var widgetList = createWidgetList(),
                    widgetPreviewTimer;

                for (var j = 0; j < widgets.length; j++) {
                    var widgetName = widgets[j]["name"];
                    var widgetUniqueId = widgets[j]["uniqueId"];
                    // var widgetParameters = widgets[j]["parameters"];
                    var widgetClass = '';
                    if (!settings.isWidgetAvailable(widgetUniqueId)) {
                        widgetClass += ' ' + settings.unavailableClass;
                    }

                    widgetList.append('<li class="' + widgetClass + '" uniqueid="' + widgetUniqueId + '">' + widgetName + '</li>');
                }

                // delay widget preview a few millisconds
                $('li:not(.' + settings.unavailableClass + ')', widgetList).on('mouseenter', function () {
                    var that = this,
                        widgetUniqueId = $(this).attr('uniqueid');
                    clearTimeout(widgetPreview);
                    widgetPreviewTimer = setTimeout(function () {
                        $('li', widgetList).removeClass(settings.choosenClass);
                        $(that).addClass(settings.choosenClass);

                        showPreview(widgetUniqueId);
                    }, 400);
                });

                // clear timeout after mouse has left
                $('li:not(.' + settings.unavailableClass + ')', widgetList).on('mouseleave', function () {
                    clearTimeout(widgetPreview);
                });

                $('li:not(.' + settings.unavailableClass + ')', widgetList).on('click', function () {
                    if (!$('.widgetLoading', widgetPreview).length) {
                        settings.onSelect($(this).attr('uniqueid'));
                        if (settings.resetOnSelect) {
                            resetWidgetPreview();
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
            function createPreviewElement() {

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
             * @return {void}
             */
            function showPreview(widgetUniqueId) {
                // do not reload id widget already displayed
                if ($('#' + widgetUniqueId, widgetPreview).length) return;

                var previewElement = createPreviewElement();

                var widget = widgetsHelper.getWidgetObjectFromUniqueId(widgetUniqueId);
                var widgetParameters = widget['parameters'];

                var emptyWidgetHtml = widgetsHelper.getEmptyWidgetHtml(
                    widgetUniqueId,
                    '<div title="' + _pk_translate("Dashboard_AddPreviewedWidget") + '">' +
                        _pk_translate('Dashboard_WidgetPreview') +
                        '</div>'
                );
                previewElement.html(emptyWidgetHtml);

                var onWidgetLoadedCallback = function (response) {
                    var widgetElement = $('#' + widgetUniqueId);
                    $('.widgetContent', widgetElement).html($(response));
                    $('.widgetContent', widgetElement).trigger('widget:create');
                    settings.onPreviewLoaded(widgetUniqueId, widgetElement);
                    $('.' + settings.widgetpreviewClass + ' .widgetTop', widgetPreview).on('click', function () {
                        settings.onSelect(widgetUniqueId);
                        if (settings.resetOnSelect) {
                            resetWidgetPreview();
                        }
                        return false;
                    });
                };

                // abort previous sent request
                if (widgetAjaxRequest) {
                    widgetAjaxRequest.abort();
                }

                widgetAjaxRequest = widgetsHelper.loadWidgetAjax(widgetUniqueId, widgetParameters, onWidgetLoadedCallback);
            }

            /**
             * Reset function
             *
             * @return {void}
             */
            function resetWidgetPreview() {
                $('.' + settings.categorylistClass + ' li', widgetPreview).removeClass(settings.choosenClass);
                createWidgetList();
                createPreviewElement();
            }

            /**
             * Constructor
             *
             * @param {object} userSettings Settings to be used
             * @return {void}
             */
            this.construct = function (userSettings) {

                if (widgetPreview && userSettings == 'reset') {
                    resetWidgetPreview();
                    return;
                }

                widgetPreview = this;

                $(this).addClass('widgetpreview-base');

                settings = jQuery.extend(settings, userSettings);

                // set onSelect callback
                if (typeof settings.onSelect == 'function') {
                    this.onSelect = settings.onSelect;
                }

                // set onPreviewLoaded callback
                if (typeof settings.onPreviewLoaded == 'function') {
                    this.onPreviewLoaded = settings.onPreviewLoaded;
                }

                availableWidgets = widgetsHelper.getAvailableWidgets();

                var categoryList = createWidgetCategoryList();

                $('li', categoryList).on('mouseover', function () {
                    var category = $(this).text();
                    var widgets = availableWidgets[category];
                    $('li', categoryList).removeClass(settings.choosenClass);
                    $(this).addClass(settings.choosenClass);
                    showWidgetList(widgets);
                    createPreviewElement(); // empty preview
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
