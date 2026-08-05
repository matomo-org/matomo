/*!
 * Matomo - free/libre analytics platform
 *
 * @link    https://matomo.org
 * @license https://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function ($) {

    $(function() {
        var switchForm = function (fromFormId, toFormId) {
            var fromFormSelector = '#' + fromFormId;
            var toFormSelector = '#' + toFormId;

            var fromLoginInputId = fromFormSelector + '_login',
                toLoginInputId = toFormSelector + '_login',
                toPasswordInputId = toFormSelector + '_password';

            if ($(toLoginInputId).val() === '') {
                $(toLoginInputId).val($(fromLoginInputId).val());
            }

            var contentFrom = $(fromFormSelector).parents('.contentForm').first();
            var contentTo = $(toFormSelector).parents('.contentForm').first();

            // hide the bottom portion of the login screen & show the password reset bits
            $(contentFrom).fadeOut(500, function () {
                // focus on login or password control based on whether a login exists
                Materialize.updateTextFields();

                $(contentTo).fadeIn(500, function () {

                    if ($(toLoginInputId).val() === '') {
                        $(toLoginInputId).focus();
                    } else {
                        $(toPasswordInputId).focus();
                    }

                });
            });
        };

        var checkIfWeShowResetForm = function () {
            var urlParams = new URLSearchParams(window.location.search);

            if (urlParams.has('showResetForm')) {
                switchForm('login_form', 'reset_form');
            }
        }

        var errorsId = 'login_form_errors';

        // Drops what the container is already showing, so a re-submit doesn't stack a stale server
        // error above the new one — they are always errors, and `noclear` leaves the user no way
        // to dismiss them. The `infoMessage` alert is plain markup rather than a `vue-entry`, and
        // once removed it could not be restored, so it is left alone.
        var clearMessages = function (container) {
            var mounted = container.querySelectorAll('[vue-entry]');

            if (!mounted.length) {
                return;
            }

            // Unmounted before removal: dropping the nodes alone would orphan the Vue apps.
            piwikHelper.destroyVueComponent(container);
            mounted.forEach(function (element) {
                element.remove();
            });
        };

        // Reports the empty fields of a login page form through the same error banner the server
        // renders for them, instead of leaving it to the browser, so that the message reads like
        // every other error on the page. The markup keeps its `required` attributes as the
        // fallback for when this script never runs, so native validation is only turned off here,
        // where a handler replaces it.
        //
        // `fields` is a list of { selector, label }, where label is a translation key.
        var validateRequiredFields = function (formSelector, fields) {
            var form = document.querySelector(formSelector);

            if (!form) {
                return;
            }

            form.setAttribute('novalidate', 'novalidate');

            form.addEventListener('submit', function (event) {
                var errors = {},
                    empty = [];

                fields.forEach(function (field) {
                    var input = form.querySelector(field.selector);

                    // Compared against '' rather than trimmed, to stay in step with the server
                    // side 'required' rule on the same fields. A selector that matches nothing is
                    // left to the server rather than silently treated as filled.
                    if (!input || input.value !== '') {
                        return;
                    }

                    errors[input.name] = _pk_translate('General_Required', [
                        _pk_translate(field.label)
                    ]);
                    empty.push(input);
                });

                if (!empty.length) {
                    return;
                }

                // Resolved from the form rather than the page, so the reset password form's own
                // message container is never touched.
                var section = form.closest('.loginForm'),
                    container = section && section.querySelector('.message_container');

                // With nowhere to report them, blocking the submit would leave the button dead and
                // unexplained. Let it through and have the server render the same errors instead.
                if (!container) {
                    return;
                }

                clearMessages(container);

                var banner = document.createElement('div');
                banner.id = errorsId;
                banner.setAttribute('vue-entry', 'Login.FormErrors');
                banner.setAttribute('form-errors', JSON.stringify(errors));
                container.appendChild(banner);

                piwikHelper.compileVueEntryComponents(banner);

                // Compiling a vue-entry is a silent no-op when the plugin bundle isn't loaded, so
                // the submit is only held back once there is something on screen to hold it back
                // for. Otherwise let it through: the server applies the same `required` rules and
                // renders the same messages, which beats a dead button with no explanation.
                if (!banner.childElementCount) {
                    banner.remove();
                    return;
                }

                event.preventDefault();
                empty[0].focus();
            });
        };

        // set login form redirect url
        $('#login_form_redirect').val(window.location.href);

        // 'lost your password?' on click
        $('#login_form_nav').click(function (e) {
            e.preventDefault();
            switchForm('login_form', 'reset_form');
            return false;
        });

        // 'cancel' on click
        $('#reset_form_nav,#alternate_reset_nav').click(function (e) {
            e.preventDefault();
            switchForm('reset_form', 'login_form');
            return false;
        });

        // password reset on submit
        $('#reset_form_submit').click(function (e) {
            e.preventDefault();

            var ajaxDone = function (response) {
                $('.loadingPiwik').hide();

                var isSuccess = response.indexOf('form-errors="null"') !== -1,
                    fadeOutIds = '.resetForm .message_container';
                if (isSuccess) {
                    fadeOutIds += ',#reset_form,#reset_form_nav';
                }

                $(fadeOutIds).fadeOut(300, function () {
                    if (isSuccess) {
                        $('#alternate_reset_nav').show();
                    }

                    $('.resetForm .message_container').html(response).fadeIn(300);
                    piwikHelper.compileVueEntryComponents($('.resetForm .message_container'));
                });
            };

            $('.loadingPiwik').show();

            // perform reset password request
            $.ajax({
                type: 'POST',
                url: 'index.php',
                dataType: 'html',
                async: true,
                error: function () { ajaxDone('<div id="login_error"><strong>HTTP Error</strong></div>'); },
                success: ajaxDone,	// Callback when the request succeeds
                data: $('#reset_form').serialize()
            });

            return false;
        });

        validateRequiredFields('#login_form', [
            { selector: '#login_form_login', label: 'Login_LoginOrEmail' },
            { selector: '#login_form_password', label: 'General_Password' }
        ]);

        validateRequiredFields('#confirm_password_form', [
            { selector: '#login_form_password', label: 'General_Password' }
        ]);

        $('#login_form_login').focus();

        checkIfWeShowResetForm();

        Materialize.updateTextFields();
    });

}(jQuery));
