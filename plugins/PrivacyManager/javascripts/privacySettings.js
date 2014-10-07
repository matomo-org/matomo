/*!
 * Piwik - free/libre analytics platform
 *
 * @link http://piwik.org
 * @license http://www.gnu.org/licenses/gpl-3.0.html GPL v3 or later
 */

$(document).ready(function () {
    function toggleBlock(id, value) {
        $('#' + id).toggle(value == 1);
    }

    function isEitherDeleteSectionEnabled() {
        return ($('input[name=deleteEnable]:checked').val() == 1)
            || ($('input[name=deleteReportsEnable]:checked').val() == 1);
    }

    function toggleOtherDeleteSections() {
        var showSection = isEitherDeleteSectionEnabled();
        toggleBlock('deleteDataEstimateSect', showSection);
        toggleBlock('deleteSchedulingSettings', showSection);
    }

    // reloads purged database size estimate
    var currentRequest;

    /**
     * @param {boolean} [forceEstimate]  (defaults to false)
     */
    function reloadDbStats(forceEstimate) {
        if (currentRequest) {
            currentRequest.abort();
        }

        // if the section isn't visible or the manual estimate link is showing, abort
        // (unless on first load or forcing)
        var isFirstLoad = $('#deleteDataEstimate').html() == '';
        if (!isFirstLoad
            && forceEstimate !== true
            && (!isEitherDeleteSectionEnabled() || $('#getPurgeEstimateLink').length > 0)) {
            return;
        }

        $('#deleteDataEstimate').hide();

        var data = $('#formDeleteSettings').serializeArray();
        var formData = {};
        for (var i = 0; i < data.length; i++) {
            formData[data[i].name] = data[i].value;
        }
        if (forceEstimate === true) {
            formData['forceEstimate'] = 1;
        }

        currentRequest = new ajaxHelper();
        currentRequest.setLoadingElement('#deleteDataEstimateSect .loadingPiwik');
        currentRequest.addParams({
            module: 'PrivacyManager',
            action: 'getDatabaseSize'
        }, 'get');
        currentRequest.addParams(formData, 'post');
        currentRequest.setCallback(
            function (data) {
                currentRequest = undefined;
                $('#deleteDataEstimate').html(data).show();

                // lock size of db size estimate
                $('#deleteDataEstimateSect').height($('#deleteDataEstimateSect').height());
            }
        );
        currentRequest.setFormat('html');
        currentRequest.send(false);
    }

    // make sure certain sections only display if their corresponding features are enabled
    $('input[name=anonymizeIPEnable]').click(function () {
        toggleBlock("anonymizeIPenabled", $(this).val());
    });

    $('input[name=deleteEnable]').click(function () {
        toggleBlock("deleteLogSettings", $(this).val());
        toggleOtherDeleteSections();
    }).change(reloadDbStats);

    $('input[name=deleteReportsEnable]').click(function () {
        toggleBlock("deleteReportsSettings", $(this).val());
        toggleBlock("deleteOldReportsMoreInfo", $(this).val());
        toggleOtherDeleteSections();
    }).change(reloadDbStats);

    // initial toggling calls
    $(function () {
        toggleBlock("deleteLogSettings", $("input[name=deleteEnable]:checked").val());
        toggleBlock("anonymizeIPenabled", $("input[name=anonymizeIPEnable]:checked").val());
        toggleBlock("deleteReportsSettings", $("input[name=deleteReportsEnable]:checked").val());
        // This one is in an AngularJS directive, so is generated later
        setTimeout(function () {
            toggleBlock("deleteOldReportsMoreInfo", $("input[name=deleteReportsEnable]:checked").val());
        }, 500);
        toggleOtherDeleteSections();
    });

    // make sure the DB size estimate is reloaded every time a delete logs/reports setting is changed
    $('#formDeleteSettings').find('input[type=text]').each(function () {
        $(this).change(reloadDbStats);
    });
    $('#formDeleteSettings').find('input[type=checkbox]').each(function () {
        $(this).click(reloadDbStats);
    });

    // make sure when the delete log/report settings are submitted, a confirmation popup is
    // displayed first
    $('#deleteLogSettingsSubmit').click(function (e) {
        var deletingLogs = $("input[name=deleteEnable]:checked").val() == 1,
            deletingReports = $("input[name=deleteReportsEnable]:checked").val() == 1,
            confirm_id;

        // hide all confirmation texts, then show the correct one based on what
        // type of deletion is enabled.
        $('#confirmDeleteSettings').find('>h2').each(function () {
            $(this).hide();
        });

        if (deletingLogs) {
            confirm_id = deletingReports ? "deleteBothConfirm" : "deleteLogsConfirm";
        }
        else if (deletingReports) {
            confirm_id = "deleteReportsConfirm";
        }

        if (confirm_id) {
            $("#" + confirm_id).show();
            e.preventDefault();

            piwikHelper.modalConfirm('#confirmDeleteSettings', {
                yes: function () {
                    $('#formDeleteSettings').submit();
                }
            });
        }
        else {
            $('#formDeleteSettings').submit();
        }
    });

    // execute purge now link click
    $('#purgeDataNowLink').click(function (e) {
        e.preventDefault();

        var link = this;

        // if any option has been modified, abort purging and instruct user to save first
        var modified = false;
        $('#formDeleteSettings').find('input').each(function () {
            if (this.type === 'checkbox' || this.type === 'radio') {
                modified |= this.defaultChecked !== this.checked;
            } else {
                modified |= this.defaultValue !== this.value;
            }
        });

        if (modified) {
            piwikHelper.modalConfirm('#saveSettingsBeforePurge', {yes: function () {}});
            return;
        }

        // ask user if they really want to delete their old data
        piwikHelper.modalConfirm('#confirmPurgeNow', {
            yes: function () {
                $(link).hide();

                // execute a data purge
                var ajaxRequest = new ajaxHelper();
                ajaxRequest.setLoadingElement('#deleteSchedulingSettings .loadingPiwik');
                ajaxRequest.addParams({
                    module: 'PrivacyManager',
                    action: 'executeDataPurge'
                }, 'get');
                ajaxRequest.setCallback(
                    function () {
                        // force reload
                        $('#deleteDataEstimate').html('');
                        reloadDbStats();

                        // show 'db purged' message
                        $('#db-purged-message').fadeIn('slow');
                        setTimeout(function () {
                            // hide 'db purged' message & show link
                            $('#db-purged-message').fadeOut('slow', function () {
                                $(link).show();
                            });
                        }, 2000);
                    }
                );
                ajaxRequest.setFormat('html');
                ajaxRequest.send(false);
            }
        });
    });

    // get estimate link click
    $('#getPurgeEstimateLink').click(function (e) {
        e.preventDefault();
        reloadDbStats(true);
    });

    // load initial db size estimate
    reloadDbStats();
});
