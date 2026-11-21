$(function () {
    $('input:first').focus();
    $('code').click(function () {
        $(this).select();
    });

    // Focus the first input field in the form
    $('form:not(.filter) :input:visible:enabled:first').focus();

    $('select').material_select();
});

$(document).ready(function() {
    $('.form-help').each(function (index, help) {
        var $help = $(help);
        var $row = $help.parents('.row').first();

        if ($row.length) {
            $help.addClass('col s12 m12 l6');
            $row.append($help);
        }
    });
    $(".should-get-hidden-by-js").hide();
});
$(function () {
    // SQLite needs a file path instead of a server, a login and a database name
    var $adapter = $('select[name="adapter"]');
    if (!$adapter.length) {
        return;
    }

    var serverFields = ['host', 'username', 'password', 'dbname', 'schema'];
    var sqliteFields = ['sqlite_file', 'sqlite_wal_reset_fix_attested'];

    function getRow(name) {
        return $('[name="' + name + '"]').closest('.form-group');
    }

    function showFieldsForAdapter() {
        var isSqlite = String($adapter.val()).toUpperCase() === 'PDO\\SQLITE';
        serverFields.forEach(function (name) {
            getRow(name).toggle(!isSqlite);
        });
        sqliteFields.forEach(function (name) {
            getRow(name).toggle(isSqlite);
        });
    }

    $adapter.on('change', showFieldsForAdapter);
    showFieldsForAdapter();
});
