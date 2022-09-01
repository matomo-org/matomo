/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
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
            var headerMessage = $(this).closest('#header_message');

            var $titleElement = headerMessage.find('.title');
            if ($titleElement.attr('target')) { // if this is an external link, internet access is not available on the server
                return;
            }

            e.preventDefault();

            var ajaxRequest = new ajaxHelper();
            ajaxRequest.setLoadingElement('#header_message .loadingPiwik');
            ajaxRequest.addParams({
                module: 'CoreHome',
                action: 'checkForUpdates'
            }, 'get');

            ajaxRequest.withTokenInUrl();

            $titleElement.addClass('activityIndicator');

            ajaxRequest.setCallback(function (response) {
                headerMessage.fadeOut('slow', function () {
                    response = $('#header_message', $('<div>' + response + '</div>'));

                    $titleElement.removeClass('activityIndicator');

                    var newVersionAvailable = response.hasClass('update_available');
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
            ajaxRequest.send();

            return false;
        });

        // when clicking the header message, show the long message w/o needing to hover
        headerMessageParent.on('click', '#header_message', function (e) {
            if (e.target.tagName.toLowerCase() != 'a') {
                $(this).toggleClass('expanded');
            }
        });

    });

}(jQuery));


$( document ).ready(function() {
    $('.accessibility-skip-to-content').click(function(e){
        $('a[name="main"]').attr('tabindex', -1).focus();
        $(window).scrollTo($('a[name="main"]'));
    });

    $("#mobile-top-menu").sideNav({
        closeOnClick: true,
        edge: 'right'
    });

    $('.navbar.collapsible').collapsible();

    $('select').not('.ui-datepicker select').material_select();

    piwikHelper.registerShortcut('?', _pk_translate('CoreHome_ShortcutHelp') , function (event) {
        // don't open if an modal is already shown
        if (event.altKey || $('.modal.open').length) {
            return;
        }
        if (event.preventDefault) {
            event.preventDefault();
        } else {
            event.returnValue = false; // IE
        }

        var list = $('#shortcuthelp dl');
        list.empty();

        var keys = Object.keys(piwikHelper.shortcuts).sort();

        jQuery.each(keys, function(i, key) {
            if (piwikHelper.shortcuts.hasOwnProperty(key)) {
                list.append($('<dt />').append($('<kbd />').text(key)));
                list.append($('<dd />').text(piwikHelper.shortcuts[key]));
            }
        });

        var isMac = navigator.userAgent.indexOf('Mac OS X') != -1;

        list.append($('<dt />').append($('<kbd />').text(_pk_translate(isMac ? "CoreHome_MacPageUp" : "CoreHome_HomeShortcut"))));

        list.append($('<dd />').text(_pk_translate('CoreHome_PageUpShortcutDescription')));

        list.append($('<dt />').append($('<kbd />').text(_pk_translate(isMac ? "CoreHome_MacPageDown" : "CoreHome_EndShortcut"))));

        list.append($('<dd />').text(_pk_translate('CoreHome_PageDownShortcutDescription')));

        piwikHelper.modalConfirm('#shortcuthelp');
    });
});
