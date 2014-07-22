/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

(function ($) {

    $(function () {

        //
        // 'check for updates' behavior
        //

        var headerMessageParent = $('#header_message').parent();

        // when 'check for updates...' link is clicked, force a check & display the result
        headerMessageParent.on('click', '#updateCheckLinkContainer', function (e) {
            e.preventDefault();

            var headerMessage = $(this).closest('#header_message');

            var ajaxRequest = new ajaxHelper();
            ajaxRequest.setLoadingElement('#header_message .loadingPiwik');
            ajaxRequest.addParams({
                module: 'CoreHome',
                action: 'checkForUpdates'
            }, 'get');
            ajaxRequest.setCallback(function (response) {
                headerMessage.fadeOut('slow', function () {
                    response = $(response);

                    var newVersionAvailable = response.hasClass('header_alert');
                    if (newVersionAvailable) {
                        headerMessage.replaceWith(response);
                    }
                    else {
                        headerMessage.html(_pk_translate('CoreHome_YouAreUsingTheLatestVersion')).show();
                        setTimeout(function () {
                            headerMessage.fadeOut('slow', function () {
                                headerMessage.replaceWith(response);
                            });
                        }, 4000);
                    }
                });
            });
            ajaxRequest.setFormat('html');
            ajaxRequest.send(false);

            return false;
        });

        // when clicking the header message, show the long message w/o needing to hover
        headerMessageParent.on('click', '#header_message', function (e) {
            if (e.target.tagName.toLowerCase() != 'a') {
                $(this).toggleClass('active');
            }
        });

        //
        // section toggler behavior
        //

        var handleSectionToggle = function (self, showType, doHide) {
            var sectionId = $(self).attr('data-section-id'),
                section = $('#' + sectionId),
                showText = _pk_translate('General_Show'),
                hideText = _pk_translate('General_Hide');

            if (typeof(doHide) == 'undefined') {
                doHide = section.is(':visible');
            }

            if (doHide) {
                var newText = $(self).text().replace(hideText, showText),
                    afterHide = function () { $(self).text(newText); };

                if (showType == 'slide') {
                    section.slideUp(afterHide);
                }
                else if (showType == 'inline') {
                    section.hide();
                    afterHide();
                }
                else {
                    section.hide(afterHide);
                }
            }
            else {
                var newText = $(self).text().replace(showText, hideText);
                $(self).text(newText);

                if (showType == 'slide') {
                    section.slideDown();
                }
                else if (showType == 'inline') {
                    section.css('display', 'inline-block');
                }
                else {
                    section.show();
                }
            }
        };

        // when click section toggler link, toggle the visibility of the associated section
        $('body').on('click', 'a.section-toggler-link', function (e) {
            e.preventDefault();
            handleSectionToggle(this, 'slide');
            return false;
        });

        $('body').on('change', 'input.section-toggler-link', function (e) {
            handleSectionToggle(this, 'inline', !$(this).is(':checked'));
        });

        //
        // reports by dimension list behavior
        //

        // when a report dimension is clicked, load the appropriate report
        var currentWidgetLoading = null;
        $('body').on('click', '.reportDimension', function (e) {
            var view = $(this).closest('.reportsByDimensionView'),
                report = $('.dimensionReport', view),
                loading = $('.loadingPiwik', view);

            // make this dimension the active one
            $('.activeDimension', view).removeClass('activeDimension');
            $(this).addClass('activeDimension');

            // hide the visible report & show the loading elem
            report.hide();
            loading.show();

            // load the report using the data-url attribute (which holds the URL to the report)
            var widgetParams = broadcast.getValuesFromUrl($(this).attr('data-url'));
            for (var key in widgetParams) {
                widgetParams[key] = decodeURIComponent(widgetParams[key]);
            }

            var widgetUniqueId = widgetParams.module + widgetParams.action;
            currentWidgetLoading = widgetUniqueId;

            widgetsHelper.loadWidgetAjax(widgetUniqueId, widgetParams, function (response) {
                // if the widget that was loaded was not for the latest clicked link, do nothing w/ the response
                if (widgetUniqueId != currentWidgetLoading) {
                    return;
                }

                loading.hide();
                report.css('display', 'inline-block').html($(response));

                // scroll to report
                piwikHelper.lazyScrollTo(report, 400);
            }, function (deferred, status) {
                if (status == 'abort' || !deferred || deferred.status < 400 || deferred.status >= 600) {
                    return;
                }

                loading.hide();

                var errorMessage = _pk_translate('General_ErrorRequest', ['', '']);
                if ($('#loadingError').html()) {
                    errorMessage = $('#loadingError').html();
                }

                report.css('display', 'inline-block').html('<div class="dimensionLoadingError">' + errorMessage + '</div>');
            });
        });
    });

}(jQuery));
