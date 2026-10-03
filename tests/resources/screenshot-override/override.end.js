$(document).ready(function () {
    function updateSystemCheck() {
        $('.system-check tr:contains(Time) td:nth-child(2)').text('Not showing in tests');
        $('.system-check tr:contains(Datetime) td:nth-child(2)').text('Not showing in tests');
        $('.system-check tr:contains(Version) td:nth-child(2)').text('Not showing in tests');
        $('.system-check tr:contains(User Agent) td:nth-child(2)').text('Not showing in tests');
        $('.system-check tr:contains(PHP_BINARY) td:nth-child(2)').text('Not showing in tests');
        $('.system-check tr:contains(Server Info) td:nth-child(2)').text('Not showing in tests');
        $('.system-check tr:contains(PHP Disabled functions)').hide();
    }

    updateSystemCheck();

    if (window.piwikHelper) {
        setTimeout(function () {
            // because of vue rendering replacing the content potentially...
            updateSystemCheck();
            setTimeout(function () {
                updateSystemCheck();
            }, 100);
        });
    }

    $('.ui-inline-help:contains(UTC time is)').hide();

    $('[notification-id=ControllerAdmin_HttpIsUsed]').hide();
});
