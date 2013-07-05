$(document).ready(function () {
    $('#showSql').click(function () {
        $('#sqlQueries').toggle();
    });
    $('#upgradeCorePluginsForm').submit(function () {
        $('input[type=submit]', this).prop('disabled', 'disabled');
    });
});