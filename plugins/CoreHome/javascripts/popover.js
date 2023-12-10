/*!
 * Matomo - free/libre analytics platform
 *
 * @link https://matomo.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

var Piwik_Popover = (function () {

    var container = false;
    var isOpen = false;
    var closeCallback = false;
    var isProgrammaticClose = false;
    var scrollTopPosition = 0;

    var createContainer = function () {
        if (container === false) {
            container = $(document.createElement('div')).attr('id', 'Piwik_Popover');
        }
    };

    var openPopover = function (title, dialogClass) {
        createContainer();

        var options =
        {
            title: title,
            modal: true,
            width: '1050px',
            resizable: false,
            autoOpen: true,
            open: function (event, ui) {
                if (dialogClass) {
                    $(this).parent().addClass(dialogClass).attr('style', '');
                }

                $('.ui-widget-overlay').on('click.popover', function () {
                    // if clicking outside of the dialog, close entire stack
                    broadcast.resetPopoverStack();
                    container.dialog('close');
                });

                // sometimes the modal can be displayed outside of the current viewport, in this case
                // we scroll to it to make sure it's visible. this isn't a perfect workaround, since it
                // doesn't center the modal.g
                var self = this;

                scrollTopPosition = $(window).scrollTop();

                $('#root').css({
                    position: 'fixed',
                    height: $(window).height + scrollTopPosition,
                    width: '100%',
                    top: -scrollTopPosition
                });

                window.scrollTo(0, 0);

                centerPopover();

            },
            close: function (event, ui) {
                container.find('div.jqplot-target').trigger('piwikDestroyPlot');
                container[0].innerHTML = '';
                container.dialog('destroy').remove();
                globalAjaxQueue.abort();
                $('.ui-widget-overlay').off('click.popover');
                isOpen = false;
                require('piwik/UI').UIControl.cleanupUnusedControls();
                if (typeof closeCallback == 'function') {
                    closeCallback();
                    closeCallback = false;
                }

                // just to avoid any annoying race conditions that cause tooltips to remain on the screen permanently,
                // remove any that still exist
                $('body > .ui-tooltip').remove();

                // if we were not called by Piwik_Popover.close(), then the user clicked the close button or clicked
                // the overlay, in which case we want to handle the popover URL as well as the actual modal.
                if (!isProgrammaticClose || isEscapeKey(event)) {
                    broadcast.propagateNewPopoverParameter(false);
                }

                $('#root').css({
                    position: '',
                    height: '',
                    width: '',
                    top: ''
                });

                window.scrollTo(0, scrollTopPosition);
            }
        };

        container.dialog(options);

        // override the undocumented _title function to ensure that the title attribute is not escaped (according to jQueryUI bug #6016)
        container.data( "uiDialog" )._title = function(title) {
            title.html( this.options.title );
        };

        isOpen = true;
    };

    var centerPopover = function () {
        if (container !== false) {
            $('.ui-dialog').css({margin: '0 0'});
            container.dialog("option", "position", {my: 'center', at: 'center', of: '.ui-widget-overlay', collision: 'fit'});
            // in some cases jQuery UI fails to place the dialog correctly and set the top values to something negative
            if ($('.ui-dialog').position().top < 0) {
                $('.ui-dialog').css('top', '0');
            }
            $('.ui-dialog').css({margin: '15px 0'});
        }
    };

    return {

        /**
         * Open the popover with a loading message
         *
         * @param {string} popoverName        name of the popover
         * @param {string} [popoverSubject]   subject of the popover (e.g. url, optional)
         * @param {int}    [height]           height of the popover in px (optional)
         * @param {string} [dialogClass]      css class to add to dialog
         */
        showLoading: function (popoverName, popoverSubject, height, dialogClass) {
            var loading = $(document.createElement('div')).addClass('Piwik_Popover_Loading');

            var loadingMessage = popoverSubject ? translations.General_LoadingPopoverFor :
                translations.General_LoadingPopover;

            loadingMessage = sprintf(loadingMessage, popoverName);

            var p1 = $(document.createElement('p')).addClass('Piwik_Popover_Loading_Name');
            loading.append(p1.text(loadingMessage));

            var p2;
            if (popoverSubject) {
                popoverSubject = piwikHelper.addBreakpointsToUrl(popoverSubject);
                p1.addClass('Piwik_Popover_Loading_NameWithSubject');
                p2 = $(document.createElement('p')).addClass('Piwik_Popover_Loading_Subject');
                loading.append(p2.html(popoverSubject));
            }

            if (height) {
                loading.height(height);
            }

            if (!isOpen) {
                openPopover(null, dialogClass);
            }

            this.setContent(loading);
            this.setTitle('');

            if (height) {
                var offset = loading.height() - p1.outerHeight();
                if (popoverSubject) {
                    offset -= p2.outerHeight();
                }
                var spacingEl = $(document.createElement('div'));
                spacingEl.height(Math.round(offset / 2));
                loading.prepend(spacingEl);
            }

            return container;
        },

        /**
         * Add a help button to the current popover
         *
         * @param {string} helpUrl
         */
        addHelpButton: function (helpUrl) {
            if (!isOpen) {
                return;
            }

            var titlebar = container.parent().find('.ui-dialog-titlebar');

            var button = $(document.createElement('a')).addClass('ui-dialog-titlebar-help');
            button.attr({href: helpUrl, target: '_blank'});

            titlebar.append(button);
        },

        /** Set the title of the popover */
        setTitle: function (titleHtml) {
            var titleText = piwikHelper.htmlDecode(titleHtml);
            if (titleText.length > 60) {
                titleHtml = $('<span>').attr('class', 'tooltip').attr('title', titleText).html(titleHtml);
            }
            container.dialog('option', 'title', titleHtml);
            try {
                $('.tooltip', container.parentNode).tooltip('destroy');
            } catch (e) {}
            if (titleText.length > 60) {
                $('.tooltip', container.parentNode).tooltip({track: true, items: '.tooltip'});
            }
        },

        /** Set inner HTML of the popover */
        setContent: function (html) {
            if (typeof closeCallback == 'function') {
                closeCallback();
                closeCallback = false;
            }

            container.html(html);

            container.children().each(function (i, childNode) {
                piwikHelper.compileVueEntryComponents(childNode);
            });

            centerPopover();
        },

        /**
         * Show an error message. All params are HTML!
         *
         * @param {string}  title
         * @param {string}  [message]
         * @param {string}  [backLabel]
         */
        showError: function (title, message, backLabel) {
            var error = $(document.createElement('div')).addClass('Piwik_Popover_Error');

            var p = $(document.createElement('p')).addClass('Piwik_Popover_Error_Title');
            error.append(p.html(title));

            if (message) {
                p = $(document.createElement('p')).addClass('Piwik_Popover_Error_Message');
                error.append(p.html(message));
            }

            if (backLabel) {
                var back = $(document.createElement('a')).addClass('Piwik_Popover_Error_Back');
                back.attr('href', '#').click(function () {
                    history.back();
                    return false;
                });
                error.append(back.html(backLabel));
            }

            if (!isOpen) {
                openPopover();
            }

            this.setContent(error);
        },

        /**
         * Add a callback for the next time the popover is closed or the content changes
         *
         * @param {function}  callback
         */
        onClose: function (callback) {
            closeCallback = callback;
        },

        /**
         * Close the popover.
         *
         * Note: this method shouldn't normally be used to close a popover, instead
         * `broadcast.propagateNewPopoverHandler(false)` should be used.
         */
        close: function () {
            if (isOpen) {
                isProgrammaticClose = true;
                container.dialog('close');
                isProgrammaticClose = false;
            }
        },

        /**
         * Create a Popover and load the specified URL in it.
         *
         * Note: If you want the popover to be persisted in the URL (so if the URL is copy/pasted
         * to a new window/tab it will be opened there), use broadcast.propagateNewPopoverParameter
         * with a popover handler function that calls this one.
         *
         * @param {string} url
         * @param {string} loadingName
         * @param {string} [dialogClass]      css class to add to dialog
         * @param {object} [ajaxRequest]      optional instance of ajaxHelper
         */
        createPopupAndLoadUrl: function (url, loadingName, dialogClass, ajaxRequest) {
            // open the popover
            var box = Piwik_Popover.showLoading(loadingName, null, null, dialogClass);

            var callback = function (html) {
                function setPopoverTitleIfOneFoundInContainer() {
                    var title = $('h1,h2', container);
                    if (title.length == 1) {
                        Piwik_Popover.setTitle(title.text());
                        $(title).hide();
                    }
                }

                Piwik_Popover.setContent(html);
                setPopoverTitleIfOneFoundInContainer();
            };

            if ('undefined' === typeof ajaxRequest) {
                ajaxRequest = new ajaxHelper();
            }
            ajaxRequest.addParams(piwikHelper.getArrayFromQueryString(url), 'get');
            ajaxRequest.setCallback(callback);
            ajaxRequest.setFormat('html');
            ajaxRequest.send();
        },

        isOpen: function() {
            return isOpen;
        }
    };
})();
