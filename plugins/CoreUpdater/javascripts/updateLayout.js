$(document).ready(function () {
    $('#showSql').click(function (e) {
        e.preventDefault();
        $('#sqlQueries').toggle();
    });
    $('#upgradeCorePluginsForm').submit(function () {
        $('input[type=submit]', this)
            .prop('disabled', 'disabled')
            .val($('#upgradeCorePluginsForm').data('updating'));
    });
});
