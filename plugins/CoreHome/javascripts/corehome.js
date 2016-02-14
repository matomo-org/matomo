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

        initTopControls();

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

            ajaxRequest.withTokenInUrl();

            var $titleElement = $(this);
            $titleElement.addClass('activityIndicator');

            ajaxRequest.setCallback(function (response) {
                headerMessage.fadeOut('slow', function () {
                    response = $(response);

                    $titleElement.removeClass('activityIndicator');

                    var newVersionAvailable = response.hasClass('header_alert');
                    if (newVersionAvailable) {
                        headerMessage.replaceWith(response);
                        headerMessage.show();
                    }
                    else {
                        headerMessage.find('.title').html(_pk_translate('CoreHome_YouAreUsingTheLatestVersion'));
                        headerMessage.show();
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
                $(this).toggleClass('expanded');
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

    });




}(jQuery));

$( document ).ready(function() {
   $('.accessibility-skip-to-content').click(function(e){
        $('a[name="main"]').attr('tabindex', -1).focus();
        $(window).scrollTo($('a[name="main"]'));
    });

});
