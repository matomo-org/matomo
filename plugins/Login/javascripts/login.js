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

        // Reports the empty fields of a login page form through the same error banner the server
        // renders for them, instead of leaving it to the browser, so that the message reads like
        // every other error on the page. The markup keeps its `required` attributes as the
        // fallback for when this script never runs, so native validation is only turned off here,
        // where a handler replaces it.
        //
        // `fields` is a list of { selector, label }, where label is a translation key.
        var validateRequiredFields = function (formSelector, fields) {
            $(formSelector).attr('novalidate', 'novalidate').on('submit', function () {
                var errors = {},
                    firstEmpty = null;

                $.each(fields, function (index, field) {
                    var $input = $(field.selector);

                    // Compared against '' rather than trimmed, to stay in step with the server
                    // side 'required' rule on the same fields.
                    if ($input.val() !== '') {
                        return;
                    }

                    errors[$input.attr('name')] = _pk_translate('General_Required', [
                        _pk_translate(field.label)
                    ]);
                    firstEmpty = firstEmpty || $input;
                });

                // Emptied on every attempt so repeated submits don't stack banners.
                var $errors = $('#login_form_errors').empty();

                if (firstEmpty === null) {
                    return true;
                }

                if (!$errors.length) {
                    // Appended rather than replacing the container, which also holds the errors
                    // and notifications rendered server side.
                    $errors = $('<div id="login_form_errors"></div>')
                        .appendTo($('.loginForm .message_container'));
                }

                var $entry = $('<div vue-entry="Login.FormErrors"></div>')
                    .attr('form-errors', JSON.stringify(errors))
                    .appendTo($errors);

                piwikHelper.compileVueEntryComponents($entry);

                firstEmpty.focus();

                return false;
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
