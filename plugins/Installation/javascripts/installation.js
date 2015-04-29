$(function () {
    $('input:first').focus();
    $('code').click(function () { $(this).select(); });

    // Focus the first input field in the form
    $('form:not(.filter) :input:visible:enabled:first').focus();
});