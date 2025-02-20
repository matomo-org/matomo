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

        $('#login_form_login').focus();

        checkIfWeShowResetForm();

        Materialize.updateTextFields();
    });

}(jQuery));
