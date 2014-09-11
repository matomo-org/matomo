/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var Piwik_Overlay = (function () {

    var $body, $iframe, $sidebar, $main, $location, $loading, $errorNotLoading;
    var $rowEvolutionLink, $transitionsLink, $fullScreenLink;

    var idSite, period, date;

    var iframeSrcBase;
    var iframeDomain = '';
    var iframeCurrentPage = '';
    var iframeCurrentPageNormalized = '';
    var iframeCurrentActionLabel = '';
    var updateComesFromInsideFrame = false;

    /** Load the sidebar for a url */
    function loadSidebar(currentUrl) {
        showLoading();

        $location.html('&nbsp;').unbind('mouseenter').unbind('mouseleave');

        iframeCurrentPage = currentUrl;
        iframeDomain = currentUrl.match(/http(s)?:\/\/(www\.)?([^\/]*)/i)[3];

        globalAjaxQueue.abort();
        var ajaxRequest = new ajaxHelper();
        ajaxRequest.addParams({
            module: 'Overlay',
            action: 'renderSidebar',
            currentUrl: currentUrl
        }, 'get');
        ajaxRequest.setCallback(
            function (response) {
                hideLoading();

                var $response = $(response);

                var $responseLocation = $response.find('.Overlay_Location');
                var $url = $responseLocation.find('span');
                iframeCurrentPageNormalized = $url.data('normalizedUrl');
                iframeCurrentActionLabel = $url.data('label');
                $url.html(piwikHelper.addBreakpointsToUrl($url.text()));
                $location.html($responseLocation.html()).show();
                $responseLocation.remove();

                var $locationSpan = $location.find('span');
                $locationSpan.html(piwikHelper.addBreakpointsToUrl($locationSpan.text()));
                if (iframeDomain) {
                    // use addBreakpointsToUrl because it also encoded html entities
                    $locationSpan.tooltip({
                        track: true,
                        items: '*',
                        tooltipClass: 'Overlay_Tooltip',
                        content: '<strong>' + Piwik_Overlay_Translations.domain + ':</strong> ' +
                                  piwikHelper.addBreakpointsToUrl(iframeDomain),
                        show: false,
                        hide: false
                    });
                }

                $sidebar.empty().append($response).show();

                if ($sidebar.find('.Overlay_NoData').size() == 0) {
                    $rowEvolutionLink.show();
                    $transitionsLink.show()
                }
            }
        );
        ajaxRequest.setErrorCallback(function () {
            hideLoading();
            $errorNotLoading.show();
        });
        ajaxRequest.setFormat('html');
        ajaxRequest.send(false);
    }

    /** Adjust the dimensions of the iframe */
    function adjustDimensions() {
        $iframe.height($(window).height());
        $iframe.width($body.width() - $iframe.offset().left - 2); // -2 because of 2px border
    }

    /** Display the loading message and hide other containers */
    function showLoading() {
        $loading.show();

        $sidebar.hide();
        $location.hide();

        $fullScreenLink.hide();
        $rowEvolutionLink.hide();
        $transitionsLink.hide();

        $errorNotLoading.hide();
    }

    /** Hide the loading message */
    function hideLoading() {
        $loading.hide();
        $fullScreenLink.show();
    }

    /** $.history callback for hash change */
    function hashChangeCallback(urlHash) {
        var location = broadcast.getParamValue('l', urlHash);
        location = Overlay_Helper.decodeFrameUrl(location);

        if (!updateComesFromInsideFrame) {
            var iframeUrl = iframeSrcBase;
            if (location) {
                iframeUrl += '#' + location;
            }
            $iframe.attr('src', iframeUrl);
            showLoading();
        } else {
            loadSidebar(location);
        }

        updateComesFromInsideFrame = false;
    }

    return {

        /** This method is called when Overlay loads  */
        init: function (iframeSrc, pIdSite, pPeriod, pDate) {
            iframeSrcBase = iframeSrc;
            idSite = pIdSite;
            period = pPeriod;
            date = pDate;

            $body = $('body');
            $iframe = $('#Overlay_Iframe');
            $sidebar = $('#Overlay_Sidebar');
            $location = $('#Overlay_Location');
            $main = $('#Overlay_Main');
            $loading = $('#Overlay_Loading');
            $errorNotLoading = $('#Overlay_Error_NotLoading');

            $rowEvolutionLink = $('#Overlay_RowEvolution');
            $transitionsLink = $('#Overlay_Transitions');
            $fullScreenLink = $('#Overlay_FullScreen');

            adjustDimensions();

            showLoading();

            // apply initial dimensions
            window.setTimeout(function () {
                adjustDimensions();
            }, 50);

            // handle window resize
            // we manipulate broadcast.pageload because it unbinds all resize events on window
            var originalPageload = broadcast.pageload;
            broadcast.pageload = function (hash) {
                originalPageload(hash);
                $(window).resize(function () {
                    adjustDimensions();
                });
            };
            $(window).resize(function () {
                adjustDimensions();
            });

            // handle hash change
            broadcast.loadAjaxContent = hashChangeCallback;
            broadcast.init();

            if (window.location.href.split('#').length == 1) {
                // if there's no hash, broadcast won't trigger the callback - we have to do it here
                hashChangeCallback('');
            }

            // handle date selection
            var $select = $('select#Overlay_DateRangeSelect').change(function () {
                var parts = $(this).val().split(';');
                if (parts.length == 2) {
                    period = parts[0];
                    date = parts[1];
                    window.location.href = Overlay_Helper.getOverlayLink(idSite, period, date, iframeCurrentPage);
                }
            });

            var optionMatchFound = false;
            $select.find('option').each(function () {
                if ($(this).val() == period + ';' + date) {
                    $(this).prop('selected', true);
                    optionMatchFound = true;
                }
            });

            if (!optionMatchFound) {
                $select.prepend('<option selected="selected">');
            }

            // handle transitions link
            $transitionsLink.click(function () {
                DataTable_RowActions_Transitions.launchForUrl(iframeCurrentPageNormalized);
                return false;
            });

            // handle row evolution link
            $rowEvolutionLink.click(function () {
                DataTable_RowActions_RowEvolution.launch('Actions.getPageUrls', iframeCurrentActionLabel);
                return false;
            });

            // handle full screen link
            $fullScreenLink.click(function () {
                var href = iframeSrcBase;
                if (iframeCurrentPage) {
                    href += '#' + iframeCurrentPage.replace(/#/g, '%23');
                }
                window.location.href = href;
                return false;
            });
        },

        /** This callback is used from within the iframe */
        setCurrentUrl: function (currentUrl) {
            showLoading();

            var locationParts = location.href.split('#');
            var currentLocation = '';
            if (locationParts.length > 1) {
                currentLocation = broadcast.getParamValue('l', locationParts[1]);
            }

            var newLocation = Overlay_Helper.encodeFrameUrl(currentUrl);

            if (newLocation != currentLocation) {
                updateComesFromInsideFrame = true;
                // put the current iframe url in the main url to enable refresh and deep linking.
                // use disableHistory=true to make sure that the back and forward buttons can be
                // used on the iframe (which in turn notifies the parent about the location change)
                broadcast.propagateAjax('l=' + newLocation, true);
            } else {
                // happens when the url is changed by hand or when the l parameter is there on page load
                loadSidebar(currentUrl);
            }
        }

    };

})();