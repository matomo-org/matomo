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
});