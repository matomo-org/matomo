/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

/**
 * Usage:
 * <div piwik-widget-preview>
 */
(function () {
    angular.module('piwikApp').directive('piwikWidgetPreview', piwikWidgetPreview);

    piwikWidgetPreview.$inject = ['piwik', '$window'];

    function piwikWidgetPreview(piwik, $window){

        function getEmbedUrl(parameters, exportFormat) {
            var copyParameters = {};
            for (var variableName in parameters) {
                copyParameters[variableName] = parameters[variableName];
            }
            copyParameters['moduleToWidgetize'] = parameters['module'];
            copyParameters['actionToWidgetize'] = parameters['action'];
            delete copyParameters['action'];
            delete copyParameters['module'];
            var sourceUrl;
            sourceUrl = $window.location.protocol + '//' + $window.location.hostname + ($window.location.port == '' ? '' : (':' + $window.location.port)) + $window.location.pathname + '?';
            sourceUrl += "module=Widgetize" +
                "&action=" + exportFormat +
                "&" + piwik.helper.getQueryStringFromParameters(copyParameters) +
                "&idSite=" + piwik.idSite +
                "&period=" + piwik.period +
                "&date=" + piwik.broadcast.getValueFromUrl('date') +
                "&disableLink=1&widget=1";
            return sourceUrl;
        }

        return {
            restrict: 'A',
            controller: function () {

                var self = this;

                this.getInputFormWithHtml = function (inputId, htmlEmbed) {
                    return '<pre piwik-select-on-focus readonly="true"  id="' + inputId + '">' + this.htmlentities(htmlEmbed) + '</pre>';
                };

                this.htmlentities = function (s) {
                    return piwik.helper.escape(piwik.helper.htmlEntities(s));
                };

                this.callbackAddExportButtonsUnderWidget = function (widgetUniqueId, loadedWidgetElement) {
                    var widget = widgetsHelper.getWidgetObjectFromUniqueId(widgetUniqueId);
                    var widgetParameters = widget['parameters'];

                    var exportButtonsElement = $('<span id="exportButtons">');

                    var urlIframe = getEmbedUrl(widgetParameters, "iframe");
                    // We first build the HTML code that will load the widget in an IFRAME
                    var widgetIframeHtml = '<div id="widgetIframe">' +
                        '<iframe width="100%" height="350" src="' + urlIframe + '" scrolling="no" frameborder="0" marginheight="0" marginwidth="0">' +
                        '</iframe>' +
                        '</div>';

                    // Add the input field containing the widget in an Iframe
                    $(exportButtonsElement).append(
                        '<div id="embedThisWidgetIframe">' +
                        '<label for="embedThisWidgetIframeInput">&rsaquo; Embed Iframe</label>' +
                        '<div id="embedThisWidgetIframeInput">' +
                        self.getInputFormWithHtml('iframeEmbed', widgetIframeHtml) +
                        '</div>' +
                        '</div>' +
                        '<div> <label for="embedThisWidgetDirectLink">&rsaquo; Direct Link</label>' +
                        '<div id="embedThisWidgetDirectLink"> ' + self.getInputFormWithHtml('directLinkEmbed', urlIframe) + ' - <a href="' + urlIframe + '" rel="noreferrer"  target="_blank">' + _pk_translate('Widgetize_OpenInNewWindow') + '</a></div>'
                        + '</div>'
                    );

                    // Finally we append the content to the parent widget DIV
                    $(loadedWidgetElement)
                        .parent()
                        .append(exportButtonsElement);

                    piwik.helper.compileAngularComponents(exportButtonsElement);
                }
            },
            compile: function (element, attrs) {

                return function (scope, element, attrs, controller) {
                    element.widgetPreview({
                        onPreviewLoaded: controller.callbackAddExportButtonsUnderWidget
                    });
                };
            }
        };
    }
})();