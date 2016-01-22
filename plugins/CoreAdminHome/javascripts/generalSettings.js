/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

function sendGeneralSettingsAJAX() {
    var enableBrowserTriggerArchiving = $('input[name=enableBrowserTriggerArchiving]:checked').val();
    var enablePluginUpdateCommunication = $('input[name=enablePluginUpdateCommunication]:checked').val();
    var releaseChannel = $('input[name=releaseChannel]:checked').val();
    var todayArchiveTimeToLive = $('#todayArchiveTimeToLive').val();

    var trustedHosts = [];
    $('input[name=trusted_host]').each(function () {
        trustedHosts.push($(this).val());
    });

    var ajaxHandler = new ajaxHelper();
    ajaxHandler.setLoadingElement();
    ajaxHandler.addParams({
        format: 'json',
        enableBrowserTriggerArchiving: enableBrowserTriggerArchiving,
        enablePluginUpdateCommunication: enablePluginUpdateCommunication,
        releaseChannel: releaseChannel,
        todayArchiveTimeToLive: todayArchiveTimeToLive,
        mailUseSmtp: isSmtpEnabled(),
        mailPort: $('#mailPort').val(),
        mailHost: $('#mailHost').val(),
        mailType: $('#mailType').val(),
        mailUsername: $('#mailUsername').val(),
        mailPassword: $('#mailPassword').val(),
        mailEncryption: $('#mailEncryption').val(),
        useCustomLogo: isCustomLogoEnabled(),
        trustedHosts: JSON.stringify(trustedHosts)
    }, 'POST');
    ajaxHandler.addParams({
        module: 'CoreAdminHome',
        action: 'setGeneralSettings'
    }, 'GET');
    ajaxHandler.redirectOnSuccess();
    ajaxHandler.send(true);
}
function showSmtpSettings(value) {
    $('#smtpSettings').toggle(value == 1);
}
function isSmtpEnabled() {
    return $('input[name="mailUseSmtp"]:checked').val();
}
function showCustomLogoSettings(value) {
    $('#logoSettings').toggle(value == 1);
}
function isCustomLogoEnabled() {
    return $('input[name="useCustomLogo"]:checked').val();
}

function refreshCustomLogo() {
    var selectors = ['#currentLogo', '#currentFavicon'];
    var index;
    for (index = 0; index < selectors.length; index++) {
        var imageDiv = $(selectors[index]);
        if (imageDiv && imageDiv.attr("src")) {
            var logoUrl = imageDiv.attr("src").split("?")[0];
            imageDiv.attr("src", logoUrl + "?" + (new Date()).getTime());
        }
    }
}

$(document).ready(function () {
    var originalTrustedHostCount = $('input[name=trusted_host]').length;

    showSmtpSettings(isSmtpEnabled());
    showCustomLogoSettings(isCustomLogoEnabled());
    $('.generalSettingsSubmit').click(function () {
        var doSubmit = function () {
            sendGeneralSettingsAJAX();
        };

        var hasTrustedHostsChanged = false,
            hosts = $('input[name=trusted_host]');
        if (hosts.length != originalTrustedHostCount) {
            hasTrustedHostsChanged = true;
        }
        else {
            hosts.each(function () {
                hasTrustedHostsChanged |= this.defaultValue != this.value;
            });
        }

        // if trusted hosts have changed, make sure to ask for confirmation
        if (hasTrustedHostsChanged) {
            piwikHelper.modalConfirm('#confirmTrustedHostChange', {yes: doSubmit});
        }
        else {
            doSubmit();
        }
    });

    $('input[name=mailUseSmtp]').click(function () {
        showSmtpSettings($(this).val());
    });
    $('input[name=useCustomLogo]').click(function () {
        refreshCustomLogo();
        showCustomLogoSettings($(this).val());
    });
    $('input').keypress(function (e) {
            var key = e.keyCode || e.which;
            if (key == 13) {
                $('.generalSettingsSubmit').click();
            }
        }
    );

    $("#logoUploadForm").submit(function (data) {
        var submittingForm = $(this);
        $('.uploaderror').fadeOut();
        var frameName = "upload" + (new Date()).getTime();
        var uploadFrame = $("<iframe name=\"" + frameName + "\" />");
        uploadFrame.css("display", "none");
        uploadFrame.load(function (data) {
            setTimeout(function () {
                refreshCustomLogo();

                var frameContent = $(uploadFrame.contents()).find('body').html();
                frameContent = $.trim(frameContent);

                if ('0' === frameContent) {
                    $('.uploaderror').show();
                }

                if ('1' === frameContent || '0' === frameContent) {
                    uploadFrame.remove();
                }
            }, 1000);
        });
        $("body:first").append(uploadFrame);
        submittingForm.attr("target", frameName);
    });

    $('#customLogo,#customFavicon').change(function () {
        $("#logoUploadForm").submit();
        $(this).val('');
    });

    // trusted hosts event handling
    var trustedHostSettings = $('#trustedHostSettings');
    trustedHostSettings.on('click', '.remove-trusted-host', function (e) {
        e.preventDefault();
        $(this).parent('li').remove();
        return false;
    });
    trustedHostSettings.find('.add-trusted-host').click(function (e) {
        e.preventDefault();

        // append new row to the table
        trustedHostSettings.find('ul').append(trustedHostSettings.find('li:last').clone());
        trustedHostSettings.find('li:last input').val('').focus();
        return false;
    });

});
