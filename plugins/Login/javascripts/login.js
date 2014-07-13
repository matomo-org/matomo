/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */
(function ($) {

    $(function() {
        var switchForm = function (fromFormId, toFormId, message, callback) {
            var fromLoginInputId = '#' + fromFormId + '_login',
                toLoginInputId = '#' + toFormId + '_login',
                toPasswordInputId = '#' + toFormId + '_password',
                fromLoginNavId = '#' + fromFormId + '_nav',
                toLoginNavId = '#' + toFormId + '_nav';

            if ($(toLoginInputId).val() === '') {
                $(toLoginInputId).val($(fromLoginInputId).val());
            }

            // hide the bottom portion of the login screen & show the password reset bits
            $('#' + fromFormId + ',#message_container').fadeOut(500, function () {
                // show lost password instructions
                $('#message_container').html(message);

                $(fromLoginNavId).hide();
                $(toLoginNavId).show();
                $('#' + toFormId + ',#message_container').fadeIn(500, function () {
                    // focus on login or password control based on whether a login exists
                    if ($(toLoginInputId).val() === '') {
                        $(toLoginInputId).focus();
                    }
                    else {
                        $(toPasswordInputId).focus();
                    }

                    if (callback) {
                        callback();
                    }
                });
            });
        };

        // 'lost your password?' on click
        $('#login_form_nav').click(function (e) {
            e.preventDefault();
            switchForm('login_form', 'reset_form', $('#lost_password_instructions').html());
            return false;
        });

        // 'cancel' on click
        $('#reset_form_nav,#alternate_reset_nav').click(function (e) {
            e.preventDefault();
            $('#alternate_reset_nav').hide();
            switchForm('reset_form', 'login_form', '');
            return false;
        });

        // password reset on submit
        $('#reset_form_submit').click(function (e) {
            e.preventDefault();

            var ajaxDone = function (response) {
                $('.loadingPiwik').hide();

                var isSuccess = response.indexOf('id="login_error"') === -1,
                    fadeOutIds = '#message_container';
                if (isSuccess) {
                    fadeOutIds += ',#reset_form,#reset_form_nav';
                }

                $(fadeOutIds).fadeOut(300, function () {
                    if (isSuccess) {
                        $('#alternate_reset_nav').show();
                    }

                    $('#message_container').html(response).fadeIn(300);
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
    });

}(jQuery));
